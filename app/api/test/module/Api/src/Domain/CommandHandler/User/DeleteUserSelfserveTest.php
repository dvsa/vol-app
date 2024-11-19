<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\User;

use Dvsa\Olcs\Api\Domain\CommandHandler\User\DeleteUserSelfserve;
use Dvsa\Olcs\Api\Domain\Exception\BadRequestException;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Api\Entity\User\User as UserEntity;
use Dvsa\Olcs\Auth\Adapter\CognitoAdapter;
use Dvsa\Olcs\Auth\Exception\DeleteUserException;
use Dvsa\Olcs\Transfer\Command\User\DeleteUserSelfserve as Cmd;
use Dvsa\Olcs\Transfer\Result\Auth\DeleteUserResult;
use Dvsa\Olcs\Transfer\Service\CacheEncryption;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use LmcRbacMvc\Service\AuthorizationService;
use Mockery as m;

class DeleteUserSelfserveTest extends AbstractCommandHandlerTestCase
{
    public const USER_ID = 8888;
    public const LOGIN_ID = 'usr8888';
    public const ADMIN_USER_ID = 8880;

    /** @var  DeleteUserSelfserve */
    protected $sut;
    /** @var  m\MockInterface */
    private $mockAuth;

    private $adapter;

    public function setUp(): void
    {
        $this->adapter = m::mock(CognitoAdapter::class);
        $this->sut = new DeleteUserSelfserve($this->adapter);
        $this->mockRepo('User', Repository\User::class);
        $this->mockRepo('OrganisationUser', Repository\OrganisationUser::class);

        $this->mockAuth = m::mock(AuthorizationService::class);
        $this->sut->setAuthService($this->mockAuth);

        $this->mockedSmServices = [
            CacheEncryption::class => m::mock(CacheEncryption::class),
            AuthorizationService::class => $this->mockAuth,
        ];

        $adminUserEntity = (new UserEntity(9999, UserEntity::USER_TYPE_OPERATOR))
            ->setId(self::ADMIN_USER_ID);
        $this->mockAuth->shouldReceive('getIdentity->getUser')->once()->andReturn($adminUserEntity);

        parent::setUp();
    }

    public function testHandleCommandNoOrganisation(): void
    {
        $data = [
            'id' => self::USER_ID,
        ];

        $command = Cmd::create($data);

        $userEntity = m::mock(UserEntity::class);
        $userEntity->expects('getId')->withNoArgs()->andReturn(self::USER_ID);
        $userEntity->expects('getLoginId')->withNoArgs()->andReturn(self::LOGIN_ID);
        $userEntity->expects('isLastOperatorAdmin')->withNoArgs()->andReturnFalse();
        $userEntity->expects('getRelatedOrganisation')->withNoArgs()->andReturnNull();

        $cognitoResult = m::mock(DeleteUserResult::class);
        $cognitoResult->expects('isValid')->withNoArgs()->andReturnTrue();
        $this->adapter->expects('deleteUser')->with(self::LOGIN_ID)->andReturn($cognitoResult);

        $this->repoMap['User']
            ->shouldReceive('fetchUsingId')->once()->andReturn($userEntity)
            ->shouldReceive('delete')->once();

        $this->repoMap['OrganisationUser']
            ->shouldReceive('deleteByUserId')->with(self::USER_ID)->once();

        $this->expectedUserCacheClear([self::USER_ID]);
        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'user' => self::USER_ID,
            ],
            'messages' => [
                'User deleted successfully'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandWithOrganisation(): void
    {
        $data = [
            'id' => self::USER_ID,
        ];

        $command = Cmd::create($data);

        $organisation = m::mock(Organisation::class);

        $userEntity = m::mock(UserEntity::class);
        $userEntity->expects('getId')->withNoArgs()->andReturn(self::USER_ID);
        $userEntity->expects('getLoginId')->withNoArgs()->andReturn(self::LOGIN_ID);
        $userEntity->expects('isLastOperatorAdmin')->withNoArgs()->andReturnFalse();
        $userEntity->expects('getRelatedOrganisation')->withNoArgs()->andReturn($organisation);

        $cognitoResult = m::mock(DeleteUserResult::class);
        $cognitoResult->expects('isValid')->withNoArgs()->andReturnTrue();
        $this->adapter->expects('deleteUser')->with(self::LOGIN_ID)->andReturn($cognitoResult);

        $this->repoMap['User']
            ->shouldReceive('fetchUsingId')->once()->andReturn($userEntity)
            ->shouldReceive('delete')->once();

        $this->repoMap['OrganisationUser']
            ->shouldReceive('deleteByUserId')->with(self::USER_ID)->once();

        $this->expectedOrganisationCacheClear($organisation);
        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'user' => self::USER_ID,
            ],
            'messages' => [
                'User deleted successfully'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandLastOperatorAdmin(): void
    {
        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage(DeleteUserSelfserve::ADMIN_ROLE_ERROR);

        $data = [
            'id' => self::USER_ID,
        ];

        $command = Cmd::create($data);

        $userEntity = m::mock(UserEntity::class);
        $userEntity->expects('isLastOperatorAdmin')->withNoArgs()->andReturnTrue();

        $this->repoMap['User']->expects('fetchUsingId')->with($command)->andReturn($userEntity);

        $this->sut->handleCommand($command);
    }

    public function testHandleCommandCognitoError(): void
    {
        $this->expectException(DeleteUserException::class);

        $data = [
            'id' => self::USER_ID,
        ];

        $command = Cmd::create($data);

        $userEntity = m::mock(UserEntity::class);
        $userEntity->expects('getId')->withNoArgs()->andReturn(self::USER_ID);
        $userEntity->expects('getLoginId')->withNoArgs()->andReturn(self::LOGIN_ID);
        $userEntity->expects('isLastOperatorAdmin')->withNoArgs()->andReturnFalse();

        $cognitoResult = m::mock(DeleteUserResult::class);
        $cognitoResult->expects('isValid')->withNoArgs()->andReturnFalse();
        $cognitoResult->expects('isUserNotPresent')->withNoArgs()->andReturnFalse();
        $this->adapter->expects('deleteUser')->with(self::LOGIN_ID)->andReturn($cognitoResult);

        $this->repoMap['User']->expects('fetchUsingId')->with($command)->andReturn($userEntity);
        $this->repoMap['User']->expects('delete')->with($userEntity);

        $this->repoMap['OrganisationUser']->expects('deleteByUserId')->with(self::USER_ID);

        $this->sut->handleCommand($command);
    }

    public function testHandleCommandDeleteHimself(): void
    {
        $this->expectException(BadRequestException::class);

        $command = Cmd::create(
            [
                'id' => self::ADMIN_USER_ID,
            ]
        );

        $this->sut->handleCommand($command);
    }
}
