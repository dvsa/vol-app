<?php

declare(strict_types=1);

/**
 * Create User Test
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\User;

use Dvsa\Contracts\Auth\Exceptions\ClientException;
use Dvsa\Olcs\Api\Domain\Command\Email\SendUserCreated as SendUserCreatedDto;
use Dvsa\Olcs\Api\Domain\Command\Email\SendUserTemporaryPassword as SendUserTemporaryPasswordDto;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Repository\Application;
use Dvsa\Olcs\Api\Domain\Repository\ContactDetails;
use Dvsa\Olcs\Api\Domain\Repository\Licence;
use Dvsa\Olcs\Api\Domain\Repository\User;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails as ContactDetailsEntity;
use Dvsa\Olcs\Api\Entity\ContactDetails\Country;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Entity\User\Permission as PermissionEntity;
use Dvsa\Olcs\Api\Entity\User\Role as RoleEntity;
use Dvsa\Olcs\Api\Entity\User\Team;
use Dvsa\Olcs\Api\Entity\User\User as UserEntity;
use Dvsa\Olcs\Api\Rbac\Identity;
use Dvsa\Olcs\Auth\Service\PasswordService;
use Dvsa\Olcs\Transfer\Command\User\CreateUser as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\CommandHandler\User\CreateUser as Sut;
use Laminas\Authentication\Adapter\ValidatableAdapterInterface;
use LmcRbacMvc\Service\AuthorizationService;
use Mockery as m;

/**
 * Create User Test
 */
class CreateUserTest extends AbstractCommandHandlerTestCase
{
    /**
     * @var ValidatableAdapterInterface|m\LegacyMockInterface|m\MockInterface
     */
    private $mockedAdapter;

    public function setUp(): void
    {
        $mockedPasswordService = m::mock(PasswordService::class);
        $mockedPasswordService->shouldReceive('generatePassword')->andReturn('abcdef123456');

        $this->mockedAdapter = m::mock(ValidatableAdapterInterface::class);

        $this->sut = new Sut($mockedPasswordService, $this->mockedAdapter);
        $this->mockRepo('User', User::class);
        $this->mockRepo('Application', Application::class);
        $this->mockRepo('ContactDetails', ContactDetails::class);
        $this->mockRepo('Licence', Licence::class);

        $this->mockedSmServices = [
            AuthorizationService::class => m::mock(AuthorizationService::class),
        ];

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            ContactDetailsEntity::CONTACT_TYPE_USER
        ];

        $this->references = [
            Country::class => [
                'GB' => m::mock(Country::class)
            ],
            Team::class => [
                '1' => m::mock(Team::class)
            ],
        ];

