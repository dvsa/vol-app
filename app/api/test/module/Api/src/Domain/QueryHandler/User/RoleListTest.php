<?php

/**
 * Role List Test
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\User;

use Mockery as m;
use Doctrine\ORM\Query as DoctrineQuery;
use Dvsa\Olcs\Api\Domain\QueryHandler\User\RoleList;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository\Role as RoleRepo;
use Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface;
use Dvsa\Olcs\Transfer\Query\User\RoleList as Qry;

/**
 * Role List Test
 */
class RoleListTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new RoleList();
        $this->mockRepo('Role', RoleRepo::class);

        parent::setUp();
    }

    public function testHandleQuery()
    {
        $query = Qry::create([]);

        $this->repoMap['Role']->shouldReceive('fetchList')
            ->with($query, DoctrineQuery::HYDRATE_OBJECT)
            ->andReturn(
                [
                    m::mock(BundleSerializableInterface::class)
                        ->shouldReceive('serialize')
                        ->andReturn(['foo'])
                        ->getMock(),
                    m::mock(BundleSerializableInterface::class)
                        ->shouldReceive('serialize')
                        ->andReturn(['bar'])
                        ->getMock()
                ]
            );

        $this->repoMap['Role']->shouldReceive('fetchCount')
            ->with($query)
            ->andReturn(2);

        $result = $this->sut->handleQuery($query);
        $this->assertEquals(2, $result['count']);
        $this->assertEquals([['foo'], ['bar']], $result['result']);
    }
}
