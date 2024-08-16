<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Fee;

use Dvsa\Olcs\Api\Domain\QueryHandler\Fee\InterimRefunds;
use Dvsa\Olcs\Api\Domain\Repository\Fee;
use Dvsa\Olcs\Transfer\Query\Fee\InterimRefunds as InterimRefundsQuery;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

class InterimRefundsTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new InterimRefunds();
        $this->mockRepo('Fee', Fee::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $startDate = '2019-06-01';
        $endDate = '2019-06-31';
        $sort = 'id';
        $order = 'DESC';
        $trafficAreas = ['A', 'B'];

        $query = InterimRefundsQuery::create([
            'startDate' => $startDate,
            'endDate' => $endDate,
            'sort' => $sort,
            'order' => $order,
            'trafficAreas' => $trafficAreas
        ]);

        $expected = [
            m::mock(\Dvsa\Olcs\Api\Entity\Fee\Fee::class)
                ->shouldReceive('serialize')->once()->andReturn('foo')->getMock()
        ];

        $this->repoMap['Fee']
            ->expects('fetchInterimRefunds')
            ->with(
                $startDate,
                $endDate,
                $sort,
                $order,
                $trafficAreas
            )
            ->andReturn($expected);

        $this->assertEquals([
            'count' => 1,
            'results' => ['foo']
        ], $this->sut->handleQuery($query));
    }

    public function testHandleQueryEmptyTrafficArea(): void
    {
        $startDate = '2019-06-01';
        $endDate = '2019-06-31';
        $sort = 'id';
        $order = 'DESC';
        $trafficAreas = ['A', 'B'];
        $trafficAreasWithOther = ['A', 'B', 'other'];

        $query = InterimRefundsQuery::create([
            'startDate' => $startDate,
            'endDate' => $endDate,
            'sort' => $sort,
            'order' => $order,
            'trafficAreas' => []
        ]);

        $userData = [
            'dataAccess' => [
                'trafficAreas' => $trafficAreas,
            ],
        ];

        $this->expectedUserDataCacheCall($userData);

        $expected = [
            m::mock(\Dvsa\Olcs\Api\Entity\Fee\Fee::class)
                ->shouldReceive('serialize')->once()->andReturn('foo')->getMock()
        ];

        $this->repoMap['Fee']
            ->expects('fetchInterimRefunds')
            ->with(
                $startDate,
                $endDate,
                $sort,
                $order,
                $trafficAreasWithOther
            )
            ->andReturn($expected);

        $this->assertEquals([
            'count' => 1,
            'results' => ['foo']
        ], $this->sut->handleQuery($query));
    }
}
