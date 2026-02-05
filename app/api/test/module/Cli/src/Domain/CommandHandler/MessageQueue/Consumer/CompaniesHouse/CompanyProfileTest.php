<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Cli\Domain\CommandHandler\MessageQueue\Consumer\CompaniesHouse;

use Dvsa\Olcs\Api\Domain\Command\CompaniesHouse\Compare;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Repository\CompaniesHouseCompany;
use Dvsa\Olcs\Api\Entity\CompaniesHouse\CompaniesHouseCompany as CHCompanyEntity;
use Dvsa\Olcs\Cli\Domain\Command\MessageQueue\Consumer\CompaniesHouse\CompanyProfile as CompanyProfileCmd;
use Dvsa\Olcs\Cli\Domain\CommandHandler\MessageQueue\Consumer\AbstractConsumer;
use Dvsa\Olcs\Cli\Domain\CommandHandler\MessageQueue\Consumer\CompaniesHouse\CompanyProfile;
use Dvsa\Olcs\Queue\Service\Message\CompaniesHouse\ProcessInsolvency;
use Dvsa\Olcs\Queue\Service\Message\MessageBuilder;
use Dvsa\Olcs\Queue\Service\Queue;
use Mockery as m;

class CompanyProfileTest extends AbstractCompaniesHouseConsumerTestCase
{
    protected $config = [
        'message_queue' => [
            'CompanyProfile_URL' => 'company_profile_queue_url',
            'ProcessInsolvency_URL' => 'process_insolvency_queue_url'
        ]
    ];

    #[\Override]
    public function setUp(): void
    {
        $this->sut = new CompanyProfile();
        $this->mockRepo('CompaniesHouseCompany', CompaniesHouseCompany::class);
    }

    public function testHandleCommandNotInsolvent(): void
    {
        $this->setupStandardService();

        $compareResult = new Result();
        $compareResult->setFlag('isInsolvent', false);
        $this->expectedSideEffect(Compare::class, ['companyNumber' => 1234], $compareResult);

        $mockCompany = m::mock(CHCompanyEntity::class);
        $mockCompany->shouldReceive('getInsolvencyProcessed')
            ->andReturn(0);

        $this->repoMap['CompaniesHouseCompany']
            ->shouldReceive('getLatestByCompanyNumber')
            ->once()
            ->andReturn($mockCompany);

        $this->mockedSmServices[Queue::class]
            ->shouldReceive('deleteMessage')
            ->with('company_profile_queue_url', 'ab123');

        $cmd = CompanyProfileCmd::create([]);
        $this->sut->handleCommand($cmd);
    }

    public function testHandleCommandInsolventProcessed(): void
    {
        $this->setupStandardService();

        $compareResult = new Result();
        $compareResult->setFlag('isInsolvent', true);
        $this->expectedSideEffect(Compare::class, ['companyNumber' => 1234], $compareResult);

        $mockCompany = m::mock(CHCompanyEntity::class);
        $mockCompany->shouldReceive('getInsolvencyProcessed')
            ->andReturn(1);

        $this->repoMap['CompaniesHouseCompany']
            ->shouldReceive('getLatestByCompanyNumber')
            ->once()
            ->andReturn($mockCompany);

        $this->mockedSmServices[Queue::class]
            ->shouldReceive('deleteMessage')
            ->with('company_profile_queue_url', 'ab123');

        $cmd = CompanyProfileCmd::create([]);
        $this->sut->handleCommand($cmd);
    }

    public function testHandleCommandInsolventNotProcessed(): void
    {
        $this->setupStandardService();

        $compareResult = new Result();
        $compareResult->setFlag('isInsolvent', true);
        $this->expectedSideEffect(Compare::class, ['companyNumber' => 1234], $compareResult);

        $mockCompany = m::mock(CHCompanyEntity::class);
        $mockCompany->shouldReceive('getInsolvencyProcessed')
            ->andReturn(0);

        $this->repoMap['CompaniesHouseCompany']
            ->shouldReceive('getLatestByCompanyNumber')
            ->once()
            ->andReturn($mockCompany);

        $message = new ProcessInsolvency(
            ['companyOrLlpNo' => 1234],
            'process_insolvency_queue_url'
        );

        $this->mockedSmServices[MessageBuilder::class]
            ->shouldReceive('buildMessage')
            ->with(
                ['companyOrLlpNo' => 1234],
                ProcessInsolvency::class,
                $this->config['message_queue']
            )
            ->andReturn($message);

        $this->mockedSmServices[Queue::class]
            ->shouldReceive('sendMessage')
            ->with($message->toArray());

        $this->mockedSmServices[Queue::class]
            ->shouldReceive('deleteMessage')
            ->with('company_profile_queue_url', 'ab123');

        $cmd = CompanyProfileCmd::create([]);
        $this->sut->handleCommand($cmd);
    }

    public function testHandleCommandNoMessages(): void
    {
        $queueService = m::mock(Queue::class);

        $queueService->shouldReceive('fetchMessages')
            ->with('company_profile_queue_url', 1, 1)
            ->andReturnNull();

        $this->mockedSmServices = [
            Queue::class => $queueService,
            MessageBuilder::class => m::mock(MessageBuilder::class),
            'config' => $this->config
        ];
        $this->setupService();

        $command = CompanyProfileCmd::create([]);
        $response = $this->sut->handleCommand($command);

        $messages = [
            AbstractConsumer::NOTHING_TO_PROCESS_MESSAGE
        ];

        $this->assertEquals($messages, $response->getMessages());
    }

    protected function getMockQueueService(): mixed
    {
        $queueService = m::mock(Queue::class);

        $queueService->shouldReceive('fetchMessages')
            ->with('company_profile_queue_url', 1, 1)
            ->andReturn([
                [
                    'Body' => '1234',
                    'ReceiptHandle' => 'ab123'
                ]
            ])
            ->once();

        return $queueService;
    }

    protected function setupStandardService(): void
    {
        $this->mockedSmServices = [
            Queue::class => $this->getMockQueueService(),
            MessageBuilder::class => m::mock(MessageBuilder::class),
            'config' => $this->config,
        ];
        $this->setupService();
    }
}
