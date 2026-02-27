<?php

declare(strict_types=1);

/**
 * Update User Selfserve Test
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\User;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Exception\BadRequestException;
use Dvsa\Olcs\Api\Domain\Repository\ContactDetails;
use Dvsa\Olcs\Api\Domain\Repository\User;
use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails as ContactDetailsEntity;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Entity\User\User as UserEntity;
use Dvsa\Olcs\Api\Rbac\JWTIdentityProvider;
use Dvsa\Olcs\Api\Service\EventHistory\Creator as EventHistoryCreator;
use Dvsa\Olcs\Auth\Service\PasswordService;
use Dvsa\Olcs\Transfer\Command\User\UpdateUserSelfserve as Cmd;
use Dvsa\Olcs\Transfer\Service\CacheEncryption;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\CommandHandler\User\UpdateUserSelfserve as Sut;
use Laminas\Authentication\Adapter\ValidatableAdapterInterface;
use LmcRbacMvc\Service\AuthorizationService;
use Mockery as m;
use ReflectionClass;

/**
 * Update User Selfserve Test
 */
class UpdateUserSelfserveTest extends AbstractCommandHandlerTestCase
{
    /**
     * @var m\LegacyMockInterface|m\MockInterface|null
     */
    private $mockAuthAdapter;

    public $config = [
        'auth' => [
            'identity_provider' => JWTIdentityProvider::class
        ]
    ];

    public function setUp(): void
    {
        $this->mockRepo('User', User::class);
        $this->mockRepo('ContactDetails', ContactDetails::class);

        $mockConfig = [
            'auth' => [
                'identity_provider' => JWTIdentityProvider::class
            ]
        ];

        $mockPasswordService = m::mock(PasswordService::class)
            ->shouldReceive('generatePassword')
            ->andReturn('GENERATED_PASSWORD')
            ->getMock();

        $this->mockAuthAdapter = m::mock(ValidatableAdapterInterface::class);

        $this->mockedSmServices = [
            CacheEncryption::class => m::mock(CacheEncryption::class),
            AuthorizationService::class => m::mock(AuthorizationService::class),
            'EventHistoryCreator' => m::mock(EventHistoryCreator::class),
            'config' => $mockConfig
        ];

        $this->sut = new Sut(
            $this->mockAuthAdapter,
            $mockPasswordService,
            $this->mockedSmServices['EventHistoryCreator']
        );

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            ContactDetailsEntity::CONTACT_TYPE_USER
        ];

