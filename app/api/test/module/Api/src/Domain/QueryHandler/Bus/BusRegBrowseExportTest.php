<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Bus;

use Dvsa\Olcs\Api\Domain\Repository\BusRegBrowseView as BusRegBrowseViewRepo;
use Dvsa\Olcs\Api\Domain\QueryHandler\Bus\BusRegBrowseExport;
use Dvsa\Olcs\Transfer\Query\Bus\BusRegBrowseExport as Qry;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

/**
 * BusRegBrowseExport test
 */
class BusRegBrowseExportTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new BusRegBrowseExport();
        $this->mockRepo('BusRegBrowseView', BusRegBrowseViewRepo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $acceptedDate = '2016-12-05';
        $trafficAreas = ['B', 'C'];
        $status = 'STATUS';

        $query = Qry::create(
            [
                'acceptedDate' => $acceptedDate,
                'trafficAreas' => $trafficAreas,
                'status' => $status,
            ]
        );

        $this->repoMap['BusRegBrowseView']
            ->shouldReceive('fetchForExport')
            ->with(
                m::type('array'),
                $acceptedDate,
                $trafficAreas,
                $status
            )
            ->andReturn(
                m::mock()
                    ->shouldReceive('next')
                    ->andReturn(
                        [['VAL11', 'VAL12']],
                        [['VAL21', 'VAL22']],
                        false
                    )
                    ->getMock()
            );

        $result = $this->sut->handleQuery($query);

        $this->assertInstanceOf(\Laminas\Http\Response\Stream::class, $result);
        $this->assertNotEmpty($result->getBody());
        $this->assertEquals(
            'Content-Type: text/csv',
            $result->getHeaders()->get('Content-Type')->toString()
        );
        $this->assertEquals(
            'Content-Disposition: attachment; filename="Bus_registration_decisions_2016-12-05.csv"',
            $result->getHeaders()->get('Content-Disposition')->toString()
        );
        $this->assertTrue($result->getHeaders()->has('Content-Length'));
    }

    public function testHandleQueryWithoutResults(): void
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\NotFoundException::class);

        $acceptedDate = '2016-12-05';
        $trafficAreas = ['B', 'C'];
        $status = 'STATUS';

        $query = Qry::create(
            [
                'acceptedDate' => $acceptedDate,
                'trafficAreas' => $trafficAreas,
                'status' => $status,
            ]
        );

        $this->repoMap['BusRegBrowseView']
            ->shouldReceive('fetchForExport')
            ->with(
                m::type('array'),
                $acceptedDate,
                $trafficAreas,
                $status
            )
            ->andReturn(
                m::mock()
                    ->shouldReceive('next')
                    ->andReturn(
                        false
                    )
                    ->getMock()
            );

        $this->sut->handleQuery($query);
    }
}
