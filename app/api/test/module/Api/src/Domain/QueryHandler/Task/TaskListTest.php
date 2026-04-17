<?php

declare(strict_types=1);

/**
 * Task List Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Task;

use Doctrine\ORM\Query;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\QueryHandler\Task\TaskList;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository\TaskSearchView as Repo;
use Dvsa\Olcs\Transfer\Query\Task\TaskList as Qry;
use Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface;

/**
 * Task List Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class TaskListTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new TaskList();
        $this->mockRepo('TaskSearchView', Repo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Qry::create(['id' => 111]);

        $mockTask = m::mock(BundleSerializableInterface::class)
            ->shouldReceive('serialize')
            ->andReturn(['foo' => 'bar'])
            ->once()
            ->getMock();

        $this->repoMap['TaskSearchView']->shouldReceive('fetchList')
            ->with($query, Query::HYDRATE_OBJECT)
            ->andReturn([$mockTask])
            ->once()
            ->shouldReceive('fetchCount')
            ->with($query)
            ->andReturn(1)
            ->once()
            ->shouldReceive('hasRows')
            ->with(m::type(Qry::class))
            ->andReturn(1)
            ->once();

        $this->assertEquals(
            [
                'result' => [['foo' => 'bar']],
                'count' => 1,
                'count-unfiltered' => 1
            ],
            $this->sut->handleQuery($query)
        );
    }
}
