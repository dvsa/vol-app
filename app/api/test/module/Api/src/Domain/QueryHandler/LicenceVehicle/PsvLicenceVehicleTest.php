<?php

declare(strict_types=1);

/**
 * Psv Licence Vehicle Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\LicenceVehicle;

use Dvsa\Olcs\Api\Domain\QueryHandler\LicenceVehicle\PsvLicenceVehicle;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity;
use Dvsa\Olcs\Transfer\Query\LicenceVehicle\PsvLicenceVehicle as Qry;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;
use LmcRbacMvc\Service\AuthorizationService;
use Dvsa\Olcs\Api\Entity\User\Permission;

/**
 * Psv Licence Vehicle Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class PsvLicenceVehicleTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new PsvLicenceVehicle();
        $this->mockRepo('LicenceVehicle', Repository\LicenceVehicle::class);
        $this->mockedSmServices[AuthorizationService::class] = m::mock(AuthorizationService::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('isGranted')
            ->with(Permission::INTERNAL_USER, null)
            ->andReturn(true);

        $query = Qry::create(
            [
                'id' => 111
            ]
        );

        /** @var Entity\Licence\LicenceVehicle $licenceVehicle */
        $licenceVehicle = m::mock(Entity\Licence\LicenceVehicle::class)->makePartial();
        $licenceVehicle->shouldReceive('serialize')
            ->once()
            ->andReturn(['foo' => 'bar'])
            ->shouldReceive('getVehicle')
            ->andReturn(
                m::mock()
                ->shouldReceive('getId')
                ->andReturn(1)
                ->once()
                ->getMock()
            )
            ->getMock();

        $this->repoMap['LicenceVehicle']->shouldReceive('fetchUsingId')
            ->with($query)
            ->andReturn($licenceVehicle)
            ->shouldReceive('fetchByVehicleId')
            ->with(1)
            ->andReturn('history')
            ->once()
            ->getMock();

        $result = $this->sut->handleQuery($query);

        $data = $result->serialize();

        $this->assertEquals(['foo' => 'bar', 'showHistory' => 1, 'history' => 'history'], $data);
    }
}