        parent::initReferences();
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function testHandleCommandInternalUser(): void
    {
        $userId = 111;
        $licenceNumber = 'LIC123';

        $data = [
            'userType' => UserEntity::USER_TYPE_INTERNAL,
            'team' => 1,
            'licenceNumber' => $licenceNumber,
            'loginId' => 'login_id',
            'roles' => [RoleEntity::ROLE_INTERNAL_CASE_WORKER],
            'contactDetails' => [
                'emailAddress' => 'test1@test.me',
                'person' => [
                    'title' => m::mock(RefData::class),
                    'forename' => 'updated forename',
                    'familyName' => 'updated familyName',
                    'birthDate' => '1975-12-12',
                ],
                'address' => [
                    'addressLine1' => 'a12',
                    'addressLine2' => 'a23',
                    'addressLine3' => 'a34',
                    'addressLine4' => 'a45',
                    'town' => 'town',
                    'postcode' => 'LS1 2AB',
                    'countryCode' => m::mock(Country::class),
                ],
                'phoneContacts' => [
                    [
                        'phoneContactType' => m::mock(RefData::class),
                        'phoneNumber' => '111',
                    ],
                    [
                        'phoneContactType' => m::mock(RefData::class),
                        'phoneNumber' => '222',
                    ]
                ],
            ],
        ];

        $command = Cmd::create($data);

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('isGranted')
            ->once()
            ->with(PermissionEntity::CAN_MANAGE_USER_INTERNAL, null)
            ->andReturn(true)
            ->shouldReceive('getIdentity->getUser->isAllowedToPerformActionOnRoles')
            ->andReturn(true);

        $this->mockedAdapter->shouldReceive('register')->once();

        $this->repoMap['User']
            ->shouldReceive('populateRefDataReference')
            ->once()
            ->andReturnUsing(
                function ($data) {
                    $role = m::mock(RoleEntity::class);
                    $role->shouldReceive('getRole')
                        ->once()
                        ->andReturn(RoleEntity::ROLE_INTERNAL_CASE_WORKER);

                    $data['roles'] = [$role];

                    return $data;
                }
            );

        $this->repoMap['ContactDetails']->shouldReceive('populateRefDataReference')
            ->once()
            ->with($data['contactDetails'])
            ->andReturn($data['contactDetails']);

        /** @var UserEntity $savedUser */
        $savedUser = null;

        $this->repoMap['User']->shouldReceive('save')
            ->once()
            ->with(m::type(UserEntity::class))
            ->andReturnUsing(
                function (UserEntity $user) use (&$savedUser, $userId) {
                    $user->setId($userId);
                    $savedUser = $user;

                    $this->expectedSideEffect(
                        SendUserCreatedDto::class,
                        [
                            'user' => $userId
                        ],
                        new Result()
                    );

                    $this->expectedSideEffect(
                        SendUserTemporaryPasswordDto::class,
                        [
                            'user' => $userId,
                            'password' => 'abcdef123456',
                        ],
                        new Result()
                    );
                }
            );

        $this->repoMap['User']
            ->shouldReceive('disableSoftDeleteable')
            ->once()
            ->shouldReceive('fetchByLoginId')
            ->once()
            ->with('login_id')
            ->andReturn([])
            ->shouldReceive('enableSoftDeleteable')
            ->once();

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'user' => $userId,
            ],
            'messages' => [
                'User created successfully'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());

        $this->assertInstanceOf(ContactDetailsEntity::class, $savedUser->getContactDetails());
        $this->assertEquals(
            UserEntity::USER_TYPE_INTERNAL,
            $savedUser->getUserType()
        );
        $this->assertEquals(
            ContactDetailsEntity::CONTACT_TYPE_USER,
            $savedUser->getContactDetails()->getContactType()->getId()
        );
        $this->assertEquals(
            $data['contactDetails']['emailAddress'],
            $savedUser->getContactDetails()->getEmailAddress()
        );
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function testHandleCommand(): void
    {
        $userId = 111;
        $licenceNumber = 'LIC123';

        $data = [
            'userType' => UserEntity::USER_TYPE_OPERATOR,
            'team' => 1,
            'licenceNumber' => $licenceNumber,
            'loginId' => 'login_id',
            'contactDetails' => [
                'emailAddress' => 'test1@test.me',
                'person' => [
                    'title' => m::mock(RefData::class),
                    'forename' => 'updated forename',
                    'familyName' => 'updated familyName',
                    'birthDate' => '1975-12-12',
                ],
                'address' => [
                    'addressLine1' => 'a12',
                    'addressLine2' => 'a23',
                    'addressLine3' => 'a34',
                    'addressLine4' => 'a45',
                    'town' => 'town',
                    'postcode' => 'LS1 2AB',
                    'countryCode' => m::mock(Country::class),
                ],
                'phoneContacts' => [
                    [
                        'phoneContactType' => m::mock(RefData::class),
                        'phoneNumber' => '111',
                    ],
                    [
                        'phoneContactType' => m::mock(RefData::class),
                        'phoneNumber' => '222',
                    ]
                ],
            ],
        ];

        $command = Cmd::create($data);

        $this->mockedSmServices[AuthorizationService::class]
            ->shouldReceive('isGranted')
            ->once()
            ->with(PermissionEntity::CAN_MANAGE_USER_INTERNAL, null)
            ->andReturn(true)
            ->shouldReceive('getIdentity')
            ->andReturn($this->getMockIdentity());

        $this->mockedAdapter->shouldReceive('register')->once();

        $this->repoMap['User']
            ->shouldReceive('disableSoftDeleteable')
            ->once()
            ->shouldReceive('fetchByLoginId')
            ->once()
            ->with($data['loginId'])
            ->andReturn([])
            ->shouldReceive('enableSoftDeleteable')
            ->once();

        $this->repoMap['User']
            ->shouldReceive('populateRefDataReference')
            ->once()
            ->andReturn($data);

        $this->repoMap['ContactDetails']->shouldReceive('populateRefDataReference')
            ->once()
            ->with($data['contactDetails'])
            ->andReturn($data['contactDetails']);

        /** @var LicenceEntity $licence */
        $licence = m::mock(LicenceEntity::class)->makePartial();
        $licence->shouldReceive('getOrganisation')
            ->once();

        $this->repoMap['Licence']->shouldReceive('fetchByLicNo')
            ->once()
            ->with($licenceNumber)
            ->andReturn($licence);

        /** @var UserEntity $savedUser */
        $savedUser = null;

        $this->repoMap['User']->shouldReceive('save')
            ->once()
            ->with(m::type(UserEntity::class))
            ->andReturnUsing(
                function (UserEntity $user) use (&$savedUser, $userId) {
                    $user->setId($userId);
                    $savedUser = $user;

                    $this->expectedSideEffect(
                        SendUserCreatedDto::class,
                        [
                            'user' => $userId
                        ],
                        new Result()
                    );

                    $this->expectedSideEffect(
                        SendUserTemporaryPasswordDto::class,
                        [
                            'user' => $userId,
                            'password' => 'abcdef123456',
                        ],
                        new Result()
                    );
                }
            );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'user' => $userId,
            ],
            'messages' => [
                'User created successfully'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());

