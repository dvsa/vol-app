<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Licence;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Api\Domain\QueryHandler\Licence\Vehicles;
use Dvsa\Olcs\Api\Entity\Licence\LicenceVehicle;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Entity\User\Permission;
use Mockery as m;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository\LicenceVehicle as LicenceVehicleRepo;
use Dvsa\Olcs\Transfer\Query\Licence\GoodsVehicles as Qry;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Vehicles Test
 */
class VehiclesTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new Vehicles();
        $this->mockRepo('LicenceVehicle', LicenceVehicleRepo::class);

        $this->mockedSmServices[AuthorizationService::class] = m::mock(AuthorizationService::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $data = ['id' => 111];

        $query = Qry::create($data);

        $qb = m::mock(QueryBuilder::class);

        $licenceVehicle = m::mock(LicenceVehicle::class)->makePartial();
        $licenceVehicle->shouldReceive('serialize')
            ->with(['vehicle', 'goodsDiscs'])
            ->andReturn(['foo' => 'bar']);

        $licenceVehicles = [
            $licenceVehicle
        ];

        // Internal user
        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('isGranted')
            ->with(Permission::INTERNAL_USER, null)
            ->andReturn(true)
            ->shouldReceive('isGranted')
            ->with(Permission::SELFSERVE_USER, null)
            ->andReturn(false);

        $goodsOrPsv = m::mock(RefData::class)->makePartial();
        $goodsOrPsv->setId(LicenceEntity::LICENCE_CATEGORY_PSV);

        $this->repoMap['LicenceVehicle']->shouldReceive('createPaginatedVehiclesDataForLicenceQuery')
            ->with($query, 111)
            ->andReturn($qb)
            ->once()
            ->shouldReceive('fetchPaginatedList')
            ->with($qb, Query::HYDRATE_OBJECT)
            ->andReturn($licenceVehicles)
            ->once()
            ->shouldReceive('fetchPaginatedCount')
            ->with($qb)
            ->andReturn(10)
            ->once();

        $result = $this->sut->handleQuery($query);

        $expected = [
            'results' => [
                ['foo' => 'bar']
            ],
            'count' => 10
        ];

        $this->assertEquals($expected, $result);
    }
}
