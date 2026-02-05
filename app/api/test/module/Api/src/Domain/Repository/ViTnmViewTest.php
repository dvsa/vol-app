<?php

declare(strict_types=1);

/**
 * VI Trading Name view test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Mockery as m;
use Dvsa\Olcs\Api\Domain\Repository\ViTnmView as ViTnmViewRepo;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Doctrine\ORM\QueryBuilder;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Exception\RuntimeException;

/**
 * VI Trading Name view test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class ViTnmViewTest extends RepositoryTestCase
{
    public function setUp(): void
    {
        $this->setUpSut(ViTnmViewRepo::class);
    }

    public function testFetchDiscsToPrint(): void
    {
        $mockQb = m::mock(QueryBuilder::class)
            ->shouldReceive('select')
            ->with('m.viLine as line')
            ->andReturnSelf()
            ->once()
            ->shouldReceive('addSelect')
            ->with('m.tradingNameId')
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

    public function testClearTradingNamesViIndicators(): void
    {
        $params = [['tradingNameId' => 1]];

        $this->expectQueryWithData('ViStoredProcedures\ViTnmComplete', ['tradingNameId' => 1]);
        $this->sut->clearTradingNamesViIndicators($params);
    }

    public function testClearTradingNamesViIndicatorsException(): void
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\RuntimeException::class);

        $params = [['tradingNameId' => 1]];

        $this->dbQueryService->shouldReceive('get')
            ->with('ViStoredProcedures\ViTnmComplete')
            ->andThrow(new RuntimeException('foo'));

        $this->sut->clearTradingNamesViIndicators($params);
    }
}
