<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Cli\Domain\CommandHandler\MessageQueue\Consumer\TransXChange;

use Aws\S3\S3Client;
use Dvsa\Olcs\Api\Domain\Command\Bus\Ebsr\UpdateTxcInboxPdf as UpdateTxcInboxPdfCmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Command\Task\CreateTask as CreateTaskCmd;
use Dvsa\Olcs\Api\Domain\CommandHandlerManager;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\QueryHandlerManager;
use Dvsa\Olcs\Api\Domain\Repository\EbsrSubmission;
use Dvsa\Olcs\Api\Domain\RepositoryServiceManager;
use Dvsa\Olcs\Api\Entity\Bus\BusReg as BusRegEntity;
use Dvsa\Olcs\Api\Entity\Ebsr\EbsrSubmission as EbsrSubmissionEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\User\User as UserEntity;
use Dvsa\Olcs\Api\Rbac\IdentityProviderInterface;
use Dvsa\Olcs\Cli\Domain\Command\MessageQueue\Consumer\TransXChange\TransXChangeConsumer as TransXChangeConsumerCmd;
use Dvsa\Olcs\Cli\Domain\CommandHandler\MessageQueue\Consumer\TransXChange\TransXChangeConsumer;
use Dvsa\Olcs\Queue\Service\Message\MessageBuilder;
use Dvsa\Olcs\Queue\Service\Queue;
use Dvsa\Olcs\Transfer\Command\Document\Upload;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Exception;
use Laminas\Filter\FilterPluginManager;
use Laminas\Validator\ValidatorPluginManager;
use Mockery as m;
use Olcs\Logging\Log\Logger;
use Olcs\XmlTools\Filter\MapXmlFile;
use Olcs\XmlTools\Filter\ParseXmlString;
use Olcs\XmlTools\Validator\Xsd;
use Olcs\XmlTools\Xml\Specification\SpecificationInterface;
use Psr\Container\ContainerInterface;
use RuntimeException;

