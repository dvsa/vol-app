<?php

declare(strict_types=1);

/**
 * VI Vehicle view test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Mockery as m;
use Dvsa\Olcs\Api\Domain\Repository\ViVhlView as ViVhlViewRepo;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Doctrine\ORM\QueryBuilder;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Exception\RuntimeException;

/**
 * VI Vehicle view test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class ViVhlViewTest extends RepositoryTestCase
{
    public function setUp(): void
    {
        $this->setUpSut(ViVhlViewRepo::class);
    }

    public function testFetchDiscsToPrint(): void
    {
        $mockQb = m::mock(QueryBuilder::class)
            ->shouldReceive('select')
            ->with('m.viLine as line')
            ->andReturnSelf()
            ->once()
            ->shouldReceive('addSelect')
            ->with('m.licId')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('addSelect')
            ->with('m.vhlId')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('getQuery')
            ->andReturn(
                m::mock()
                ->shouldReceive('getResult')
                ->with(Query::HYDRATE_ARRAY)
                ->once()
                ->andReturn(['result'])
                ->getMock()
            )
            ->getMock();

        $this->em
            ->shouldReceive('getRepository->createQueryBuilder')
            ->once()
            ->andReturn($mockQb);

        $this->assertEquals(['result'], $this->sut->fetchForExport());
    }

    public function testClearLicenceVehiclesViIndicators(): void
    {
        $params = [
            [
                'licId' => 1,
                'vhlId' => 2
            ]
        ];

        $this->expectQueryWithData('ViStoredProcedures\ViVhlComplete', ['licenceId' => 1, 'vehicleId' => '2']);
        $this->sut->clearLicenceVehiclesViIndicators($params);
    }

    public function testClearLicenceVehiclesViIndicatorsException(): void
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\RuntimeException::class);

        $params = [
            [
                'licId' => 1,
                'vhlId' => 2
            ]
        ];

        $this->dbQueryService->shouldReceive('get')
            ->with('ViStoredProcedures\ViVhlComplete')
            ->andThrow(new RuntimeException('foo'));

        $this->sut->clearLicenceVehiclesViIndicators($params);
    }
}
