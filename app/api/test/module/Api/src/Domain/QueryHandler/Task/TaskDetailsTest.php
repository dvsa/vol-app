<?php

declare(strict_types=1);

/**
 * Task Details Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Task;

use Doctrine\ORM\Query;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\QueryHandler\Task\TaskDetails;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository\TaskSearchView as Repo;
use Dvsa\Olcs\Transfer\Query\Task\TaskDetails as Qry;
use Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface;

/**
 * Task Details Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class TaskDetailsTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new TaskDetails();
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

        $this->repoMap['TaskSearchView']->shouldReceive('fetchUsingId')
            ->with($query)
            ->andReturn($mockTask);

        $this->assertEquals(['foo' => 'bar'], $this->sut->handleQuery($query)->serialize());
    }
}
