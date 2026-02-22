<?php

declare(strict_types=1);

/**
 * BusServiceType list test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Bus;

use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Domain\QueryHandler\Bus\BusServiceTypeList;
use Dvsa\Olcs\Api\Domain\Repository\BusServiceType as BusServiceTypeRepo;
use Dvsa\Olcs\Transfer\Query\Bus\BusServiceTypeList as Qry;
use Doctrine\ORM\Query;
use Mockery as m;

/**
 * BusServiceType list test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class BusServiceTypeTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new BusServiceTypeList();
        $this->mockRepo('BusServiceType', BusServiceTypeRepo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Qry::create([]);

        $mockResult = m::mock(\ArrayIterator::class);
        $mockResult->shouldReceive('serialize')->once()->andReturn('foo');

        $this->repoMap['BusServiceType']->shouldReceive('fetchList')
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
