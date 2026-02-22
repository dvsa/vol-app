<?php

declare(strict_types=1);

/**
 * LicenceVehicle Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\LicenceVehicle;

use Dvsa\Olcs\Api\Domain\QueryHandler\Result;
use Dvsa\Olcs\Api\Entity\User\Permission;
use Dvsa\Olcs\Api\Entity\Vehicle\Vehicle;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\QueryHandler\LicenceVehicle\LicenceVehicle;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository\LicenceVehicle as LicenceVehicleRepo;
use Dvsa\Olcs\Api\Domain\Repository\VehicleHistoryView as VehicleHistoryViewRepo;
use Dvsa\Olcs\Transfer\Query\LicenceVehicle\LicenceVehicle as Qry;
use LmcRbacMvc\Service\AuthorizationService;
use Dvsa\Olcs\Api\Entity\Licence\LicenceVehicle as LicenceVehicleEntity;

/**
 * LicenceVehicle Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class LicenceVehicleTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new LicenceVehicle();
        $this->mockRepo('LicenceVehicle', LicenceVehicleRepo::class);
        $this->mockRepo('VehicleHistoryView', VehicleHistoryViewRepo::class);

        $this->mockedSmServices[AuthorizationService::class] = m::mock(AuthorizationService::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $data = [];
        $query = Qry::create($data);

        $history = [
            'foo' => 'bar'
        ];

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('isGranted')
            ->with(Permission::INTERNAL_USER, null)
            ->andReturn(true);

        $vehicle = m::mock(Vehicle::class)->makePartial();
        $vehicle->setVrm('ABC123');

        /** @var LicenceVehicleEntity $licenceVehicle */
        $licenceVehicle = m::mock(LicenceVehicleEntity::class)->makePartial();
        $licenceVehicle->setVehicle($vehicle);

        $this->repoMap['LicenceVehicle']->shouldReceive('fetchUsingId')
            ->with($query)
            ->andReturn($licenceVehicle);

        $this->repoMap['VehicleHistoryView']->shouldReceive('fetchByVrm')
            ->with('ABC123')
            ->andReturn($history);

        $result = $this->sut->handleQuery($query);

        $this->assertInstanceOf(Result::class, $result);

        $licenceVehicle->shouldReceive('serialize')
            ->once()
            ->andReturn(['foo' => 'cake']);

        $expected = [
            'foo' => 'cake',
            'showHistory' => true,
            'history' => [
                'foo' => 'bar'
            ]
        ];

        $this->assertEquals($expected, $result->serialize());
    }
}
