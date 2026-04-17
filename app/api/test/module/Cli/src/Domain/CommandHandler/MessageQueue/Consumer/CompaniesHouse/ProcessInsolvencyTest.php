<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Cli\Domain\CommandHandler\MessageQueue\Consumer\CompaniesHouse;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Domain\Command\Document\GenerateAndStoreWithMultipleAddresses;
use Dvsa\Olcs\Api\Domain\Command\Queue\Create as CreateQueue;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Repository\CompaniesHouseCompany;
use Dvsa\Olcs\Api\Domain\Repository\Organisation as OrganisationRepo;
use Dvsa\Olcs\Api\Domain\Repository\Team;
use Dvsa\Olcs\Api\Entity\CompaniesHouse\CompaniesHouseCompany as CHCompanyEntity;
use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Api\Entity\Organisation\OrganisationUser;
use Dvsa\Olcs\Api\Entity\User\Team as TeamEntity;
use Dvsa\Olcs\Api\Entity\User\User;
use Dvsa\Olcs\Cli\Domain\Command\MessageQueue\Consumer\CompaniesHouse\ProcessInsolvency as ProcessInsolvencyCmd;
use Dvsa\Olcs\Cli\Domain\CommandHandler\MessageQueue\Consumer\AbstractConsumer;
use Dvsa\Olcs\Cli\Domain\CommandHandler\MessageQueue\Consumer\CompaniesHouse\ProcessInsolvency;
use Dvsa\Olcs\CompaniesHouse\Service\Client as CompaniesHouseClient;
use Dvsa\Olcs\Queue\Service\Message\MessageBuilder;
use Dvsa\Olcs\Queue\Service\Queue;
use Dvsa\Olcs\Transfer\Command\Document\PrintLetter;
use Mockery as m;
use PHPUnit\Framework\Attributes\DataProvider;

class ProcessInsolvencyTest extends AbstractCompaniesHouseConsumerTestCase
{
    protected $config = [
        'message_queue' => [
            'ProcessInsolvency_URL' => 'process_insolvency_queue_url'
        ]
    ];

    #[\Override]
    public function setUp(): void
    {
        $this->sut = new ProcessInsolvency();
        $this->mockRepo('CompaniesHouseCompany', CompaniesHouseCompany::class);
        $this->mockRepo('Organisation', OrganisationRepo::class);
        $this->mockRepo('Team', Team::class);
    }

    public function testHandleCommand(): void
    {
        $this->setupStandardService();
        $this->setupMockCHRepo();

        $this->repoMap['Organisation']
            ->shouldReceive('getByCompanyOrLlpNo')
            ->with('1234')
            ->andReturn(
                [
                    m::mock(Organisation::class)
                        ->shouldReceive('getActiveLicences')
                        ->andReturn(new ArrayCollection([]))
                        ->getMock()
                ]
            )->getMock();

        $command = ProcessInsolvencyCmd::create([]);
        $response = $this->sut->handleCommand($command);

        $messages = [
            '3 insolvency practitioners added for company 1234'
        ];

        $this->assertEquals($messages, $response->getMessages());
    }

    #[DataProvider('addressData')]
    public function testHandleCommandCreatesTasks(mixed $licence, mixed $team): void
    {
        $this->setupStandardService();
        $this->setupMockCHRepo();

        $this->repoMap['Organisation']
            ->shouldReceive('getByCompanyOrLlpNo')
            ->with('1234')
            ->andReturn(
                [
                    m::mock(Organisation::class)
                        ->shouldReceive('getActiveLicences')
                        ->andReturn(new ArrayCollection([$licence]))
                        ->getMock()
                ]
            )->getMock();

        $this->repoMap['Team']
            ->shouldReceive('fetchOneByName')
            ->with($team)
            ->andReturn(
                m::mock(TeamEntity::class)
                    ->shouldReceive('getId')
                    ->andReturn(1)
                    ->getMock()
            );

        $this->setupSideEffects(4);

        $command = ProcessInsolvencyCmd::create([]);
        $response = $this->sut->handleCommand($command);

        $messages = [
            '3 insolvency practitioners added for company 1234'
        ];

        $this->assertEquals($messages, $response->getMessages());
    }

