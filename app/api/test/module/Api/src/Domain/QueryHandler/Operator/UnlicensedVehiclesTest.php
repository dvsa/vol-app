<?php

declare(strict_types=1);

/**
 * Unlicensed Operator Vehicles Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Operator;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Api\Domain\QueryHandler\Operator\UnlicensedVehicles as VehiclesQueryHandler;
use Dvsa\Olcs\Api\Domain\Repository\LicenceVehicle as LicenceVehicleRepo;
use Dvsa\Olcs\Api\Domain\Repository\Organisation as OrganisationRepo;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation as OrganisationEntity;
use Dvsa\Olcs\Api\Entity\System\RefData as RefData;
use Dvsa\Olcs\Transfer\Query\Operator\UnlicensedVehicles as Qry;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

/**
 * Unlicensed Operator Vehicles Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class UnlicensedVehiclesTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = m::mock(VehiclesQueryHandler::class)->makePartial()->shouldAllowMockingProtectedMethods();
        $this->mockRepo('Organisation', OrganisationRepo::class);
        $this->mockRepo('LicenceVehicle', LicenceVehicleRepo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $organisationId = 69;
        $licenceId = 7;

        $query = Qry::create(['organisation' => $organisationId]);

        $organisation = m::mock(OrganisationEntity::class);
        $licence = m::mock(LicenceEntity::class);

        $this->repoMap['Organisation']
            ->shouldReceive('fetchById')
            ->with($organisationId)
            ->once()
            ->andReturn($organisation);

        $organisation
            ->shouldReceive('getLicences->first')
            ->once()
            ->andReturn($licence);

        $lvQuery = m::mock(QueryBuilder::class);
        $this->repoMap['LicenceVehicle']
            ->shouldReceive('createPaginatedVehiclesDataForUnlicensedOperatorQuery')
            ->with($query, $licenceId)
            ->andReturn($lvQuery)
            ->shouldReceive('fetchPaginatedList')
            ->with($lvQuery, Query::HYDRATE_OBJECT)
            ->andReturn(
                [
                    m::mock()->shouldReceive('serialize')->andReturn(['id' => 1])->getMock(),
                    m::mock()->shouldReceive('serialize')->andReturn(['id' => 2])->getMock(),
                ]
            )
            ->shouldReceive('fetchPaginatedCount')
            ->with($lvQuery)
            ->andReturn(2);

        $goodsOrPsv = m::mock(RefData::class);
        $goodsOrPsv
            ->shouldReceive('serialize')
            ->andReturn(['id' => 'lcat_psv']);
        $licence
            ->shouldReceive('getId')
            ->andReturn($licenceId)
            ->shouldReceive('getGoodsOrPsv')
            ->andReturn($goodsOrPsv);

        $result = $this->sut->handleQuery($query);

        $expected = [
            'result' => [
                ['id' => 1],
                ['id' => 2],
            ],
            'count' => 2,
            'goodsOrPsv' => [
                'id' => 'lcat_psv',
            ],
        ];

        $this->assertEquals($expected, $result);
    }
}
