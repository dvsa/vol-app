<?php

declare(strict_types=1);

/**
 * GoodsVehicles Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Licence;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Api\Domain\QueryHandler\Result;
use Dvsa\Olcs\Api\Entity\Licence\LicenceVehicle;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Entity\User\Permission;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\QueryHandler\Licence\GoodsVehicles;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository\Licence as LicenceRepo;
use Dvsa\Olcs\Api\Domain\Repository\LicenceVehicle as LicenceVehicleRepo;
use Dvsa\Olcs\Transfer\Query\Licence\GoodsVehicles as Qry;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * GoodsVehicles Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class GoodsVehiclesTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new GoodsVehicles();
        $this->mockRepo('Licence', LicenceRepo::class);
        $this->mockRepo('LicenceVehicle', LicenceVehicleRepo::class);

        $this->mockedSmServices[AuthorizationService::class] = m::mock(AuthorizationService::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $data = [];

        $query = Qry::create($data);

        $qb = m::mock(QueryBuilder::class);

        $licenceVehicle = m::mock(LicenceVehicle::class)->makePartial();
        $licenceVehicle->shouldReceive('serialize')
            ->with(['vehicle', 'goodsDiscs', 'interimApplication'])
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

        /** @var LicenceEntity $licence */
        $licence = m::mock(LicenceEntity::class)->makePartial();
        $licence->setId(111);
        $licence->shouldReceive('getActiveVehiclesCount')
            ->andReturn(2)
            ->shouldReceive('getRemainingSpaces')
            ->andReturn(3);

        $licence->shouldReceive('getOtherActiveLicences->count')
            ->andReturn(5);

        $this->repoMap['Licence']->shouldReceive('fetchUsingId')
            ->with($query)
            ->andReturn($licence);

        $this->repoMap['LicenceVehicle']->shouldReceive('createPaginatedVehiclesDataForLicenceQuery')
            ->with($query, 111)
            ->andReturn($qb)
            ->shouldReceive('fetchPaginatedList')
            ->with($qb, Query::HYDRATE_OBJECT)
            ->andReturn($licenceVehicles)
            ->shouldReceive('fetchPaginatedCount')
            ->with($qb)
            ->andReturn(10)
            ->shouldReceive('fetchAllVehiclesCount')
            ->with(111)
            ->andReturn(3)
            ->once();

        $result = $this->sut->handleQuery($query);

        $this->assertInstanceOf(Result::class, $result);

        $licence->shouldReceive('serialize')
            ->with(['organisation'])
            ->andReturn(['foo' => 'bar']);

        $expected = [
            'foo' => 'bar',
            'canReprint' => true,
            'canTransfer' => true,
            'canExport' => true,
            'canPrintVehicle' => true,
            'licenceVehicles' => [
                'results' => [
                    ['foo' => 'bar']
                ],
                'count' => 10
            ],
            'spacesRemaining' => 3,
            'activeVehicleCount' => 2,
            'allVehicleCount' => 3
        ];

        $this->assertEquals($expected, $result->serialize());
    }
}
