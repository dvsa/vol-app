<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Licence;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Domain\QueryHandler\Licence\PsvVehicles;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity;
use Dvsa\Olcs\Transfer\Query\Licence\PsvVehicles as Qry;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;
use Doctrine\ORM\Query;

/**
 * Psv Vehicles Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class PsvVehiclesTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new PsvVehicles();
        $this->mockRepo('Licence', Repository\Licence::class);
        $this->mockRepo('LicenceVehicle', Repository\LicenceVehicle::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Qry::create(
            [
                'id' => 111,
                'includeRemoved' => true
            ]
        );

        /** @var Entity\Licence\Licence|m\MockInterface $licence */
        $licence = m::mock(Entity\Licence\Licence::class)->makePartial();
        $licence->shouldReceive('hasPsvBreakdown')->andReturn(false);
        $licence->shouldReceive('getOtherActiveLicences->isEmpty')->andReturn(true);
        $licence->shouldReceive('serialize')
            ->with(['organisation'])
            ->andReturn(['foo' => 'bar'])
            ->shouldReceive('getId')
            ->andReturn(111)
            ->once()
            ->shouldReceive('getActiveVehiclesCount')
            ->andReturn(1)
            ->once()
            ->getMock();
        $licence->shouldReceive('getLicenceVehicles->count')->andReturn(3)->once()->getMock();

        $this->repoMap['Licence']->shouldReceive('fetchUsingId')
            ->with($query)
            ->andReturn($licence);

        $mockList = m::mock()
            ->shouldReceive('serialize')
            ->andReturn(['foo' => 'bar'])
            ->once()
            ->getMock();

        $mockQb = m::mock(\Doctrine\ORM\QueryBuilder::class);

        $this->repoMap['LicenceVehicle']
            ->shouldReceive('createPaginatedVehiclesDataForLicenceQueryPsv')
            ->with($query, 111)
            ->andReturn($mockQb)
            ->once()
            ->shouldReceive('fetchPaginatedList')
            ->with($mockQb, Query::HYDRATE_OBJECT)
            ->andReturn([$mockList])
            ->once()
            ->shouldReceive('fetchPaginatedCount')
            ->andReturn(1)
            ->with($mockQb)
            ->once();

        $result = $this->sut->handleQuery($query);

        $data = $result->serialize();

        $expected = [
            'foo' => 'bar',
            'canTransfer' => false,
            'hasBreakdown' => false,
            'licenceVehicles' => ['results' => [['foo' => 'bar']], 'count' => 1],
            'activeVehicleCount' => 1,
            'allVehicleCount' => 3
        ];

        $this->assertEquals($expected, $data);
    }
}
