<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Cli\Domain\CommandHandler\MessageQueue\Consumer\CompaniesHouse;

use Dvsa\Olcs\Api\Domain\Command\Email\SendFailedOrganisationsList;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Repository\MessageFailures as MessageFailuresRepo;
use Dvsa\Olcs\Api\Domain\Repository\Organisation as OrganisationRepo;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Cli\Domain\Command\MessageQueue\Consumer\CompaniesHouse\CompanyProfileDlq as CompanyProfileDlqCmd;
use Dvsa\Olcs\Cli\Domain\CommandHandler\MessageQueue\Consumer\CompaniesHouse\CompanyProfileDlq;
use Dvsa\Olcs\Queue\Service\Message\MessageBuilder;
use Dvsa\Olcs\Queue\Service\Queue;
use Mockery as m;

class CompanyProfileDlqTest extends AbstractCompaniesHouseConsumerTestCase
{
    protected $config = [
        'message_queue' => [
            'CompanyProfileDlq_URL' => 'company_profile_dlq_url'
        ],
        'company_house_dlq' => [
            'notification_email_address' => 'test@test.com'
        ]
    ];

    #[\Override]
    public function setUp(): void
    {
        $this->sut = new CompanyProfileDlq();
        $this->mockRepo('MessageFailures', MessageFailuresRepo::class);
        $this->mockRepo('Organisation', OrganisationRepo::class);
    }

    public function testHandleCommandSendsEmail(): void
    {
        $this->setUpServices();

        $this->repoMap['MessageFailures']
            ->shouldReceive('saveOnFlush')
            ->times(4);

        $this->repoMap['MessageFailures']
            ->shouldReceive('flushAll')
            ->once();

        $this->repoMap['Organisation']
            ->shouldReceive('getByCompanyOrLlpNo')
            ->andReturn([m::mock(Organisation::class)])
            ->times(4)
            ->getMock();

        $result = new Result();
        $result->addMessage('Email sent');
        $this->expectedSideEffect(
            SendFailedOrganisationsList::class,
            [
                'organisationNumbers' => ['0000', '1111', '2222', '3333'],
                'emailAddress' => 'test@test.com',
                'emailSubject' => 'Companies House Company Profile process failure - list of those that failed'
            ],
            $result
        );

        $command = CompanyProfileDlqCmd::create([]);
        $response = $this->sut->handleCommand($command);

        $messages = [
            'Email sent'
        ];

        $this->assertEquals($messages, $response->getMessages());
    }

    public function testHandleCommandRemovesDuplicateOrganisationNumbersFromEmail(): void
    {
        $this->setUpServicesWithDuplicateQueueEntries();

        $this->repoMap['MessageFailures']
            ->shouldReceive('saveOnFlush')
            ->times(2);

        $this->repoMap['MessageFailures']
            ->shouldReceive('flushAll')
            ->once();

        $this->repoMap['Organisation']
            ->shouldReceive('getByCompanyOrLlpNo')
            ->andReturn([m::mock(Organisation::class)])
            ->times(2)
            ->getMock();

        $result = new Result();
        $result->addMessage('Email sent');
        $this->expectedSideEffect(
            SendFailedOrganisationsList::class,
            [
                'organisationNumbers' => ['0000'],
                'emailAddress' => 'test@test.com',
                'emailSubject' => 'Companies House Company Profile process failure - list of those that failed'
            ],
            $result
        );

        $command = CompanyProfileDlqCmd::create([]);
        $response = $this->sut->handleCommand($command);

        $messages = [
            'Email sent'
        ];

        $this->assertEquals($messages, $response->getMessages());
    }

    public function testHandleCommandWhenQueueIsEmpty(): void
    {
        $this->setupServicesWithEmptyQueue();

        $command = CompanyProfileDlqCmd::create([]);
        $response = $this->sut->handleCommand($command);

        $messages = ['No messages to process'];
        $flags = ['no_messages' => true];

        $this->assertEquals($messages, $response->getMessages());
        $this->assertEquals($flags, $response->getFlags());
    }

    protected function getMockQueueService(): mixed
    {
        $queueService = m::mock(Queue::class);

        $firstFetchResult = [
            [
                'Body' => '0000',
                'ReceiptHandle' => 1
            ],
            [
                'Body' => '1111',
                'ReceiptHandle' => 1
            ]
        ];

        $secondFetchResult = [
            [
                'Body' => '2222',
                'ReceiptHandle' => 1
            ],
            [
                'Body' => '3333',
                'ReceiptHandle' => 1
            ]
        ];

        $thirdFetchResult = null;

        $queueService->shouldReceive('fetchMessages')
            ->with('company_profile_dlq_url', 10, 1)
            ->andReturn(
                $firstFetchResult,
                $secondFetchResult,
                $thirdFetchResult
            )
            ->times(3);

        $queueService->shouldReceive('deleteMessage')
            ->with('company_profile_dlq_url', 1)
            ->times(4);

        return $queueService;
    }

    protected function setUpServices(): void
    {
        $this->mockedSmServices = [
            Queue::class => $this->getMockQueueService(),
            MessageBuilder::class => m::mock(MessageBuilder::class),
            'config' => $this->config
        ];
        $this->setupService();
    }

    protected function getEmptyQueueService(): mixed
    {
        $queueService = m::mock(Queue::class);

        $queueService->shouldReceive('fetchMessages')
            ->with('company_profile_dlq_url', 10, 1)
            ->andReturn([])
            ->once();

        return $queueService;
    }

    protected function setupServicesWithEmptyQueue(): void
    {
        $this->mockedSmServices = [
            Queue::class => $this->getEmptyQueueService(),
            MessageBuilder::class => m::mock(MessageBuilder::class),
            'config' => $this->config
        ];
        $this->setupService();
    }

    protected function setUpServicesWithDuplicateQueueEntries(): void
    {
        $this->mockedSmServices = [
            Queue::class => $this->getMockQueueServiceWithDuplicateEntries(),
            MessageBuilder::class => m::mock(MessageBuilder::class),
            'config' => $this->config
        ];
        $this->setupService();
    }

    protected function getMockQueueServiceWithDuplicateEntries(): mixed
    {
        $queueService = m::mock(Queue::class);

        $firstFetchResult = [
            [
                'Body' => '0000',
                'ReceiptHandle' => 1
            ],
            [
                'Body' => '0000',
                'ReceiptHandle' => 1
            ]
        ];

        $secondFetchResult = null;

        $queueService->shouldReceive('fetchMessages')
            ->with('company_profile_dlq_url', 10, 1)
            ->andReturn(
                $firstFetchResult,
                $secondFetchResult
            )
            ->times(2);

        $queueService->shouldReceive('deleteMessage')
            ->with('company_profile_dlq_url', 1)
            ->times(2);

        return $queueService;
    }
}
