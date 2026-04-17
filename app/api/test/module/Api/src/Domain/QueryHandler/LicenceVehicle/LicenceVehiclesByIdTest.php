<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\LicenceVehicle;

use Dvsa\Olcs\Api\Domain\QueryHandler\LicenceVehicle\LicenceVehiclesById;
use Dvsa\Olcs\Api\Entity\Vehicle\Vehicle;
use Mockery as m;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository\LicenceVehicle as LicenceVehicleRepo;
use Dvsa\Olcs\Transfer\Query\LicenceVehicle\LicenceVehiclesById as Qry;
use Dvsa\Olcs\Api\Entity\Licence\LicenceVehicle as LicenceVehicleEntity;

class LicenceVehiclesByIdTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new LicenceVehiclesById();
        $this->mockRepo('LicenceVehicle', LicenceVehicleRepo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $data = [
            'ids' => ['1', '2']
        ];
        $query = Qry::create($data);

        $queryResult = [
            m::mock(LicenceVehicleEntity::class)->makePartial()->setVehicle(
                m::mock(Vehicle::class)->makePartial()->setVrm('ABC123')
            ),
            m::mock(LicenceVehicleEntity::class)->makePartial()->setVehicle(
                m::mock(Vehicle::class)->makePartial()->setVrm('ABC321')
            )
        ];

        $this->repoMap['LicenceVehicle']->shouldReceive('fetchByIds')
            ->with($query->getIds())
            ->once()
            ->andReturn($queryResult);

        $result = $this->sut->handleQuery($query);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('results', $result);
        $this->assertArrayHasKey('count', $result);
        $this->assertEquals(2, $result['count']);

        $this->assertArrayHasKey('vehicle', $result['results'][0]);
        $this->assertArrayHasKey('vrm', $result['results'][0]['vehicle']);
        $this->assertEquals('ABC123', $result['results'][0]['vehicle']['vrm']);

        $this->assertArrayHasKey('vehicle', $result['results'][1]);
        $this->assertArrayHasKey('vrm', $result['results'][1]['vehicle']);
        $this->assertEquals('ABC321', $result['results'][1]['vehicle']['vrm']);
    }
}