#[\PHPUnit\Framework\Attributes\PreserveGlobalState(false)]
#[\PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses]
#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class TransXChangeConsumerTest extends AbstractCommandHandlerTestCase
{
    protected array $config = [
        'message_queue' => [
            'TransXChangeConsumer_URL' => 'QUEUE_URI',
        ],
        'awsOptions' => [
            'region' => 'eu-west-1',
            'global' => [],
            's3' => [],
        ],
        'ebsr' => [
            'transexchange_publisher' => [
                'consumer_proxy' => 'http://proxy.test',
            ],
            'output_s3_bucket' => 'txc-local-output',
            'txc_consumer_role_arn' => 'arn:aws:iam::000000000000:role/txc-local-consumer-role',
            'max_messages_per_run' => 100,
        ],
    ];

    /**
     * @var m\MockInterface|Xsd
     */
    private m\MockInterface $xmlValidator;

    /**
     * @var m\MockInterface|MapXmlFile
     */
    private m\MockInterface $xmlFilter;

    /**
     * @var m\MockInterface|ParseXmlString
     */
    private m\MockInterface $xmlParser;

    /**
     * @var m\MockInterface|S3Client
     */
    private m\MockInterface $s3Client;

    #[\Override]
    public function setUp(): void
    {
        $this->sut = new TransXChangeConsumer();
        $this->mockRepo('EbsrSubmission', EbsrSubmission::class);

        $logger = new \Dvsa\OlcsTest\SafeLogger();
        $logger->addWriter(new \Laminas\Log\Writer\Mock());
        Logger::setLogger($logger);
    }

    public function testEmptyQueue(): void
    {
        $this->setupMocks();

        $this->mockedSmServices[Queue::class]->shouldReceive('fetchMessages')->andReturn([]);

        $command = TransXChangeConsumerCmd::create([]);
        $response = $this->sut->handleCommand($command);

        $messages = [
            'No messages to process'
        ];

        $this->assertEquals($messages, $response->getMessages());
    }

    public function testMaxMessagesPerRun(): void
    {
        $this->setupMocks(20);

        $messages = array_fill(0, 7, $this->createQueueMessage('Timetable', 'ExampleTimetable.xml'));

        // Ensure that all types of requests are ran.
        array_push(
            $messages,
            $this->createQueueMessage(TransXChangeConsumer::TYPES['Timetable'], 'ExampleTimetable.xml'),
            $this->createQueueMessage(TransXChangeConsumer::TYPES['DvsaRecord'], 'ExampleDvsaRecord.xml'),
            $this->createQueueMessage(TransXChangeConsumer::TYPES['RouteMap'], 'ExampleRouteMap.xml', 'auto'),
        );

        $this->mockedSmServices[Queue::class]
            ->expects('fetchMessages')
            ->with('QUEUE_URI', 10, 60)
            ->twice()
            ->andReturn($messages);

        $this->mockedSmServices[Queue::class]
            ->shouldReceive('deleteMessage')
            ->times(20 /* Two batches of 10 messages*/);

        $busRegistration = m::mock(BusRegEntity::class);
        $busRegistration->shouldReceive('getId')->andReturn(1);
        $busRegistration->shouldReceive('getRegNo')->andReturn(1);

        $licence = m::mock(LicenceEntity::class);
        $licence->shouldReceive('getId')->andReturn(1);
        $busRegistration->shouldReceive('getLicence')->andReturn($licence);

        $createdBy = m::mock(UserEntity::class);
        $createdBy->expects('getId')->times(20)->andReturn(1);
        $busRegistration->expects('getCreatedBy')->times(20)->andReturn($createdBy);

        $ebsrSubmission = m::mock(EbsrSubmissionEntity::class);
        $ebsrSubmission->shouldReceive('getBusReg')->andReturn($busRegistration);

        $this->repoMap['EbsrSubmission']
            ->shouldReceive('fetchById')
            ->andReturn($ebsrSubmission);

        $this->expectedSideEffect(
            Upload::class,
            [],
            new Result(),
            20 /* All types of requests are uploaded. Each request has 1 document. */
        );

        $this->expectedSideEffect(
            UpdateTxcInboxPdfCmd::class,
            [],
            new Result(),
            4 /* Only non-timetable requests are sent to the TxC inbox. */
        );

        $command = TransXChangeConsumerCmd::create([]);
        $this->sut->handleCommand($command);
    }

    public function testMessagesProcessedAndDeletedFromQueue(): void
    {
        $this->setupMocks();

        $messages = array_fill(0, 7, $this->createQueueMessage('Timetable', 'ExampleTimetable.xml'));

        // Ensure that all types of requests are ran.
        array_push(
            $messages,
            $this->createQueueMessage(TransXChangeConsumer::TYPES['Timetable'], 'ExampleTimetable.xml'),
            $this->createQueueMessage(TransXChangeConsumer::TYPES['DvsaRecord'], 'ExampleDvsaRecord.xml'),
            $this->createQueueMessage(TransXChangeConsumer::TYPES['RouteMap'], 'ExampleRouteMap.xml', 'auto'),
        );

        $this->mockedSmServices[Queue::class]
            ->shouldReceive('fetchMessages')
            ->twice()
            ->andReturn($messages);

        // The loop should break when the `fetchMessages` call is empty or below the batch size (10).
        $this->mockedSmServices[Queue::class]
            ->shouldReceive('fetchMessages')
            ->once()
            ->andReturn([]);

        $this->mockedSmServices[Queue::class]
            ->shouldReceive('deleteMessage')
            ->times(20 /* Two batches of 10 messages*/);

        $busRegistration = m::mock(BusRegEntity::class);
        $busRegistration->shouldReceive('getId')->andReturn(1);
        $busRegistration->shouldReceive('getRegNo')->andReturn(1);

        $licence = m::mock(LicenceEntity::class);
        $licence->shouldReceive('getId')->andReturn(1);
        $busRegistration->shouldReceive('getLicence')->andReturn($licence);

        $createdBy = m::mock(UserEntity::class);
        $createdBy->shouldReceive('getId')->andReturn(1);
        $busRegistration->shouldReceive('getCreatedBy')->andReturn($createdBy);

        $ebsrSubmission = m::mock(EbsrSubmissionEntity::class);
        $ebsrSubmission->shouldReceive('getBusReg')->andReturn($busRegistration);

        $this->repoMap['EbsrSubmission']
            ->shouldReceive('fetchById')
            ->andReturn($ebsrSubmission);

        $this->expectedSideEffect(
            Upload::class,
            [],
            new Result(),
            20 /* All types of requests are uploaded. Each request has 1 document. */
        );

        $this->expectedSideEffect(
            UpdateTxcInboxPdfCmd::class,
            [],
            new Result(),
            4 /* Only non-timetable requests are sent to the TxC inbox. */
        );

        $command = TransXChangeConsumerCmd::create([]);
        $this->sut->handleCommand($command);
    }

    /**
     * @@doesNotPerformAssertions
     */
    public function testMessageVisibilityResetIfNoMatchedEbsrSubmission(): void
    {
        $this->setupMocks();

        $messages = [$this->createQueueMessage('Timetable', 'ExampleTimetable.xml')];

        $this->mockedSmServices[Queue::class]
            ->expects('fetchMessages')
            ->with('QUEUE_URI', 10, 60)
            ->andReturn($messages);

        $this->mockedSmServices[Queue::class]
            ->shouldReceive('changeMessageVisibility')
            ->once();

        $this->repoMap['EbsrSubmission']
            ->shouldReceive('fetchById')
            ->andThrow(NotFoundException::class);

        $command = TransXChangeConsumerCmd::create([]);
        $this->sut->handleCommand($command);
    }

    /**
     * @@doesNotPerformAssertions
     */
    public function testMessageVisibilityResetIfNoMatchedBusRegistration(): void
    {
        $this->setupMocks();

        $messages = [$this->createQueueMessage('Timetable', 'ExampleTimetable.xml')];

        $this->mockedSmServices[Queue::class]
            ->expects('fetchMessages')
            ->with('QUEUE_URI', 10, 60)
            ->andReturn($messages);

        $this->mockedSmServices[Queue::class]
            ->expects('changeMessageVisibility');

        $ebsrSubmission = m::mock(EbsrSubmissionEntity::class);
        $ebsrSubmission->shouldReceive('getBusReg')->andReturn(null);

        $this->repoMap['EbsrSubmission']
            ->shouldReceive('fetchById')
            ->andReturn($ebsrSubmission);

        $command = TransXChangeConsumerCmd::create([]);
        $this->sut->handleCommand($command);
    }

    public function testMessageVisibilityResetIfRuntimeException(): void
    {
        $this->setupMocks();

        $messages = [$this->createQueueMessage('Timetable', 'ExampleTimetable.xml')];

        $this->mockedSmServices[Queue::class]
            ->expects('fetchMessages')
            ->with('QUEUE_URI', 10, 60)
            ->andReturn($messages);

        $this->repoMap['EbsrSubmission']->expects('fetchById')->andThrow(RuntimeException::class);

        $command = TransXChangeConsumerCmd::create([]);

        // Exception will be caught, logged, and visibility reset, then rethrown.
        $this->expectException(Exception::class);

        $this->sut->handleCommand($command);
    }

    public function testErrorReturnedByTranXChange(): void
    {
        $this->setupMocks();

        $this->xmlFilter->expects('filter')->andReturn([
            'error' => 'Something went wrong.'
        ]);

        $messages = [$this->createQueueMessage('Timetable', 'ExampleTimetable.xml')];

        $busRegistration = m::mock(BusRegEntity::class);
        $busRegistration->expects('getId')->andReturn(999);
        $busRegistration->expects('getRegNo')->andReturn('123456789/999');

        $licence = m::mock(LicenceEntity::class);
        $licence->expects('getId')->withNoArgs()->andReturn(888);
        $busRegistration->expects('getLicence')->withNoArgs()->andReturn($licence);

        $ebsrSubmission = m::mock(EbsrSubmissionEntity::class);
        $ebsrSubmission->expects('getBusReg')->withNoArgs()->andReturn($busRegistration);

        $this->repoMap['EbsrSubmission']
            ->expects('fetchById')
            ->andReturn($ebsrSubmission);

        $this->mockedSmServices[Queue::class]
            ->expects('fetchMessages')
            ->with('QUEUE_URI', 10, 60)
            ->andReturn($messages);

        $this->mockedSmServices[Queue::class]
            ->expects('deleteMessage');

        $this->expectedSideEffect(
            CreateTaskCmd::class,
            [],
            new Result()
        );

        $command = TransXChangeConsumerCmd::create([]);

        $this->sut->handleCommand($command);
    }

    protected function createQueueMessage(string $type, string $inputDocumentName, ?string $scale = null): array
    {
        $body = "<xml></xml>";

        $attributes = [
            'Type' => ['StringValue' => $type],
            'InputDocumentName' => ['StringValue' => $inputDocumentName],
        ];

        if (!is_null($scale)) {
            $attributes['Scale'] = ['StringValue' => $scale];
        }

        return [
            'Body' => json_encode($body),
            'MessageAttributes' => $attributes,
            'ReceiptHandle' => '1234',
        ];
    }

    protected function setupMocks(int $maxMessages = 100): void
    {
        $transXChangeXmlMapping = m::mock(SpecificationInterface::class);
        $this->xmlParser = m::mock(ParseXmlString::class);
        $this->xmlFilter = m::mock(MapXmlFile::class);
        $this->xmlFilter->expects('setMapping')
            ->with($transXChangeXmlMapping)
            ->andReturnSelf();

        $this->xmlParser->shouldReceive('filter')->andReturn([]);
        $this->xmlFilter->shouldReceive('filter')->byDefault()->andReturn([
            'files' => ['ExampleTimetable.xml']
        ]);

        $this->xmlValidator = m::mock(Xsd::class);
        $this->xmlValidator->shouldReceive('setXsd')->andReturnSelf();
        $this->xmlValidator->shouldReceive('isValid')->andReturn(true);

        $filterPluginManager = m::mock(FilterPluginManager::class);
        $filterPluginManager->shouldReceive('get')->with(ParseXmlString::class)->andReturn($this->xmlParser);
        $filterPluginManager->shouldReceive('get')->with(MapXmlFile::class)->andReturn($this->xmlFilter);

        $validatorManager = m::mock(ValidatorPluginManager::class);
        $validatorManager->shouldReceive('get')->with(Xsd::class)->andReturn($this->xmlValidator);

        $this->config['ebsr']['max_messages_per_run'] = $maxMessages;

        // Parent mocks.
        $this->mockedSmServices = [
            Queue::class => m::mock(Queue::class),
            MessageBuilder::class => m::mock(MessageBuilder::class),
            'FilterManager' => $filterPluginManager,
            'ValidatorManager' => $validatorManager,
            'config' => $this->config
        ];

        $this->repoManager = m::mock(RepositoryServiceManager::class);
        $this->queryHandler = m::mock(QueryHandlerManager::class);
        $this->commandHandler = m::mock(CommandHandlerManager::class);
        $this->identityProvider = m::mock(IdentityProviderInterface::class);

        foreach ($this->repoMap as $alias => $service) {
            $this->repoManager
                ->shouldReceive('get')
                ->with($alias)
                ->andReturn($service);
        }

        $sm = m::mock(ContainerInterface::class);
        $sm->expects('get')->with('RepositoryServiceManager')->times(2)->andReturn($this->repoManager);
        $sm->expects('get')->with('QueryHandlerManager')->andReturn($this->queryHandler);
        $sm->expects('get')->with('CommandHandlerManager')->andReturn($this->commandHandler);
        $sm->expects('get')->with(IdentityProviderInterface::class)->andReturn($this->identityProvider);
        $sm->expects('get')->with('TransExchangePublisherXmlMapping')->andReturn($transXChangeXmlMapping);

        foreach ($this->mockedSmServices as $serviceName => $service) {
            $sm->shouldReceive('get')->with($serviceName)->andReturn($service);
        }

        // AWS Services.
        $stsAssumeRoleResult = new \Aws\Result();
        $stsAssumeRoleResult['Credentials'] = [
            'AccessKeyId' => 'access_key_id',
            'SecretAccessKey' => 'secret_access_key',
            'SessionToken' => 'session_token',
        ];

        m::mock('overload:\Aws\Sts\StsClient')->shouldReceive('AssumeRole')->andReturn($stsAssumeRoleResult);
        m::mock('overload:\Aws\Sqs\SqsClient');
        $this->s3Client = m::mock('overload:\Aws\S3\S3Client');
        $this->s3Client->shouldReceive('getObject')->andReturn(new \Aws\Result(['Body' => '']));

        $this->sut->__invoke($sm, TransXChangeConsumer::class);
        $this->sut->setQueueService($this->mockedSmServices[Queue::class]);
    }
}
