<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Bus;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\QueryHandler\Bus\BusRegBrowseList;
use Dvsa\Olcs\Api\Domain\Repository\BusRegBrowseView as BusRegBrowseViewRepo;
use Dvsa\Olcs\Transfer\Query\Bus\BusRegBrowseList as Qry;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

/**
 * BusRegBrowseListTest
 */
class BusRegBrowseListTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new BusRegBrowseList();
        $this->mockRepo('BusRegBrowseView', BusRegBrowseViewRepo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Qry::create([]);

        $mockResult = m::mock(\ArrayIterator::class);
        $mockResult->shouldReceive('serialize')->once()->andReturn('foo');

        $this->repoMap['BusRegBrowseView']->shouldReceive('fetchList')
            ->with($query, Query::HYDRATE_OBJECT)
            ->andReturn([$mockResult])
            ->shouldReceive('fetchCount')
            ->with($query)
            ->andReturn(1);

        $this->assertEquals(
            [
                'results' => ['foo'],
                'count' => 1
            ],
            $this->sut->handleQuery($query, Query::HYDRATE_OBJECT)
        );
    }
}
