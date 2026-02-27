<?php

declare(strict_types=1);

/**
 * BusNoticePeriod list test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Bus;

use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Domain\QueryHandler\Bus\BusNoticePeriodList;
use Dvsa\Olcs\Api\Domain\Repository\BusNoticePeriod as BusNoticePeriodRepo;
use Dvsa\Olcs\Transfer\Query\Bus\BusNoticePeriodList as Qry;
use Doctrine\ORM\Query;
use Mockery as m;

/**
 * BusNoticePeriod list test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class BusNoticePeriodListTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new BusNoticePeriodList();
        $this->mockRepo('BusNoticePeriod', BusNoticePeriodRepo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Qry::create([]);

        $mockResult = m::mock(\ArrayIterator::class);
        $mockResult->shouldReceive('serialize')->once()->andReturn('foo');

        $this->repoMap['BusNoticePeriod']->shouldReceive('fetchList')
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