    #[DataProvider('emailTestsDataProvider')]
    public function testHandleCommandSendsEmails(mixed $licence): void
    {
        $this->setupStandardService();
        $this->setupMockCHRepo();

        $this->repoMap['Organisation']
            ->shouldReceive('getByCompanyOrLlpNo')
            ->with('1234')
            ->andReturn(
                [
                    m::mock(Organisation::class)
                        ->shouldReceive('getActiveLicences')
                        ->andReturn(new ArrayCollection([$licence]))
                        ->getMock()
                ]
            )->getMock();

        $this->repoMap['Team']
            ->shouldReceive('fetchOneByName')
            ->andReturn(
                m::mock(TeamEntity::class)
                    ->shouldReceive('getId')
                    ->andReturn(1)
                    ->getMock()
            );

        $this->setupSideEffects(4);

        $command = ProcessInsolvencyCmd::create([]);
        $response = $this->sut->handleCommand($command);

        $messages = [
            '3 insolvency practitioners added for company 1234'
        ];

        $this->assertEquals($messages, $response->getMessages());
    }

    public function testHandleCommandSendsEmailsWithNoRegisteredUsers(): void
    {
        $this->setupStandardService();
        $this->setupMockCHRepo();

        $licence = self::getMockLicences()[2];

        $this->repoMap['Organisation']
            ->shouldReceive('getByCompanyOrLlpNo')
            ->with('1234')
            ->andReturn(
                [
                    m::mock(Organisation::class)
                        ->shouldReceive('getActiveLicences')
                        ->andReturn(new ArrayCollection([$licence]))
                        ->getMock()
                ]
            )->getMock();

        $this->repoMap['Team']
            ->shouldReceive('fetchOneByName')
            ->andReturn(
                m::mock(TeamEntity::class)
                    ->shouldReceive('getId')
                    ->andReturn(1)
                    ->getMock()
            );

        $this->setupSideEffects(2);

        $command = ProcessInsolvencyCmd::create([]);
        $response = $this->sut->handleCommand($command);

        $messages = [
            '3 insolvency practitioners added for company 1234'
        ];

        $this->assertEquals($messages, $response->getMessages());
    }

    public function testHandleCommandSendsEmailsWithNoCorrespondenceEmail(): void
    {
        $this->setupStandardService();
        $this->setupMockCHRepo();

        $licence = self::getMockLicences()[3];

        $this->repoMap['Organisation']
            ->shouldReceive('getByCompanyOrLlpNo')
            ->with('1234')
            ->andReturn(
                [
                    m::mock(Organisation::class)
                        ->shouldReceive('getActiveLicences')
                        ->andReturn(new ArrayCollection([$licence]))
                        ->getMock()
                ]
            )->getMock();

        $this->repoMap['Team']
            ->shouldReceive('fetchOneByName')
            ->andReturn(
                m::mock(TeamEntity::class)
                    ->shouldReceive('getId')
                    ->andReturn(1)
                    ->getMock()
            );

        $this->setupSideEffects(1);

        $command = ProcessInsolvencyCmd::create([]);
        $response = $this->sut->handleCommand($command);

        $messages = [
            '3 insolvency practitioners added for company 1234',
            'Unable to send emails: No email addresses found'
        ];

        $this->assertEquals($messages, $response->getMessages());
    }

    public function testHandleCommandNoMessages(): void
    {
        $queueService = m::mock(Queue::class);

        $queueService->shouldReceive('fetchMessages')
            ->with('process_insolvency_queue_url', 1, 1)
            ->andReturnNull()
            ->once();

        $this->mockedSmServices = [
            CompaniesHouseClient::class => m::mock(CompaniesHouseClient::class),
            Queue::class => $queueService,
            MessageBuilder::class => m::mock(MessageBuilder::class),
            'config' => $this->config
        ];

        $this->setupService();

        $command = ProcessInsolvencyCmd::create([]);
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
            ->with('process_insolvency_queue_url', 1, 1)
            ->andReturn([
                [
                    'Body' => '1234',
                    'ReceiptHandle' => 1
                ]
            ])
            ->once();

        $queueService->shouldReceive('deleteMessage')
            ->with('process_insolvency_queue_url', 1)
            ->once();

        return $queueService;
    }

    protected function getMockCompaniesHouseClient(): m\MockInterface
    {
        $mockClient = m::mock(CompaniesHouseClient::class);
        $mockClient->shouldReceive('getInsolvencyDetails')
            ->once()
            ->andReturn($this->mockInsolvencyDataResponse());

        return $mockClient;
    }

