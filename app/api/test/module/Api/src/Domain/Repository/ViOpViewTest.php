<?php

declare(strict_types=1);

/**
 * VI Operator view test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Mockery as m;
use Dvsa\Olcs\Api\Domain\Repository\ViOpView as ViOpViewRepo;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Doctrine\ORM\QueryBuilder;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Exception\RuntimeException;

/**
 * VI Operator view test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class ViOpViewTest extends RepositoryTestCase
{
    public function setUp(): void
    {
        $this->setUpSut(ViOpViewRepo::class);
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
            ->andReturnSelf()
            ->once()
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

    public function testClearLicencesViIndicators(): void
    {
        $params = [['licId' => 1]];

        $this->expectQueryWithData('ViStoredProcedures\ViOpComplete', ['licenceId' => 1]);
        $this->sut->clearLicencesViIndicators($params);
    }

    public function testClearLicencesViIndicatorsException(): void
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\RuntimeException::class);

        $params = [['licId' => 1]];

        $this->dbQueryService->shouldReceive('get')
            ->with('ViStoredProcedures\ViOpComplete')
            ->andThrow(new RuntimeException('foo'));

        $this->sut->clearLicencesViIndicators($params);
    }
}
