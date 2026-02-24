<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\DBAL\Types\Types;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;
use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Api\Domain\Repository\Query\Permits\ExpireIrhpPermits as ExpireIrhpPermitsQuery;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Transfer\Query\IrhpPermit\GetListByIrhpId;
use Dvsa\Olcs\Transfer\Query\Permits\ReadyToPrint;
use Dvsa\Olcs\Transfer\Query\Permits\ReadyToPrintConfirm;
use Dvsa\Olcs\Transfer\Query\IrhpPermit\GetListByLicence;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermit;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermit as IrhpPermitEntity;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitRange as IrhpPermitRangeEntity;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitType as IrhpPermitTypeEntity;
use Mockery as m;
use PDO;

/**
 * IRHP Permit test
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class IrhpPermitTest extends RepositoryTestCase
{
    public function setUp(): void
    {
        $this->setUpSut(IrhpPermit::class);
    }

    public function testGetPermitCountWithoutEmissionsCategoryId(): void
    {
        $permitCount = 744;
        $stockId = 5;

        $queryBuilder = m::mock(QueryBuilder::class);
        $this->em->shouldReceive('createQueryBuilder')->once()->andReturn($queryBuilder);

        $queryBuilder->shouldReceive('select')
            ->with('count(ip.id)')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('from')
            ->with(IrhpPermitEntity::class, 'ip')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('innerJoin')
            ->with('ip.irhpPermitRange', 'ipr')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('where')
            ->with('IDENTITY(ipr.irhpPermitStock) = ?1')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('andWhere')
            ->with('ipr.ssReserve = false')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('andWhere')
            ->with('ipr.lostReplacement = false')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('setParameter')
            ->with(1, $stockId)
            ->once()
            ->andReturnSelf()
            ->shouldReceive('getQuery->getSingleScalarResult')
            ->once()
            ->andReturn($permitCount);

        $this->assertEquals(
            $permitCount,
            $this->sut->getPermitCount($stockId)
        );
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestGetPermitCountWithEmissionsCategoryId')]
    public function testGetPermitCountWithEmissionsCategoryId(mixed $emissionsCategoryId): void
    {
        $permitCount = 744;
        $stockId = 5;

        $queryBuilder = m::mock(QueryBuilder::class);
        $this->em->shouldReceive('createQueryBuilder')->once()->andReturn($queryBuilder);

        $queryBuilder->shouldReceive('select')
            ->with('count(ip.id)')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('from')
            ->with(IrhpPermitEntity::class, 'ip')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('innerJoin')
            ->with('ip.irhpPermitRange', 'ipr')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('where')
            ->with('IDENTITY(ipr.irhpPermitStock) = ?1')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('andWhere')
            ->with('ipr.ssReserve = false')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('andWhere')
            ->with('ipr.lostReplacement = false')
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
            ->andReturn($permitCount);

        $this->assertEquals(
            $permitCount,
            $this->sut->getPermitCount($stockId, $emissionsCategoryId)
        );
    }

    public static function dpTestGetPermitCountWithEmissionsCategoryId(): array
    {
        return [
            [RefData::EMISSIONS_CATEGORY_EURO5_REF],
            [RefData::EMISSIONS_CATEGORY_EURO6_REF],
        ];
    }

    public function testGetPermitCountByRange(): void
    {
        $permitCount = 200;
        $rangeId = 3;

        $queryBuilder = m::mock(QueryBuilder::class);
        $this->em->shouldReceive('createQueryBuilder')->once()->andReturn($queryBuilder);

        $queryBuilder->shouldReceive('select')
            ->with('count(ip.id)')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('from')
            ->with(IrhpPermitEntity::class, 'ip')
            ->once()
            ->andReturnSelf()
           ->shouldReceive('where')
            ->with('IDENTITY(ip.irhpPermitRange) = ?1')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('setParameter')
            ->with(1, $rangeId)
            ->once()
            ->andReturnSelf()
            ->shouldReceive('getQuery->getSingleScalarResult')
            ->once()
            ->andReturn($permitCount);

        $this->assertEquals(
            $permitCount,
            $this->sut->getPermitCountByRange($rangeId)
        );
    }

    public function testGetEcmtAnnualPermitCountByLicenceAndStockEndYear(): void
    {
        $licenceId = 47;
        $stockEndYear = 2023;
        $permitCount = 55;

        $queryBuilder = m::mock(QueryBuilder::class);
        $this->em->shouldReceive('createQueryBuilder')->once()->andReturn($queryBuilder);

        $queryBuilder->shouldReceive('select')
            ->with('count(ip.id)')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('from')
            ->with(IrhpPermitEntity::class, 'ip')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('innerJoin')
            ->with('ip.irhpPermitRange', 'ipr')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('innerJoin')
            ->with('ipr.irhpPermitStock', 'ips')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('innerJoin')
            ->with('ip.irhpPermitApplication', 'ipa')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('innerJoin')
            ->with('ipa.irhpApplication', 'ia')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('where')
            ->with('IDENTITY(ia.licence) = ?1')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('andWhere')
            ->with('YEAR(ips.validTo) = ?2')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('andWhere')
            ->with('IDENTITY(ips.irhpPermitType) = ?3')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('setParameter')
            ->with(1, $licenceId)
            ->once()
            ->andReturnSelf()
            ->shouldReceive('setParameter')
            ->with(2, $stockEndYear)
            ->once()
            ->andReturnSelf()
            ->shouldReceive('setParameter')
            ->with(3, IrhpPermitTypeEntity::IRHP_PERMIT_TYPE_ID_ECMT)
            ->once()
            ->andReturnSelf()
            ->shouldReceive('getQuery->getSingleScalarResult')
            ->once()
            ->andReturn($permitCount);

        $this->assertEquals(
            $permitCount,
            $this->sut->getEcmtAnnualPermitCountByLicenceAndStockEndYear($licenceId, $stockEndYear)
        );
    }

    public function testGetAssignedPermitNumbersByRange(): void
    {
        $rangeId = 45;

        $queryBuilder = m::mock(QueryBuilder::class);
        $this->em->shouldReceive('createQueryBuilder')->once()->andReturn($queryBuilder);

        $unflattenedPermitNumbers = [
            ['permitNumber' => 4],
            ['permitNumber' => 5],
            ['permitNumber' => 7],
            ['permitNumber' => 8],
        ];

        $flattenedPermitNumbers = [4, 5, 7, 8];

        $queryBuilder->shouldReceive('select')
            ->with('ip.permitNumber')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('from')
            ->with(IrhpPermitEntity::class, 'ip')
            ->once()
            ->andReturnSelf()
           ->shouldReceive('where')
            ->with('IDENTITY(ip.irhpPermitRange) = ?1')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('setParameter')
            ->with(1, $rangeId)
            ->once()
            ->andReturnSelf()
            ->shouldReceive('getQuery->getScalarResult')
            ->once()
            ->andReturn($unflattenedPermitNumbers);

        $this->assertEquals(
            $flattenedPermitNumbers,
            $this->sut->getAssignedPermitNumbersByRange($rangeId)
        );
    }

    public function testFetchListForReadyToPrint(): void
    {
        $this->setUpSut(IrhpPermit::class, true);
        $this->sut->shouldReceive('fetchPaginatedList')->andReturn(['RESULTS']);

        $qb = $this->createMockQb('BLAH');
        $this->mockCreateQueryBuilder($qb);

        $this->queryBuilder
            ->shouldReceive('modifyQuery')->with($qb)->andReturnSelf()
            ->shouldReceive('withRefdata')->once()->andReturnSelf()
            ->shouldReceive('with')->with('irhpPermitApplication', 'ipa')->once()->andReturnSelf()
            ->shouldReceive('paginate')->once()->andReturnSelf();

        $query = ReadyToPrint::create([]);
        $this->assertEquals(['RESULTS'], $this->sut->fetchList($query));

        $expectedQuery = 'BLAH '
            . 'INNER JOIN ipa.irhpApplication ia '
            . 'AND m.status IN [[['
                . '"' . IrhpPermitEntity::STATUS_PENDING . '",'
                . '"' . IrhpPermitEntity::STATUS_AWAITING_PRINTING . '",'
                . '"' . IrhpPermitEntity::STATUS_PRINTING . '",'
                . '"' . IrhpPermitEntity::STATUS_ERROR . '"'
            . ']]]';
        $this->assertEquals($expectedQuery, $this->query);
    }

    public function testFetchListForReadyToPrintWithStock(): void
    {
        $this->setUpSut(IrhpPermit::class, true);
        $this->sut->shouldReceive('fetchPaginatedList')->andReturn(['RESULTS']);

        $qb = $this->createMockQb('BLAH');
        $this->mockCreateQueryBuilder($qb);

        $this->queryBuilder
            ->shouldReceive('modifyQuery')->with($qb)->andReturnSelf()
            ->shouldReceive('withRefdata')->once()->andReturnSelf()
            ->shouldReceive('with')->with('irhpPermitApplication', 'ipa')->once()->andReturnSelf()
            ->shouldReceive('paginate')->once()->andReturnSelf();

        $query = ReadyToPrint::create(['irhpPermitStock' => 100]);
        $this->assertEquals(['RESULTS'], $this->sut->fetchList($query));

        $expectedQuery = 'BLAH '
            . 'INNER JOIN ipa.irhpApplication ia '
            . 'INNER JOIN m.irhpPermitRange ipr '
            . 'INNER JOIN ipr.irhpPermitStock ips '
            . 'AND ips.id = [[100]] '
            . 'AND m.status IN [[['
                . '"' . IrhpPermitEntity::STATUS_PENDING . '",'
                . '"' . IrhpPermitEntity::STATUS_AWAITING_PRINTING . '",'
                . '"' . IrhpPermitEntity::STATUS_PRINTING . '",'
                . '"' . IrhpPermitEntity::STATUS_ERROR . '"'
            . ']]]';
        $this->assertEquals($expectedQuery, $this->query);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpFetchListForReadyToPrintWithStockAndRangeType')]
    public function testFetchListForReadyToPrintWithStockAndRangeType(mixed $irhpPermitRangeType, mixed $expectedJourney, mixed $expectedCabotage): void
    {
        $this->setUpSut(IrhpPermit::class, true);
        $this->sut->shouldReceive('fetchPaginatedList')->andReturn(['RESULTS']);

        $qb = $this->createMockQb('BLAH');
        $this->mockCreateQueryBuilder($qb);

        $this->queryBuilder
            ->shouldReceive('modifyQuery')->with($qb)->andReturnSelf()
            ->shouldReceive('withRefdata')->once()->andReturnSelf()
            ->shouldReceive('with')->with('irhpPermitApplication', 'ipa')->once()->andReturnSelf()
            ->shouldReceive('paginate')->once()->andReturnSelf();

        $query = ReadyToPrint::create(
            [
                'irhpPermitStock' => 100,
                'irhpPermitRangeType' => $irhpPermitRangeType,
            ]
        );
        $this->assertEquals(['RESULTS'], $this->sut->fetchList($query));

        $expectedQuery = 'BLAH '
            . 'INNER JOIN ipa.irhpApplication ia '
            . 'INNER JOIN m.irhpPermitRange ipr '
            . 'INNER JOIN ipr.irhpPermitStock ips '
            . 'AND ips.id = [[100]] '
            . 'AND ipr.journey = [[' . $expectedJourney . ']] '
            . 'AND ipr.cabotage = [[' . $expectedCabotage . ']] '
            . 'AND m.status IN [[['
                . '"' . IrhpPermitEntity::STATUS_PENDING . '",'
                . '"' . IrhpPermitEntity::STATUS_AWAITING_PRINTING . '",'
                . '"' . IrhpPermitEntity::STATUS_PRINTING . '",'
                . '"' . IrhpPermitEntity::STATUS_ERROR . '"'
            . ']]]';
        $this->assertEquals($expectedQuery, $this->query);
    }

    public static function dpFetchListForReadyToPrintWithStockAndRangeType(): array
    {
        return [
            [
                IrhpPermitRangeEntity::BILATERAL_TYPE_STANDARD_SINGLE,
                RefData::JOURNEY_SINGLE,
                'false',
            ],
            [
                IrhpPermitRangeEntity::BILATERAL_TYPE_STANDARD_MULTIPLE,
                RefData::JOURNEY_MULTIPLE,
                'false',
            ],
            [
                IrhpPermitRangeEntity::BILATERAL_TYPE_CABOTAGE_SINGLE,
                RefData::JOURNEY_SINGLE,
                'true',
            ],
            [
                IrhpPermitRangeEntity::BILATERAL_TYPE_CABOTAGE_MULTIPLE,
                RefData::JOURNEY_MULTIPLE,
                'true',
            ],
        ];
    }

    public function testFetchListForReadyToPrintConfirm(): void
    {
        $this->setUpSut(IrhpPermit::class, true);
        $this->sut->shouldReceive('fetchPaginatedList')->andReturn(['RESULTS']);

        $qb = $this->createMockQb('BLAH');
        $this->mockCreateQueryBuilder($qb);

        $this->queryBuilder
            ->shouldReceive('modifyQuery')->with($qb)->andReturnSelf()
            ->shouldReceive('withRefdata')->once()->andReturnSelf()
            ->shouldReceive('with')->with('irhpPermitApplication', 'ipa')->once()->andReturnSelf()
            ->shouldReceive('paginate')->andReturnSelf();

        $query = ReadyToPrintConfirm::create(['ids' => [1, 2, 3]]);
        $this->assertEquals(['RESULTS'], $this->sut->fetchList($query));

        $expectedQuery = 'BLAH '
            . 'AND m.id IN [[[1,2,3]]] '
            . 'ORDER BY m.permitNumber ASC';
        $this->assertEquals($expectedQuery, $this->query);
    }

    public function testFetchByNumberAndRange(): void
    {
        $permitNumber = 1500;
        $rangeId = 7;

        $queryBuilder = m::mock(QueryBuilder::class);
        $this->em->shouldReceive('createQueryBuilder')->once()->andReturn($queryBuilder);

        $queryBuilder->shouldReceive('select')
            ->with('ip')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('from')
            ->with(IrhpPermitEntity::class, 'ip')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('where')
            ->with('ip.permitNumber = ?1')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('andWhere')
            ->with('ip.irhpPermitRange = ?2')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('setParameter')
            ->with(1, $permitNumber)
            ->once()
            ->andReturnSelf()
            ->shouldReceive('setParameter')
            ->with(2, $rangeId)
            ->once()
            ->andReturnSelf()
            ->shouldReceive('getQuery->execute')
            ->once()
            ->andReturn([]);

        $this->assertEquals(
            [],
            $this->sut->fetchByNumberAndRange($permitNumber, $rangeId)
        );
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpFetchListByLicence')]
    public function testFetchListByLicence(mixed $status, mixed $validOnly, mixed $expectedStatuses): void
    {
        $this->setUpSut(IrhpPermit::class, true);
        $this->sut->shouldReceive('fetchPaginatedList')->andReturn(['RESULTS']);

        $qb = $this->createMockQb('BLAH');
        $this->mockCreateQueryBuilder($qb);

        $this->queryBuilder
            ->shouldReceive('modifyQuery')->with($qb)->andReturnSelf()
            ->shouldReceive('withRefdata')->once()->andReturnSelf()
            ->shouldReceive('with')->with('irhpPermitApplication', 'ipa')->once()->andReturnSelf()
            ->shouldReceive('paginate')->once()->andReturnSelf();

        $query = GetListByLicence::create(
            [
                'licence' => 7,
                'irhpPermitType' => IrhpPermitTypeEntity::IRHP_PERMIT_TYPE_ID_BILATERAL,
                'page' => 1,
                'limit' => 10,
                'status' => $status,
                'validOnly' => $validOnly,
            ]
        );
        $this->assertEquals(['RESULTS'], $this->sut->fetchList($query));

        $expectedQuery = 'BLAH '
            . 'INNER JOIN ipa.irhpApplication ia '
            . 'INNER JOIN m.irhpPermitRange ipr '
            . 'INNER JOIN ipr.irhpPermitStock ips '
            . 'LEFT JOIN ips.country ipc '
            . 'AND ia.licence = [[7]] '
            . 'AND m.status IN [[["' . implode('","', $expectedStatuses) . '"]]] '
            . 'AND ips.irhpPermitType = [[' . IrhpPermitTypeEntity::IRHP_PERMIT_TYPE_ID_BILATERAL . ']] '
            . 'ORDER BY ipc.countryDesc ASC '
            . 'ORDER BY m.expiryDate ASC '
            . 'ORDER BY ipa.id ASC '
            . 'ORDER BY m.permitNumber ASC';
        $this->assertEquals($expectedQuery, $this->query);
    }

    public static function dpFetchListByLicence(): array
    {
        return [
            'valid only' => [null, true, IrhpPermitEntity::$validStatuses],
            'all' => [null, false, IrhpPermitEntity::ALL_STATUSES],
            'specific' => [IrhpPermitEntity::STATUS_PRINTING, null, [IrhpPermitEntity::STATUS_PRINTING]],
        ];
    }

    public function testGetLivePermitCountsGroupedByStock(): void
    {
        $licenceId = 47;

        $livePermitCounts = [
            [
                'irhpPermitStockId' => 7,
                'irhpPermitCount' => 8
            ],
            [
                'irhpPermitStockId' => 5,
                'irhpPermitCount' => 12
            ]
        ];

        $dbalResult = m::mock(Result::class);
        $dbalResult->expects('fetchAllAssociative')
            ->andReturn($livePermitCounts);

        $connection = m::mock(Connection::class);
        $connection->expects('executeQuery')
            ->with(
                'select ips.id AS irhpPermitStockId, ' .
                'count(ip.id) AS irhpPermitCount ' .
                'from irhp_permit ip ' .
                'inner join irhp_permit_application ipa ON ip.irhp_permit_application_id = ipa.id ' .
                'and ip.status in (?) ' .
                'inner join irhp_application ia ON ipa.irhp_application_id = ia.id ' .
                'inner join irhp_permit_window ipw ON ipa.irhp_permit_window_id = ipw.id ' .
                'inner join irhp_permit_stock ips ON ipw.irhp_permit_stock_id = ips.id ' .
                'where ia.licence_id = ? ' .
                'group BY ips.id',
                [
                    [
                        IrhpPermitEntity::STATUS_PENDING,
                        IrhpPermitEntity::STATUS_AWAITING_PRINTING,
                        IrhpPermitEntity::STATUS_PRINTING,
                        IrhpPermitEntity::STATUS_PRINTED
                    ],
                    $licenceId
                ],
                [
                    Types::STRING,
                    PDO::PARAM_INT
                ]
            )
            ->andReturn($dbalResult);

        $this->em->shouldReceive('getConnection')->once()->andReturn($connection);

        $this->assertEquals(
            $livePermitCounts,
            $this->sut->getLivePermitCountsGroupedByStock($licenceId)
        );
    }

    public function testFetchListByIrhpId(): void
    {
        $this->setUpSut(IrhpPermit::class, true);
        $this->sut->shouldReceive('fetchPaginatedList')->andReturn(['RESULTS']);

        $qb = $this->createMockQb('BLAH');
        $this->mockCreateQueryBuilder($qb);

        $this->queryBuilder
            ->shouldReceive('modifyQuery')->with($qb)->andReturnSelf()
            ->shouldReceive('withRefdata')->once()->andReturnSelf()
            ->shouldReceive('with')->with('irhpPermitApplication', 'ipa')->once()->andReturnSelf()
            ->shouldReceive('paginate')->once()->andReturnSelf();

        $query = GetListByIrhpId::create(['irhpApplication' => 2, 'page' => 1, 'limit' => 10]);
        $this->assertEquals(['RESULTS'], $this->sut->fetchList($query));

        $expectedQuery = 'BLAH '
            . 'AND ipa.irhpApplication = [[2]]';
        $this->assertEquals($expectedQuery, $this->query);
    }

    public function testMarkAsExpired(): void
    {
        $this->expectQueryWithData(ExpireIrhpPermitsQuery::class, []);
        $this->sut->markAsExpired();
    }
}