    protected function mockInsolvencyDataResponse(): array
    {
        return [
            [
                'practitioners' => [
                    0 => [
                        'role' => 'practitioner',
                        'address' => [
                            'postal_code' => 'postal code',
                            'address_line_1' => 'address line 1',
                            'locality' => 'locality',
                            'address_line_2' => 'address line 2',
                        ],
                        'name' => 'Edwin Hubble',
                    ],
                    1 => [
                        'address' => [
                            'locality' => 'locality',
                            'address_line_1' => 'address line 1',
                            'postal_code' => 'postal code',
                            'address_line_2' => 'address line 2',
                        ],
                        'name' => 'Alice Cooper',
                        'role' => 'practitioner',
                    ],
                    2 => [
                        'address' => [
                            'locality' => 'locality',
                            'address_line_1' => 'address line 1',
                            'postal_code' => 'postal code',
                            'address_line_2' => 'address line 2',
                        ],
                        'name' => 'Alice Cooper',
                        'role' => 'practitioner',
                        'ceased_to_act_on' => ''
                    ],
                    3 => [
                        'address' => [
                            'locality' => 'locality',
                            'address_line_1' => 'address line 1',
                            'postal_code' => 'postal code',
                            'address_line_2' => 'address line 2',
                        ],
                        'name' => 'Alice Cooper',
                        'role' => 'practitioner',
                        'ceased_to_act_on' => '2017-02-02'
                    ],
                    4 => [
                        'address' => [
                            'locality' => 'locality',
                            'address_line_1' => 'address line 1',
                            'postal_code' => 'postal code',
                            'address_line_2' => 'address line 2',
                        ],
                        'name' => 'Alice Cooper',
                        'role' => 'practitioner',
                    ],
                ],
                'number' => '1',
                'type' => 'in-administration',
                'dates' => [
                    0 => [
                        'type' => 'administration-started-on',
                        'date' => '2019-07-24',
                    ],
                ],
            ],
        ];
    }

    private static function getMockLicences(): ArrayCollection
    {
        $mockOrgGB = self::getMockOrganisationWithUserEmails([
            'emailgb1@example.com',
            'emailgb2@example.com'
        ]);

        $mockOrgNI = self::getMockOrganisationWithUserEmails([
            'emailni1@example.com',
            'emailni2@example.com'
        ]);

        $mockOrgGBNoRegisteredUsers = self::getMockOrganisationWithUserEmails([]);

        $mockCorrespondenceDetailsGB = m::mock(ContactDetails::class)
            ->shouldReceive('getEmailAddress')
            ->andReturn('gbcorrespondenceemail@example.com')
            ->getMock();

        $mockCorrespondenceDetailsNI = m::mock(ContactDetails::class)
            ->shouldReceive('getEmailAddress')
            ->andReturn('nicorrespondenceemail@example.com')
            ->getMock();

        $mockCorrespondenceDetailsNoEmail = m::mock(ContactDetails::class)
            ->shouldReceive('getEmailAddress')
            ->andReturnNull()
            ->getMock();

        $mockGBLicence = m::mock(Licence::class)
            ->shouldReceive('isNi')
            ->andReturn(false)
            ->shouldReceive('isRestricted')
            ->andReturn(true)
            ->shouldReceive('isNi')
            ->andReturn(false)
            ->shouldReceive('getId')
            ->andReturn(1)
            ->shouldReceive('isPsv')
            ->andReturn(true)
            ->shouldReceive('isRestricted')
            ->andReturn(false)
            ->shouldReceive('getTranslateToWelsh')
            ->andReturn('Y')
            ->shouldReceive('getCorrespondenceCd')
            ->andReturn($mockCorrespondenceDetailsGB)
            ->shouldReceive('getOrganisation')
            ->andReturn($mockOrgGB)
            ->getMock();

        $mockNiLicence = m::mock(Licence::class)
            ->shouldReceive('isNi')
            ->andReturn(true)
            ->shouldReceive('isSpecialRestricted')
            ->andReturn(true)
            ->shouldReceive('isNi')
            ->andReturn(true)
            ->getMock()
            ->shouldReceive('getId')
            ->andReturn(2)
            ->shouldReceive('isRestricted')
            ->andReturn(false)
            ->shouldReceive('getTranslateToWelsh')
            ->andReturn('N')
            ->shouldReceive('getCorrespondenceCd')
            ->andReturn($mockCorrespondenceDetailsNI)
            ->shouldReceive('getOrganisation')
            ->andReturn($mockOrgNI)
            ->getMock();

        $mockGBLicenceNoRegisteredUsers = m::mock(Licence::class)
            ->shouldReceive('isNi')
            ->andReturn(true)
            ->shouldReceive('isRestricted')
            ->andReturn(true)
            ->shouldReceive('isNi')
            ->andReturn(false)
            ->shouldReceive('getId')
            ->andReturn(1)
            ->shouldReceive('isPsv')
            ->andReturn(true)
            ->shouldReceive('isRestricted')
            ->andReturn(false)
            ->shouldReceive('getTranslateToWelsh')
            ->andReturn('N')
            ->shouldReceive('getCorrespondenceCd')
            ->andReturn($mockCorrespondenceDetailsGB)
            ->shouldReceive('getOrganisation')
            ->andReturn($mockOrgGBNoRegisteredUsers)
            ->getMock();

        $mockGBLicenceNoCorrespondenceEmail = m::mock(Licence::class)
            ->shouldReceive('isNi')
            ->andReturn(true)
            ->shouldReceive('getId')
            ->andReturn(123)
            ->shouldReceive('isNi')
            ->andReturn(false)
            ->shouldReceive('getId')
            ->andReturn(1)
            ->shouldReceive('isPsv')
            ->andReturn(true)
            ->shouldReceive('isRestricted')
            ->andReturn(false)
            ->shouldReceive('getTranslateToWelsh')
            ->andReturn('N')
            ->shouldReceive('getCorrespondenceCd')
            ->andReturn($mockCorrespondenceDetailsNoEmail)
            ->shouldReceive('getOrganisation')
            ->andReturn($mockOrgGBNoRegisteredUsers)
            ->getMock();

        return new ArrayCollection([
            $mockGBLicence,
            $mockNiLicence,
            $mockGBLicenceNoRegisteredUsers,
            $mockGBLicenceNoCorrespondenceEmail
        ]);
    }

