<?php

declare(strict_types=1);

/**
 * UserSelfserveTest
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\User;

use Dvsa\Olcs\Api\Domain\QueryHandler\User\UserSelfserve as QueryHandler;
use Dvsa\Olcs\Api\Domain\Repository\User as Repo;
use Dvsa\Olcs\Api\Entity\User\User as UserEntity;
use Dvsa\Olcs\Transfer\Query\User\UserSelfserve as Query;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

/**
 * UserSelfserveTest
 */
class UserSelfserveTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new QueryHandler();
        $this->mockRepo('User', Repo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $mockUser = m::mock(UserEntity::class);
        $mockUser->shouldReceive('serialize')->once()->andReturn(['foo' => 'bar']);
        $mockUser->shouldReceive('getPermission')->once()->andReturn(UserEntity::PERMISSION_USER);

        $query = Query::create(['QUERY']);

        $this->repoMap['User']->shouldReceive('fetchUsingId')->with($query)->andReturn($mockUser);

        $result = $this->sut->handleQuery($query)->serialize();

        $this->assertSame(['foo' => 'bar', 'permission' => UserEntity::PERMISSION_USER], $result);
    }
}
