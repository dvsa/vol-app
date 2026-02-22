<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\Expr\Func;
use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitRange;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermit as IrhpPermitEntity;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitRange as IrhpPermitRangeEntity;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Mockery as m;

/**
 * IRHP Permit Range test
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class IrhpPermitRangeTest extends RepositoryTestCase
{
    public function setUp(): void
    {
        $this->setUpSut(IrhpPermitRange::class);
    }

    public function testGetCombinedRangeSizeWithoutEmissionsCategoryId(): void
    {
        $combinedRangeSize = 1002;
        $stockId = 5;

        $queryBuilder = m::mock(QueryBuilder::class);
        $this->em->shouldReceive('createQueryBuilder')->once()->andReturn($queryBuilder);

        $queryBuilder->shouldReceive('select')
            ->with('SUM((ipr.toNo - ipr.fromNo) + 1)')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('from')
            ->with(IrhpPermitRangeEntity::class, 'ipr')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('where')
            ->with('ipr.ssReserve = false')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('andWhere')
            ->with('ipr.lostReplacement = false')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('andWhere')
            ->with('IDENTITY(ipr.irhpPermitStock) = ?1')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('setParameter')
            ->with(1, $stockId)
            ->once()
            ->andReturnSelf()
            ->shouldReceive('getQuery->getSingleScalarResult')
            ->once()
            ->andReturn($combinedRangeSize);

        $this->assertEquals(
            $combinedRangeSize,
            $this->sut->getCombinedRangeSize($stockId)
        );
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestGetCombinedRangeSizeWithEmissionsCategoryId')]
    public function testGetCombinedRangeSizeWithEmissionsCategoryId(mixed $emissionsCategoryId): void
    {
        $combinedRangeSize = 1002;
        $stockId = 5;

        $queryBuilder = m::mock(QueryBuilder::class);
        $this->em->shouldReceive('createQueryBuilder')->once()->andReturn($queryBuilder);

        $queryBuilder->shouldReceive('select')
            ->with('SUM((ipr.toNo - ipr.fromNo) + 1)')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('from')
            ->with(IrhpPermitRangeEntity::class, 'ipr')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('where')
            ->with('ipr.ssReserve = false')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('andWhere')
            ->with('ipr.lostReplacement = false')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('andWhere')
            ->with('IDENTITY(ipr.irhpPermitStock) = ?1')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('setParameter')
            ->with(1, $stockId)
            ->once()
            ->andReturnSelf()
            ->shouldReceive('andWhere')
            ->with('IDENTITY(ipr.emissionsCategory) = ?2')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('setParameter')
            ->with(2, $emissionsCategoryId)
            ->once()
            ->andReturnSelf()
            ->shouldReceive('getQuery->getSingleScalarResult')
            ->once()
            ->andReturn($combinedRangeSize);

        $this->assertEquals(
            $combinedRangeSize,
            $this->sut->getCombinedRangeSize($stockId, $emissionsCategoryId)
        );
    }

    public static function dpTestGetCombinedRangeSizeWithEmissionsCategoryId(): array
    {
        return [
            [RefData::EMISSIONS_CATEGORY_EURO5_REF],
            [RefData::EMISSIONS_CATEGORY_EURO6_REF],
        ];
    }

    public function testGetByStockId(): void
    {
        $expectedResult = [
            IrhpPermitRangeEntity::class,
            IrhpPermitRangeEntity::class,
            IrhpPermitRangeEntity::class,
        ];

        $stockId = 7;

        $queryBuilder = m::mock(QueryBuilder::class);
        $this->em->shouldReceive('createQueryBuilder')->once()->andReturn($queryBuilder);

        $queryBuilder->shouldReceive('select')
            ->with('ipr')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('from')
            ->with(IrhpPermitRangeEntity::class, 'ipr')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('where')
            ->with('ipr.ssReserve = false')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('andWhere')
            ->with('ipr.lostReplacement = false')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('andWhere')
            ->with('IDENTITY(ipr.irhpPermitStock) = ?1')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('setParameter')
            ->with(1, $stockId)
            ->once()
            ->andReturnSelf()
            ->shouldReceive('getQuery->getResult')
            ->once()
            ->andReturn($expectedResult);

        $this->assertEquals(
            $expectedResult,
            $this->sut->getByStockId($stockId)
        );
    }

    public function testFetchByPermitNumberAndStock(): void
    {
        $permitNumber = 200;
        $permitStock = 3;
        $expectedResult = [m::mock(IrhpPermitRangeEntity::class)];

        $queryBuilder = $this->createMockQb('BLAH');
        $this->mockCreateQueryBuilder($queryBuilder);

        $eqFunc1 = m::mock(Func::class);
        $gteFunc = m::mock(Func::class);
        $lteFunc = m::mock(Func::class);
        $eqFunc2 = m::mock(Func::class);

        $expr = m::mock(Expr::class);

        $queryBuilder->shouldReceive('andWhere')
            ->with($eqFunc1)
            ->andReturnSelf()
            ->shouldReceive('andWhere')
            ->with($gteFunc)
            ->andReturnSelf()
            ->shouldReceive('andWhere')
            ->with($lteFunc)
            ->andReturnSelf()
            ->shouldReceive('andWhere')
            ->with($eqFunc2)
            ->andReturnSelf()
            ->shouldReceive('setParameter')
            ->with('permitNumber', $permitNumber)
            ->andReturnSelf()
            ->shouldReceive('setParameter')
            ->with('permitStock', $permitStock)
            ->andReturnSelf()
            ->shouldReceive('getQuery->execute')
            ->once()
            ->andReturn($expectedResult);

        $queryBuilder->shouldReceive('expr')
            ->andReturn($expr);

        $expr->shouldReceive('eq')
            ->with('ipr.irhpPermitStock', ':permitStock')
            ->andReturn($eqFunc1);

        $expr->shouldReceive('gte')
            ->with(':permitNumber', 'ipr.fromNo')
            ->andReturn($gteFunc);

        $expr->shouldReceive('lte')
            ->with(':permitNumber', 'ipr.toNo')
            ->andReturn($lteFunc);

        $expr->shouldReceive('eq')
            ->with('ipr.lostReplacement', 1)
            ->andReturn($eqFunc2);

        $this->assertEquals(
            $expectedResult,
            $this->sut->fetchByPermitNumberAndStock($permitNumber, $permitStock)
        );
    }

    public function testFetchRangeIdToCountryIdAssociations(): void
    {
        $stockId = 14;

        $associations = [
            2 => 'AT',
            2 => 'RU',
            3 => 'GR'
        ];

        $dbalResult = m::mock(Result::class);
        $dbalResult->expects('fetchAllAssociative')
            ->andReturn($associations);

        $connection = m::mock(Connection::class);
        $connection->expects('executeQuery')
            ->with(
                'select iprc.irhp_permit_stock_range_id as rangeId, iprc.country_id as countryId ' .
                'from irhp_permit_range_country iprc ' .
                'inner join irhp_permit_range as r on r.id = iprc.irhp_permit_stock_range_id ' .
                'where r.irhp_permit_stock_id = :stockId',
                ['stockId' => $stockId]
            )
            ->andReturn($dbalResult);

        $this->em->shouldReceive('getConnection')->once()->andReturn($connection);

        $this->assertEquals(
            $associations,
            $this->sut->fetchRangeIdToCountryIdAssociations($stockId)
        );
    }

    public function testFetchReadyToPrint(): void
    {
        $irhpPermitStockId = 100;
        $results = [
            [
                'journey' => RefData::JOURNEY_SINGLE,
                'cabotage' => false,
            ],
            [
                'journey' => RefData::JOURNEY_MULTIPLE,
                'cabotage' => false,
            ],
            [
                'journey' => RefData::JOURNEY_SINGLE,
                'cabotage' => true,
            ],
            [
                'journey' => RefData::JOURNEY_MULTIPLE,
                'cabotage' => true,
            ],
        ];
        $expected = [
            IrhpPermitRangeEntity::BILATERAL_TYPE_STANDARD_SINGLE,
            IrhpPermitRangeEntity::BILATERAL_TYPE_STANDARD_MULTIPLE,
            IrhpPermitRangeEntity::BILATERAL_TYPE_CABOTAGE_SINGLE,
            IrhpPermitRangeEntity::BILATERAL_TYPE_CABOTAGE_MULTIPLE,
        ];

        $qb = $this->createMockQb('BLAH');

        $this->mockCreateQueryBuilder($qb);

        $qb->shouldReceive('getQuery')->andReturn(
            m::mock()->shouldReceive('execute')
                ->shouldReceive('getResult')
                ->andReturn($results)
                ->getMock()
        );
        $this->assertEquals($expected, $this->sut->fetchReadyToPrint($irhpPermitStockId));

        $expectedQuery = 'BLAH '
            . 'SELECT rd.id as journey, m.cabotage DISTINCT '
            . 'INNER JOIN m.irhpPermits ip '
            . 'INNER JOIN m.journey rd '
            . 'AND ip.status IN [[['
                . '"' . IrhpPermitEntity::STATUS_PENDING . '",'
                . '"' . IrhpPermitEntity::STATUS_AWAITING_PRINTING . '",'
                . '"' . IrhpPermitEntity::STATUS_PRINTING . '",'
                . '"' . IrhpPermitEntity::STATUS_ERROR . '"'
            . ']]] '
            . 'AND m.irhpPermitStock = [[100]] '
            . 'ORDER BY rd.id ASC '
            . 'ORDER BY m.cabotage ASC';

        $this->assertEquals($expectedQuery, $this->query);
    }

    public function testFindOverlappingRangesByType(): void
    {
        $irhpPermitStockId = 1;
        $prefix = 'UK';
        $from = 100;
        $to = 199;

        $results = ['RESULTS'];

        $qb = $this->createMockQb('BLAH');

        $this->mockCreateQueryBuilder($qb);

        $qb->shouldReceive('getQuery')->andReturn(
            m::mock()->shouldReceive('execute')
                ->shouldReceive('getResult')
                ->andReturn($results)
                ->getMock()
        );
        $this->assertEquals(
            $results,
            $this->sut->findOverlappingRangesByType(
                $irhpPermitStockId,
                $prefix,
                $from,
                $to
            )
        );

        $expectedQuery = 'BLAH '
            . 'OR m.fromNo BETWEEN [[' . $from . ']] AND [[' . $to . ']] '
            . 'OR m.toNo BETWEEN [[' . $from . ']] AND [[' . $to . ']] '
            . 'OR [[' . $from . ']] BETWEEN m.fromNo AND m.toNo '
            . 'AND m.irhpPermitStock = [[' . $irhpPermitStockId . ']] '
            . 'AND m.prefix = [[' . $prefix . ']]';

        $this->assertEquals($expectedQuery, $this->query);
    }

    public function testFindOverlappingRangesByTypeWithRange(): void
    {
        $irhpPermitStockId = 1;
        $irhpPermitRangeId = 2;
        $prefix = 'UK';
        $from = 100;
        $to = 199;

        $results = ['RESULTS'];

        $qb = $this->createMockQb('BLAH');

        $this->mockCreateQueryBuilder($qb);

        $qb->shouldReceive('getQuery')->andReturn(
            m::mock()->shouldReceive('execute')
                ->shouldReceive('getResult')
                ->andReturn($results)
                ->getMock()
        );
        $this->assertEquals(
            $results,
            $this->sut->findOverlappingRangesByType(
                $irhpPermitStockId,
                $prefix,
                $from,
                $to,
                $irhpPermitRangeId
            )
        );

        $expectedQuery = 'BLAH '
            . 'OR m.fromNo BETWEEN [[' . $from . ']] AND [[' . $to . ']] '
            . 'OR m.toNo BETWEEN [[' . $from . ']] AND [[' . $to . ']] '
            . 'OR [[' . $from . ']] BETWEEN m.fromNo AND m.toNo '
            . 'AND m.irhpPermitStock = [[' . $irhpPermitStockId . ']] '
            . 'AND m.prefix = [[' . $prefix . ']] '
            . 'AND m.id != [[' . $irhpPermitRangeId . ']]';

        $this->assertEquals($expectedQuery, $this->query);
    }
}