    private static function getMockOrganisationWithUserEmails(array $emails): mixed
    {
        $mockUsers = array_map(
            static function ($emailAddress) {
                $mockUserCD = m::Mock(ContactDetails::class)
                    ->shouldReceive('getEmailAddress')
                    ->andReturn($emailAddress)
                    ->getMock();

                $mockUser = m::Mock(User::class)
                    ->shouldReceive('getContactDetails')
                    ->andReturn($mockUserCD)
                    ->getMock();

                return m::Mock(OrganisationUser::class)
                    ->shouldReceive('getUser')
                    ->andReturn($mockUser)
                    ->getMock();
            },
            $emails
        );

        return (m::Mock(Organisation::class)
            ->shouldReceive('getAdminOrganisationUsers')
            ->andReturn(new ArrayCollection($mockUsers))
            ->getMock());
    }

    public static function addressData(): array
    {
        return [
            'GBLicence' => [
                self::getMockLicences()[0],
                ProcessInsolvency::GB_TEAMLEADER_TASK,
            ],
            'NILicence' => [
                self::getMockLicences()[1],
                ProcessInsolvency::NI_TEAMLEADER_TASK,
            ]
        ];
    }

    public static function emailTestsDataProvider(): array
    {
        return [
            'GBLicence' => [
                self::getMockLicences()[0],
            ],
            'NILicence' => [
                self::getMockLicences()[1],
            ]
        ];
    }

    protected function setupStandardService(): void
    {
        $this->mockedSmServices = [
            CompaniesHouseClient::class => $this->getMockCompaniesHouseClient(),
            Queue::class => $this->getMockQueueService(),
            MessageBuilder::class => m::mock(MessageBuilder::class),
            'config' => $this->config
        ];
        $this->setupService();
    }

    private function setupSideEffects(int $numberOfQueueItems): void
    {
        $result = new Result();
        $result->addId('documents', 1, true);
        $result->addId('documents', 2, true);
        $result->addId('documents', 3, true);

        $this->expectedSideEffect(
            GenerateAndStoreWithMultipleAddresses::class,
            [],
            $result
        );

        for ($i = 1; $i < 4; $i++) {
            $this->expectedSideEffect(
                PrintLetter::class,
                [
                    'id' => $i,
                    'method' => PrintLetter::METHOD_PRINT_AND_POST
                ],
                new Result()
            );
        }

        $this->expectedSideEffect(
            CreateQueue::class,
            [],
            new Result(),
            $numberOfQueueItems
        );
    }

    protected function setupMockCHRepo(): void
    {
        $mockCompany = m::mock(CHCompanyEntity::class);
        $mockCompany
            ->shouldReceive('getId')
            ->andReturn(123);
        $mockCompany
            ->shouldReceive('getCompanyNumber')
            ->andReturn('1234');
        $mockCompany
            ->shouldReceive('getCompanyStatus')
            ->andReturn('TEST');
        $mockCompany
            ->shouldReceive('setInsolvencyProcessed')
            ->with(true);
        $mockCompany->shouldReceive('setInsolvencyPractitioners')
            ->once();

        $this->repoMap['CompaniesHouseCompany']
            ->shouldReceive('getLatestByCompanyNumber')
            ->once()
            ->andReturn($mockCompany);

        $this->repoMap['CompaniesHouseCompany']
            ->shouldReceive('save')
            ->with($mockCompany)
            ->twice();
    }
}