        $this->assertInstanceOf(ContactDetailsEntity::class, $savedUser->getContactDetails());
        $this->assertEquals(
            UserEntity::USER_TYPE_OPERATOR,
            $savedUser->getUserType()
        );
        $this->assertEquals(
            ContactDetailsEntity::CONTACT_TYPE_USER,
            $savedUser->getContactDetails()->getContactType()->getId()
        );
        $this->assertEquals(
            $data['contactDetails']['emailAddress'],
            $savedUser->getContactDetails()->getEmailAddress()
        );
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function testHandleCommandForTm(): void
    {
        $userId = 111;
        $applicationId = 3;

        $data = [
            'userType' => UserEntity::USER_TYPE_TRANSPORT_MANAGER,
            'application' => $applicationId,
            'transport_manager' => 1,
            'loginId' => 'login_id',
            'contactDetails' => [
                'emailAddress' => 'test1@test.me',
                'person' => [
                    'title' => m::mock(RefData::class),
                    'forename' => 'updated forename',
                    'familyName' => 'updated familyName',
                    'birthDate' => '1975-12-12',
                ],
                'address' => [
                    'addressLine1' => 'a12',
                    'addressLine2' => 'a23',
                    'addressLine3' => 'a34',
                    'addressLine4' => 'a45',
                    'town' => 'town',
                    'postcode' => 'LS1 2AB',
                    'countryCode' => m::mock(Country::class),
                ],
                'phoneContacts' => [
                    [
                        'phoneContactType' => m::mock(RefData::class),
                        'phoneNumber' => '111',
                    ],
                    [
                        'phoneContactType' => m::mock(RefData::class),
                        'phoneNumber' => '222',
                    ]
                ],
            ],
        ];

        $command = Cmd::create($data);

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('isGranted')
            ->once()
            ->with(PermissionEntity::CAN_MANAGE_USER_INTERNAL, null)
            ->andReturn(true)
            ->shouldReceive('getIdentity')
            ->andReturn($this->getMockIdentity());

        $this->mockedAdapter->shouldReceive('register')->once();

        $this->repoMap['User']
            ->shouldReceive('disableSoftDeleteable')
            ->once()
            ->shouldReceive('fetchByLoginId')
            ->once()
            ->with($data['loginId'])
            ->andReturn([])
            ->shouldReceive('enableSoftDeleteable')
            ->once();

        $this->repoMap['User']
            ->shouldReceive('populateRefDataReference')
            ->once()
            ->andReturn($data);

        $this->repoMap['ContactDetails']->shouldReceive('populateRefDataReference')
            ->once()
            ->with($data['contactDetails'])
            ->andReturn($data['contactDetails']);

        /** @var LicenceEntity $licence */
        $licence = m::mock(LicenceEntity::class)->makePartial();
        $licence->shouldReceive('getOrganisation')
            ->once();

        /** @var ApplicationEntity $application */
        $application = m::mock(ApplicationEntity::class)->makePartial();
        $application->shouldReceive('getLicence')
            ->once()
            ->andReturn($licence);

        $this->repoMap['Application']->shouldReceive('fetchWithLicenceAndOrg')
            ->once()
            ->with($applicationId)
            ->andReturn($application);

        /** @var UserEntity $savedUser */
        $savedUser = null;

        $this->repoMap['User']->shouldReceive('save')
            ->once()
            ->with(m::type(UserEntity::class))
            ->andReturnUsing(
                function (UserEntity $user) use (&$savedUser, $userId) {
                    $user->setId($userId);
                    $savedUser = $user;

                    $this->expectedSideEffect(
                        SendUserCreatedDto::class,
                        [
                            'user' => $userId
                        ],
                        new Result()
                    );

                    $this->expectedSideEffect(
                        SendUserTemporaryPasswordDto::class,
                        [
                            'user' => $userId,
                            'password' => 'abcdef123456',
                        ],
                        new Result()
                    );
                }
            );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'user' => $userId,
            ],
            'messages' => [
                'User created successfully'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());

