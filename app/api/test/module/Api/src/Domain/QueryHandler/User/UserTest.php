<?php

/**
 * UserTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\User;

use Dvsa\Olcs\Api\Domain\QueryHandler\User\User as QueryHandler;
use Dvsa\Olcs\Api\Domain\Repository\User as Repo;
use Dvsa\Olcs\Api\Entity\EventHistory\EventHistory as EventHistoryEntity;
use Dvsa\Olcs\Api\Entity\EventHistory\EventHistoryType as EventHistoryTypeEntity;
use Dvsa\Olcs\Api\Entity\User\Permission as PermissionEntity;
use Dvsa\Olcs\Api\Rbac\IdentityProviderInterface;
use Dvsa\Olcs\Api\Rbac\JWTIdentityProvider;
use Dvsa\Olcs\Transfer\Query\User\User as Query;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * UserTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class UserTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new QueryHandler();
        $this->mockRepo('User', Repo::class);
        $this->mockRepo('EventHistory', Repo::class);
        $this->mockRepo('EventHistoryType', Repo::class);

        $mockedConfig = [
            'auth' => [
                'identity_provider' => JWTIdentityProvider::class
            ]
        ];

        $this->mockedSmServices = [
            AuthorizationService::class => m::mock(AuthorizationService::class),
            'config' => $mockedConfig
        ];

        parent::setUp();
    }

    public function testHandleQuery()
    {
        $query = Query::create(['QUERY']);

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('isGranted')
            ->once()
            ->with(PermissionEntity::CAN_MANAGE_USER_INTERNAL, null)
            ->andReturn(true);

        $userId = 100;
        $mockUser = m::mock(\Dvsa\Olcs\Api\Entity\User\User::class);
        $mockUser->shouldReceive('getId')->andReturn($userId);
        $mockUser->shouldReceive('serialize')->once()->andReturn(['foo' => 'bar']);
        $mockUser->shouldReceive('getUserType')->once()->andReturn('internal');
        $mockUser->shouldReceive('getLastLoginAt')->once()->andReturn('2016-12-06T16:12:46+0000');

        $this->repoMap['User']->shouldReceive('fetchUsingId')->with($query)->andReturn($mockUser);

        $eventHistoryType = m::mock(EventHistoryTypeEntity::class);

        $this->repoMap['EventHistoryType']
            ->shouldReceive('fetchOneByEventCode')
            ->with(EventHistoryTypeEntity::EVENT_CODE_PASSWORD_RESET)
            ->andReturn($eventHistoryType);

        $eventHistory = m::mock(EventHistoryEntity::class);
        $eventHistory->shouldReceive('serialize')->andReturn('PASSWORD RESET EVENT');

        $this->repoMap['EventHistory']
            ->shouldReceive('fetchByAccount')
            ->with($userId, $eventHistoryType, 'id', 'desc', 1)
            ->andReturn([$eventHistory]);

        $result = $this->sut->handleQuery($query)->serialize();

        $this->assertSame(
            [
                'foo' => 'bar',
                'userType' => 'internal',
                'lastLoggedInOn' => '2016-12-06T16:12:46+0000',
                'lockedOn' => null,
                'latestPasswordResetEvent' => 'PASSWORD RESET EVENT'
            ],
            $result
        );
    }

    public function testHandleQueryWithNoLastLoginTime()
    {
        $query = Query::create(['QUERY']);

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('isGranted')
            ->once()
            ->with(PermissionEntity::CAN_MANAGE_USER_INTERNAL, null)
            ->andReturn(true);

        $userId = 100;
        $mockUser = m::mock(\Dvsa\Olcs\Api\Entity\User\User::class);
        $mockUser->shouldReceive('getId')->andReturn($userId);
        $mockUser->shouldReceive('getPid')->andReturn('pid');
        $mockUser->shouldReceive('serialize')->once()->andReturn(['foo' => 'bar']);
        $mockUser->shouldReceive('getUserType')->once()->andReturn('internal');
        $mockUser->shouldReceive('getLastLoginAt')->once()->andReturnNull();

        $this->repoMap['User']->shouldReceive('fetchUsingId')->with($query)->andReturn($mockUser);

        $eventHistoryType = m::mock(EventHistoryTypeEntity::class);

        $this->repoMap['EventHistoryType']
            ->shouldReceive('fetchOneByEventCode')
            ->with(EventHistoryTypeEntity::EVENT_CODE_PASSWORD_RESET)
            ->andReturn($eventHistoryType);

        $eventHistory = m::mock(EventHistoryEntity::class);
        $eventHistory->shouldReceive('serialize')->andReturn('PASSWORD RESET EVENT');

        $this->repoMap['EventHistory']
            ->shouldReceive('fetchByAccount')
            ->with($userId, $eventHistoryType, 'id', 'desc', 1)
            ->andReturn([$eventHistory]);

        $result = $this->sut->handleQuery($query)->serialize();

        $this->assertSame(
            [
                'foo' => 'bar',
                'userType' => 'internal',
                'lastLoggedInOn' => null,
                'lockedOn' => null,
                'latestPasswordResetEvent' => 'PASSWORD RESET EVENT'
            ],
            $result
        );
    }

    public function testHandleQueryWithoutPasswordResetEvent()
    {
        $query = Query::create(['QUERY']);

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('isGranted')
            ->once()
            ->with(PermissionEntity::CAN_MANAGE_USER_INTERNAL, null)
            ->andReturn(true);

        $userId = 100;
        $mockUser = m::mock(\Dvsa\Olcs\Api\Entity\User\User::class);
        $mockUser->shouldReceive('getId')->andReturn($userId);
        $mockUser->shouldReceive('getPid')->andReturn('pid');
        $mockUser->shouldReceive('serialize')->once()->andReturn(['foo' => 'bar']);
        $mockUser->shouldReceive('getUserType')->once()->andReturn('internal');
        $mockUser->shouldReceive('getLastLoginAt')->once()->andReturnNull();

        $this->repoMap['User']->shouldReceive('fetchUsingId')->with($query)->andReturn($mockUser);

        $eventHistoryType = m::mock(EventHistoryTypeEntity::class);

        $this->repoMap['EventHistoryType']
            ->shouldReceive('fetchOneByEventCode')
            ->with(EventHistoryTypeEntity::EVENT_CODE_PASSWORD_RESET)
            ->andReturn($eventHistoryType);

        $this->repoMap['EventHistory']
            ->shouldReceive('fetchByAccount')
            ->with($userId, $eventHistoryType, 'id', 'desc', 1)
            ->andReturn([]);

        $result = $this->sut->handleQuery($query)->serialize();

        $this->assertSame(
            [
                'foo' => 'bar',
                'userType' => 'internal',
                'lastLoggedInOn' => null,
                'lockedOn' => null,
                'latestPasswordResetEvent' => null,
            ],
            $result
        );
    }

    public function testHandleQueryThrowsIncorrectPermissionException()
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\ForbiddenException::class);

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('isGranted')
            ->once()
            ->with(PermissionEntity::CAN_MANAGE_USER_INTERNAL, null)
            ->andReturn(false);

        $query = Query::create(['QUERY']);

        $this->repoMap['User']->shouldReceive('fetchUsingId')->never();

        $this->sut->handleQuery($query)->serialize();
    }
}
