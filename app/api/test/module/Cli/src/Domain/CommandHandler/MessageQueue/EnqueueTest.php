<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Cli\Domain\CommandHandler\MessageQueue;

use Aws\Command;
use Aws\Exception\AwsException;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Cli\Domain\Command\MessageQueue\Enqueue as EnqueueCmd;
use Dvsa\Olcs\Cli\Domain\CommandHandler\MessageQueue\Enqueue;
use Dvsa\Olcs\Queue\Service\Message\CompaniesHouse\CompanyProfile;
use Dvsa\Olcs\Queue\Service\Message\MessageBuilder;
use Dvsa\Olcs\Queue\Service\Queue;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;
use Olcs\Logging\Log\Logger;

#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class EnqueueTest extends AbstractCommandHandlerTestCase
{
    protected $sut;
    protected $queueService;
    protected $messageBuilderService;
    protected $mockSl;
    protected $logger;

    public function setUp(): void
    {
        $this->sut = new Enqueue();

        $this->queueService = m::mock(Queue::class);
        $this->messageBuilderService = m::mock(MessageBuilder::class);

        $this->mockedSmServices = [
            MessageBuilder::class => m::mock(MessageBuilder::class),
            Queue::class => m::mock(Queue::class),
            'config' => [
                'message_queue' => ['companies_house_initial_queue_url' => '_URL_']
            ]
        ];

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $this->mockedSmServices[MessageBuilder::class]->shouldReceive('buildMessages')->andReturn([
            new CompanyProfile(['companyOrLlpNo' => 123], 'queue_url'),
            new CompanyProfile(['companyOrLlpNo' => 754], 'queue_url'),
            new CompanyProfile(['companyOrLlpNo' => 938], 'queue_url'),
            new CompanyProfile(['companyOrLlpNo' => 345], 'queue_url'),
            new CompanyProfile(['companyOrLlpNo' => 456], 'queue_url'),
        ]);
        $this->mockedSmServices[Queue::class]->shouldReceive('sendMessage')->times(3);

        $context = [
            'message' => 'message failed',
            'code' => 'contents error'
        ];
        $exceptionCommand = new Command('name', []);
        $this->mockedSmServices[Queue::class]->shouldReceive('sendMessage')->twice()->andThrow(
            new AwsException('invalid message format', $exceptionCommand, $context)
        );

        $command = EnqueueCmd::create([
            'messageData' => [
                [123],
                [754],
                [938],
                [345],
                [456]
            ],
            'messageType' => 'CompanyProfile',
            $this->mockedSmServices['config']
        ]);

        /**
         * @var $result Result
         */
        $result = $this->sut->handleCommand($command);

        $this->assertEquals(
            [
                '3 messages of type CompanyProfile successfully added to the queue.',
                '2 messages of type CompanyProfile could not be added to the queue.'
            ],
            $result->getMessages()
        );
    }

    public function testExceptionHandling(): void
    {
        $this->mockedSmServices[MessageBuilder::class]->shouldReceive('buildMessages')->andReturn([
            new CompanyProfile(['companyOrLlpNo' => 123], 'queue_url'),
        ]);
        $this->mockedSmServices[Queue::class]->shouldReceive('sendMessage')->times(1)->andThrow(\Exception::class);

        $logWriter = m::mock(\Laminas\Log\Writer\WriterInterface::class);
        $logWriter->shouldReceive('write')->once();
        $this->logger = m::mock(\Dvsa\OlcsTest\SafeLogger::class, [])->makePartial();
        $this->logger->addWriter($logWriter);

        Logger::setLogger($this->logger);

        $command = EnqueueCmd::create([
            'messageData' => [
                [123],
                [754],
                [938],
                [345],
                [456]
            ],
            'messageType' => 'CompanyProfile',
            $this->mockedSmServices['config']
        ]);

        $result = $this->sut->handleCommand($command);
    }
}