        $this->assertInstanceOf(ContactDetailsEntity::class, $savedUser->getContactDetails());
        $this->assertEquals(
            UserEntity::USER_TYPE_TRANSPORT_MANAGER,
            $savedUser->getUserType()
        );
        $this->assertEquals(
            ContactDetailsEntity::CONTACT_TYPE_USER,
            $savedUser->getContactDetails()->getContactType()->getId()
        );
        $this->assertEquals(
            $data['contactDetails']['emailAddress'],
            $savedUser->getContactDetails()->getEmailAddress()
        );
    }

    public function testHandleCommandThrowsIncorrectPermissionException(): void
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\ForbiddenException::class);

        $data = [
            'id' => 111,
            'version' => 1,
        ];

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('isGranted')
            ->once()
            ->with(PermissionEntity::CAN_MANAGE_USER_INTERNAL, null)
            ->andReturn(false)
            ->shouldReceive('getIdentity')
            ->andReturn($this->getMockIdentity());

        $this->repoMap['User']
            ->shouldReceive('fetchById')
            ->never()
            ->shouldReceive('save')
            ->never();

        $command = Cmd::create($data);

        $this->sut->handleCommand($command);
    }

    public function testHandleCommandThrowsUsernameExistsException(): void
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\ValidationException::class);

        $data = [
            'loginId' => 'login_id',
        ];

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('isGranted')
            ->once()
            ->with(PermissionEntity::CAN_MANAGE_USER_INTERNAL, null)
            ->andReturn(true)
            ->shouldReceive('getIdentity')
            ->andReturn($this->getMockIdentity());

        $command = Cmd::create($data);

        $this->repoMap['User']
            ->shouldReceive('disableSoftDeleteable')
            ->once()
            ->shouldReceive('fetchByLoginId')
            ->once()
            ->with($data['loginId'])
            ->andReturn([m::mock(UserEntity::class)])
            ->shouldReceive('enableSoftDeleteable')
            ->once();

        $this->sut->handleCommand($command);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('handleCommandThrowsNoOrgExceptionProvider')]
    public function testHandleCommandThrowsNoOrgException(mixed $userType): void
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\ValidationException::class);

        $data = [
            'userType' => $userType,
            'loginId' => 'login_id',
        ];

        $command = Cmd::create($data);

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('isGranted')
            ->once()
            ->with(PermissionEntity::CAN_MANAGE_USER_INTERNAL, null)
            ->andReturn(true)
            ->shouldReceive('getIdentity')
            ->andReturn($this->getMockIdentity());

        $this->repoMap['User']
            ->shouldReceive('disableSoftDeleteable')
            ->once()
            ->shouldReceive('fetchByLoginId')
            ->once()
            ->with($data['loginId'])
            ->andReturn([])
            ->shouldReceive('enableSoftDeleteable')
            ->once();

        $this->sut->handleCommand($command);
    }

    /**
     * @return array
     */
    public static function handleCommandThrowsNoOrgExceptionProvider(): array
    {
        return [
            [UserEntity::USER_TYPE_OPERATOR],
            [UserEntity::USER_TYPE_TRANSPORT_MANAGER]
        ];
    }


    public function testHandleCommandThrowsExceptionCannotStoreUser(): void
    {
        $userId = 111;
        $applicationId = 3;

        $data = [
            'userType' => UserEntity::USER_TYPE_TRANSPORT_MANAGER,
            'application' => $applicationId,
            'transport_manager' => 1,
            'loginId' => 'login_id',
            'contactDetails' => [
                'emailAddress' => 'test1@test.me',
                'person' => [
                    'title' => m::mock(RefData::class),
                    'forename' => 'updated forename',
                    'familyName' => 'updated familyName',
                    'birthDate' => '1975-12-12',
                ],
                'address' => [
                    'addressLine1' => 'a12',
                    'addressLine2' => 'a23',
                    'addressLine3' => 'a34',
                    'addressLine4' => 'a45',
                    'town' => 'town',
                    'postcode' => 'LS1 2AB',
                    'countryCode' => m::mock(Country::class),
                ],
                'phoneContacts' => [
                    [
                        'phoneContactType' => m::mock(RefData::class),
                        'phoneNumber' => '111',
                    ],
                    [
                        'phoneContactType' => m::mock(RefData::class),
                        'phoneNumber' => '222',
                    ]
                ],
            ],
        ];

        $command = Cmd::create($data);

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('isGranted')
            ->once()
            ->with(PermissionEntity::CAN_MANAGE_USER_INTERNAL, null)
            ->andReturn(true)
            ->shouldReceive('getIdentity')
            ->andReturn($this->getMockIdentity());

        $this->mockedAdapter->shouldReceive('register')->once()->andThrow(ClientException::class);

        $this->repoMap['User']
            ->shouldReceive('disableSoftDeleteable')
            ->once()
            ->shouldReceive('fetchByLoginId')
            ->once()
            ->with($data['loginId'])
            ->andReturn([])
            ->shouldReceive('enableSoftDeleteable')
            ->once();

        $this->repoMap['User']
            ->shouldReceive('populateRefDataReference')
            ->once()
            ->andReturn($data);

        $this->repoMap['User']
            ->shouldReceive('delete')
            ->once();

        $this->repoMap['ContactDetails']->shouldReceive('populateRefDataReference')
            ->once()
            ->with($data['contactDetails'])
            ->andReturn($data['contactDetails']);

        /** @var LicenceEntity $licence */
        $licence = m::mock(LicenceEntity::class)->makePartial();
        $licence->shouldReceive('getOrganisation')
            ->once();

        /** @var ApplicationEntity $application */
        $application = m::mock(ApplicationEntity::class)->makePartial();
        $application->shouldReceive('getLicence')
            ->once()
            ->andReturn($licence);

        $this->repoMap['Application']->shouldReceive('fetchWithLicenceAndOrg')
            ->once()
            ->with($applicationId)
            ->andReturn($application);

        $this->repoMap['User']->shouldReceive('save')
            ->once()
            ->with(m::type(UserEntity::class));

        $this->expectException(\Exception::class);

        $this->sut->handleCommand($command);
    }

    private function getMockIdentity(): m\MockInterface
    {
        $mockUser = m::mock(UserEntity::class)
            ->shouldReceive('isAllowedToPerformActionOnRoles')
            ->andReturn(true)
            ->getMock();

        return m::mock(Identity::class)
            ->shouldReceive('getUser')
            ->andReturn($mockUser)
            ->getMock();
    }
}
