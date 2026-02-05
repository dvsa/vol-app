<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Bus;

use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Domain\QueryHandler\Bus\ByLicenceRoute;
use Dvsa\Olcs\Api\Domain\Repository\Bus as BusRepo;
use Dvsa\Olcs\Api\Domain\Query\Bus\ByLicenceRoute as Qry;
use Doctrine\ORM\Query;
use Mockery as m;

/**
 * ByLicenceRoute Test
 */
class ByLicenceRouteTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new ByLicenceRoute();
        $this->mockRepo('Bus', BusRepo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Qry::create([]);

        $mockResult = m::mock(\ArrayIterator::class);
        $mockResult->shouldReceive('serialize')->once()->andReturn('foo');

        $this->repoMap['Bus']->shouldReceive('fetchList')
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
