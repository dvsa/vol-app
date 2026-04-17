<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\User;

use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Exception\BadRequestException;
use Dvsa\Olcs\Api\Domain\Exception\ForbiddenException;
use Dvsa\Olcs\Api\Domain\Repository\OrganisationUser as OrganisationUserRepo;
use Dvsa\Olcs\Api\Domain\Repository\Task as TaskRepo;
use Dvsa\Olcs\Api\Domain\Repository\User as UserRepo;
use Dvsa\Olcs\Api\Entity\User\Permission as PermissionEntity;
use Dvsa\Olcs\Api\Entity\User\User as UserEntity;
use Dvsa\Olcs\Auth\Adapter\CognitoAdapter;
use Dvsa\Olcs\Auth\Exception\DeleteUserException;
use Dvsa\Olcs\Transfer\Command\User\DeleteUser as Cmd;
use Dvsa\Olcs\Transfer\Result\Auth\DeleteUserResult;
use Dvsa\Olcs\Transfer\Service\CacheEncryption;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\CommandHandler\User\DeleteUser as Sut;
use LmcRbacMvc\Service\AuthorizationService;
use Mockery as m;

/**
 * Class Delete User Test
 */
class DeleteUserTest extends AbstractCommandHandlerTestCase
{
    /** @var UserEntity|m\Mock */
    private $userEntity;

    private $adapter;

    public function setUp(): void
    {
        $this->adapter = m::mock(CognitoAdapter::class);

        $this->sut = new Sut($this->adapter);
        $this->mockRepo('User', UserRepo::class);
        $this->mockRepo('Task', TaskRepo::class);
        $this->mockRepo('OrganisationUser', OrganisationUserRepo::class);

        $this->mockedSmServices = [
            CacheEncryption::class => m::mock(CacheEncryption::class),
            AuthorizationService::class => m::mock(AuthorizationService::class),
        ];

        parent::setUp();

        $this->userEntity = m::mock(UserEntity::class)->makePartial();
        $this->userEntity->setId('DUMMY-USER-ID');
        $this->userEntity->setLoginId('login_id');

        $this->repoMap['User']
            ->shouldReceive('fetchUsingId')
            ->andReturn($this->userEntity);
    }

    public function testHandleCommandThrowsIncorrectPermissionException(): void
    {
        $this->setPermissionGranted(false);

        $this->expectException(ForbiddenException::class);
        $this->sut->handleCommand(Cmd::create(['id' => 111, 'version' => 1]));
    }

    public function testHandleCommandForUserWithTasks(): void
    {
        $this->setPermissionGranted(true);
        $this->setUserTasks([['id' => 100]]);

        $this->expectException(BadRequestException::class);
        $this->sut->handleCommand(Cmd::create(['id' => 'DUMMY-USER-ID']));
    }

    public function testThatCommandHandlerIsTransactional(): void
    {
        $this->assertInstanceOf(TransactionedInterface::class, $this->sut);
    }

    public function testHandleCommandWhenCognitoError(): void
    {
        $this->setPermissionGranted(true);
        $this->setUserTasks([]);
        $this->expectCognitoFailure();

        $this->expectDeleteUser();
        $this->expectDeleteOrganisationUser();

        $this->expectException(DeleteUserException::class);
        $this->sut->handleCommand(Cmd::create(['id' => 'DUMMY-USER-ID']));
    }

    public function testHandleCommandWhenCognitoUserDoesNotExist(): void
    {
        $this->setPermissionGranted(true);
        $this->setUserTasks([]);
        $this->expectCognitoNotFound();

        $this->expectDeleteUser();
        $this->expectDeleteOrganisationUser();
        $this->expectedUserCacheClear(['DUMMY-USER-ID']);

        $this->assertEquals(
            ['id' => ['user' => 'DUMMY-USER-ID'], 'messages' => ['User deleted successfully']],
            $this->sut->handleCommand(Cmd::create(['id' => 'DUMMY-USER-ID']))->toArray()
        );
    }

    public function testHandleCommand(): void
    {
        $this->setPermissionGranted(true);
        $this->setUserTasks([]);

        $this->expectDeleteUser();
        $this->expectDeleteOrganisationUser();
        $this->expectCognitoDelete();
        $this->expectedUserCacheClear(['DUMMY-USER-ID']);

        $this->assertEquals(
            ['id' => ['user' => 'DUMMY-USER-ID'], 'messages' => ['User deleted successfully']],
            $this->sut->handleCommand(Cmd::create(['id' => 'DUMMY-USER-ID']))->toArray()
        );
    }

    private function setPermissionGranted(mixed $granted): void
    {
        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('isGranted')
            ->with(PermissionEntity::CAN_MANAGE_USER_INTERNAL, null)
            ->andReturn($granted);
    }

    private function setUserTasks(mixed $tasks): void
    {
        $this->repoMap['Task']
            ->shouldReceive('fetchByUser')
            ->with('DUMMY-USER-ID', true)
            ->andReturn($tasks);
    }

    private function expectCognitoFailure(): void
    {
        $cognitoResult = m::mock(DeleteUserResult::class);
        $cognitoResult->expects('isValid')->withNoArgs()->andReturnFalse();
        $cognitoResult->expects('isUserNotPresent')->withNoArgs()->andReturnFalse();
        $this->adapter->expects('deleteUser')->with('login_id')->andReturn($cognitoResult);
    }

    private function expectCognitoDelete(): void
    {
        $cognitoResult = m::mock(DeleteUserResult::class);
        $cognitoResult->expects('isValid')->withNoArgs()->andReturnTrue();
        $this->adapter->expects('deleteUser')->with('login_id')->andReturn($cognitoResult);
    }

    private function expectCognitoNotFound(): void
    {
        $cognitoResult = m::mock(DeleteUserResult::class);
        $cognitoResult->expects('isValid')->withNoArgs()->andReturnFalse();
        $cognitoResult->expects('isUserNotPresent')->withNoArgs()->andReturnTrue();
        $this->adapter->expects('deleteUser')->with('login_id')->andReturn($cognitoResult);
    }

    private function expectDeleteUser(): void
    {
        $this->repoMap['User']
            ->shouldReceive('delete')
            ->with($this->userEntity)
            ->once();
    }

    private function expectDeleteOrganisationUser(): void
    {
        $this->repoMap['OrganisationUser']
            ->shouldReceive('deleteByUserId')
            ->with('DUMMY-USER-ID')
            ->once();
    }
}
