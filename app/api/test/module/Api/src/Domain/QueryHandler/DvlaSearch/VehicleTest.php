<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\DvlaSearch;

use Dvsa\Olcs\Api\Domain\QueryHandler\DvlaSearch\Vehicle;
use Dvsa\Olcs\Api\Service\DvlaSearch\DvlaSearchService;
use Dvsa\Olcs\Api\Service\DvlaSearch\Model\DvlaVehicle;
use Dvsa\Olcs\Api\Service\DvlaSearch\Exception\VehicleUnavailableException;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

class VehicleTest extends QueryHandlerTestCase
{
    protected $sut;

    public function setUp(): void
    {
        /**
         * @var Vehicle
         */
        $this->sut = new Vehicle();

        $this->mockedSmServices[DvlaSearchService::class] = m::mock(DvlaSearchService::class);
        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $queryData = ['vrm' => "ABC123"];

        $vehicle = m::mock(DvlaVehicle::class);
        $vehicle->shouldReceive('toArray')
            ->withNoArgs()
            ->once()
            ->andReturn([
                'registrationNumber' => $queryData['vrm']
            ]);
        $this->mockedSmServices[DvlaSearchService::class]->shouldReceive('getVehicle')
            ->with($queryData['vrm'])
            ->once()
            ->andReturn($vehicle);

        $query = (new \Dvsa\Olcs\Transfer\Query\DvlaSearch\Vehicle())->create($queryData);
        $actual = $this->sut->handleQuery($query);

        $expected = [
            'count' => 1,
            'result' => [
                0 => [
                    'registrationNumber' => $queryData['vrm']
                ]
            ]
        ];
        $this->assertEquals($expected, $actual);
    }

    public function testNoVehicleReturnsEmptyResultSetHandleQuery(): void
    {
        $queryData = ['vrm' => "ABC123"];
        $this->mockedSmServices[DvlaSearchService::class]->shouldReceive('getVehicle')
            ->with($queryData['vrm'])
            ->once()
            ->andThrow(new VehicleUnavailableException());

        $query = (new \Dvsa\Olcs\Transfer\Query\DvlaSearch\Vehicle())->create($queryData);
        $actual = $this->sut->handleQuery($query);

        $expected = [
            'count' => 0,
            'result' => []
        ];
        $this->assertEquals($expected, $actual);
    }
}