        parent::initReferences();
    }

    public function testHandleCommandLastOperatorAdmin(): void
    {
        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage(Sut::ADMIN_ROLE_ERROR);

        $userId = 111;

        $data = [
            'id' => 111,
            'version' => 1,
            'userType' => UserEntity::USER_TYPE_OPERATOR,
            'loginId' => 'login_id',
            'contactDetails' => [
                'emailAddress' => 'test1@test.me',
                'person' => [
                    'forename' => 'updated forename',
                    'familyName' => 'updated familyName',
                ],
            ],
            'permission' => UserEntity::PERMISSION_USER,
        ];

        $command = Cmd::create($data);

        $user = m::mock(UserEntity::class);
        $user->expects('isLastOperatorAdmin')->withNoArgs()->andReturnTrue();

        $this->repoMap['User']->expects('fetchById')
            ->with($userId, Query::HYDRATE_OBJECT, 1)
            ->andReturn($user);

        $this->sut->handleCommand($command);
    }

    public function testHandleCommandWithNewContactDetails(): void
    {
        $userId = 111;

        $data = [
            'id' => 111,
            'version' => 1,
            'userType' => UserEntity::USER_TYPE_OPERATOR,
            'loginId' => 'login_id',
            'contactDetails' => [
                'emailAddress' => 'test1@test.me',
                'person' => [
                    'forename' => 'updated forename',
                    'familyName' => 'updated familyName',
                ],
            ],
            'permission' => UserEntity::PERMISSION_ADMIN,
        ];

        $command = Cmd::create($data);

        $contactType = m::mock(RefData::class);
        $contactType->shouldReceive('getId')->andReturn('ct_user');

        /** @var ContactDetailsEntity $contactDetails */
        $contactDetails = m::mock(ContactDetailsEntity::class);
        $contactDetails->shouldReceive('getEmailAddress')
            ->withNoArgs()
            ->andReturn('test1@test.me');

        $contactDetails->shouldReceive('update')
            ->once()
            ->andReturnSelf();

        $contactDetails->shouldReceive('getContactType')
            ->once()
            ->andReturn($contactType);

        $organisation = m::mock(Organisation::class);
        $this->expectedOrganisationCacheClear($organisation);

        /** @var UserEntity $user */
        $user = m::mock(UserEntity::class)->makePartial();
        $user->setContactDetails($contactDetails);
        $user->setId($userId);
        $user->setPid('pid');
        $user->setLoginId($data['loginId']);
        $user->shouldReceive('update')->once()->with($data)->andReturnSelf();
        $user->expects('getRelatedOrganisation')->withNoArgs()->andReturn($organisation);

        $reflectionClass = new ReflectionClass(UserEntity::class);
        $property = $reflectionClass->getProperty('userType');
        $property->setValue($user, \Dvsa\Olcs\Api\Entity\User\User::USER_TYPE_OPERATOR);

        $this->repoMap['User']->shouldReceive('fetchById')
            ->once()
            ->with($userId, Query::HYDRATE_OBJECT, 1)
            ->andReturn($user)
            ->shouldReceive('populateRefDataReference')
            ->once()
            ->andReturn($data);

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
                function (UserEntity $user) use (&$savedUser) {
                    $savedUser = $user;
                }
            );

        $this->mockAuthAdapter
            ->shouldReceive('doesUserExist')
            ->with('login_id')
            ->once()
            ->andReturnTrue();
        $this->mockAuthAdapter
            ->shouldReceive('changeAttribute')
            ->with('login_id', 'email', 'test1@test.me')
            ->once();

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'user' => $userId,
            ],
            'messages' => [
                'User updated successfully'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());

        $this->assertInstanceOf(ContactDetailsEntity::class, $savedUser->getContactDetails());
        $this->assertEquals(
            ContactDetailsEntity::CONTACT_TYPE_USER,
            $savedUser->getContactDetails()->getContactType()->getId()
        );
        $this->assertEquals(
            $data['contactDetails']['emailAddress'],
            $savedUser->getContactDetails()->getEmailAddress()
        );
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestHandleCommandWithUpdatedContactDetails')]
    public function testHandleCommandWithUpdatedContactDetails(mixed $userType, mixed $canUpdatePerson, mixed $existingEmail, mixed $eventHistoryTimes): void
    {
        $userId = 111;

        $data = [
            'id' => 111,
            'version' => 1,
            'userType' => $userType,
            'team' => 1,
            'loginId' => 'login_id',
            'contactDetails' => [
                'emailAddress' => 'test1@test.me',
            ],
            'permission' => UserEntity::PERMISSION_ADMIN,
        ];

        $command = Cmd::create($data);

        $contactType = m::mock(RefData::class);
        $contactType->shouldReceive('getId')->andReturn('ct_user');

        /** @var ContactDetailsEntity $contactDetails */
        $contactDetails = m::mock(ContactDetailsEntity::class)->makePartial();
        $contactDetails->shouldReceive('update')
            ->once()
            ->with($data['contactDetails'], $canUpdatePerson)
            ->andReturnSelf();

        $contactDetails->setEmailAddress($existingEmail);
        $contactDetails->setContactType($contactType);

        /** @var UserEntity $user */
        $user = m::mock(UserEntity::class)->makePartial();
        $user->setId($userId);
        $user->setPid('pid');
        $user->setLoginId($data['loginId']);
        $user->setContactDetails($contactDetails);
        $user->expects('getRelatedOrganisation')->withNoArgs()->andReturnNull();

        $user->shouldReceive('getUserType')->once()->withNoArgs()->andReturn($userType);

        $user->shouldReceive('update')->once()->with($data)->andReturnSelf();

        $this->repoMap['User']->shouldReceive('fetchById')
            ->once()
            ->with($userId, Query::HYDRATE_OBJECT, 1)
            ->andReturn($user)
            ->shouldReceive('populateRefDataReference')
            ->once()
            ->andReturn($data);

        $this->repoMap['ContactDetails']->shouldReceive('populateRefDataReference')
            ->once()
            ->with($data['contactDetails'])
            ->andReturn($data['contactDetails']);

        $this->mockedSmServices['EventHistoryCreator']->shouldReceive('create')
            ->times($eventHistoryTimes);

        /** @var UserEntity $savedUser */
        $savedUser = null;

        $this->repoMap['User']->shouldReceive('save')
            ->once()
            ->with(m::type(UserEntity::class))
            ->andReturnUsing(
                function (UserEntity $user) use (&$savedUser) {
                    $savedUser = $user;
                }
            );

        $this->mockAuthAdapter
            ->shouldReceive('doesUserExist')
            ->with('login_id')
            ->once()
            ->andReturnTrue();
        $this->mockAuthAdapter
            ->shouldReceive('changeAttribute')
            ->with('login_id', 'email', $existingEmail)
            ->once();

        $this->expectedUserCacheClear([$userId]);
        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'user' => $userId,
            ],
            'messages' => [
                'User updated successfully'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());

        $this->assertSame(
            $contactDetails,
            $savedUser->getContactDetails()
        );
    }

    public static function dpTestHandleCommandWithUpdatedContactDetails(): array
    {
        return [
            [UserEntity::USER_TYPE_OPERATOR, false, 'test2@test.me', 1],
            [UserEntity::USER_TYPE_TRANSPORT_MANAGER, false, 'test1@test.me', 0]
        ];
    }
}
