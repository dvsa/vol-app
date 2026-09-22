<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitRange as Repo;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermit as IrhpPermitEntity;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitRange as Entity;
use Mockery as m;

final class IrhpPermitRangeTest extends RepositoryTestCase
{
    private const string SUM_SELECT = 'SELECT SUM((ipr.toNo - ipr.fromNo) + 1) FROM ' . Entity::class . ' ipr';

    private const string SUM_WHERE = ' WHERE ipr.ssReserve = false AND ipr.lostReplacement = false'
        . ' AND IDENTITY(ipr.irhpPermitStock) = ?1';

    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(Repo::class);
    }

    public function testGetCombinedRangeSizeWithoutEmissionsCategoryId(): void
    {
        $qb = $this->newRealQb();
        $qb->stubbedQuery()->expects('getSingleScalarResult')->andReturn(1002);
        $this->em->expects('createQueryBuilder')->withNoArgs()->andReturn($qb);

        $this->assertSame(1002, $this->sut->getCombinedRangeSize(5));

        $this->assertSame(self::SUM_SELECT . self::SUM_WHERE, $qb->getDQL());
        $this->assertSame(5, $qb->getParameter(1)->getValue());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('emissionsCategoryProvider')]
    public function testGetCombinedRangeSizeWithEmissionsCategoryId(string $emissionsCategoryId): void
    {
        $qb = $this->newRealQb();
        $qb->stubbedQuery()->expects('getSingleScalarResult')->andReturn(1002);
        $this->em->expects('createQueryBuilder')->withNoArgs()->andReturn($qb);

        $this->assertSame(1002, $this->sut->getCombinedRangeSize(5, $emissionsCategoryId));

        $this->assertSame(
            self::SUM_SELECT . self::SUM_WHERE . ' AND IDENTITY(ipr.emissionsCategory) = ?2',
            $qb->getDQL(),
        );
        $this->assertSame($emissionsCategoryId, $qb->getParameter(2)->getValue());
    }

    public static function emissionsCategoryProvider(): \Iterator
    {
        yield 'euro5' => [\Dvsa\Olcs\Api\Entity\System\RefData::EMISSIONS_CATEGORY_EURO5_REF];
        yield 'euro6' => [\Dvsa\Olcs\Api\Entity\System\RefData::EMISSIONS_CATEGORY_EURO6_REF];
    }

    public function testGetByStockId(): void
    {
        $qb = $this->newRealQb()->willReturn(['RESULTS']);
        $this->em->expects('createQueryBuilder')->withNoArgs()->andReturn($qb);

        $this->assertSame(['RESULTS'], $this->sut->getByStockId(5));

        $this->assertSame(
            'SELECT ipr FROM ' . Entity::class . ' ipr' . self::SUM_WHERE,
            $qb->getDQL(),
        );
        $this->assertSame(5, $qb->getParameter(1)->getValue());
    }

    /**
     * The permit number is compared against the range bounds the other way round — the bound
     * is the second operand — so the parameter appears on the left of both comparisons.
     */
    public function testFetchByPermitNumberAndStock(): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('execute')->withNoArgs()->andReturn(['RESULTS']);

        $this->assertSame(['RESULTS'], $this->sut->fetchByPermitNumberAndStock(150, 1));

        $this->assertSame(
            'SELECT m FROM ' . Entity::class . ' m'
            . ' WHERE m.irhpPermitStock = :permitStock AND :permitNumber >= m.fromNo'
            . ' AND :permitNumber <= m.toNo AND m.lostReplacement = 1',
            $qb->getDQL(),
        );
        $this->assertSame(150, $qb->getParameter('permitNumber')->getValue());
        $this->assertSame(1, $qb->getParameter('permitStock')->getValue());
    }

    public function testFetchRangeIdToCountryIdAssociations(): void
    {
        $associations = [2 => 'RU', 3 => 'GR'];

        $dbalResult = m::mock(Result::class);
        $dbalResult->expects('fetchAllAssociative')->andReturn($associations);

        $connection = m::mock(Connection::class);
        $connection->expects('executeQuery')
            ->with(
                'select iprc.irhp_permit_stock_range_id as rangeId, iprc.country_id as countryId '
                . 'from irhp_permit_range_country iprc '
                . 'inner join irhp_permit_range as r on r.id = iprc.irhp_permit_stock_range_id '
                . 'where r.irhp_permit_stock_id = :stockId',
                ['stockId' => 14],
            )
            ->andReturn($dbalResult);

        $this->em->expects('getConnection')->withNoArgs()->andReturn($connection);

        $this->assertSame($associations, $this->sut->fetchRangeIdToCountryIdAssociations(14));
    }

    public function testFetchReadyToPrint(): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getResult')->with(Query::HYDRATE_ARRAY)->andReturn([]);

        $this->assertSame([], $this->sut->fetchReadyToPrint(1));

        $this->assertSame(
            'SELECT DISTINCT rd.id as journey, m.cabotage FROM ' . Entity::class . ' m'
            . ' INNER JOIN m.irhpPermits ip INNER JOIN m.journey rd'
            . ' WHERE ip.status IN(:statuses) AND m.irhpPermitStock = :irhpPermitStockId'
            . ' ORDER BY rd.id ASC, m.cabotage ASC',
            $qb->getDQL(),
        );
        $this->assertSame(IrhpPermitEntity::$readyToPrintStatuses, $qb->getParameter('statuses')->getValue());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('overlappingProvider')]
    public function testFindOverlappingRangesByType(?int $excludedRange, string $expectedExtraWhere): void
    {
        $qb = $this->createRealQb()->willReturn(['RESULTS']);

        $this->assertSame(
            ['RESULTS'],
            $this->sut->findOverlappingRangesByType(1, 'UK', 100, 199, $excludedRange),
        );

        $this->assertSame(
            // Doctrine brackets the orWhere group, so the overlap test is ANDed with the
            // stock and prefix filters rather than binding loosely. The old flat string could
            // not show whether that precedence held.
            'SELECT m FROM ' . Entity::class . ' m'
            . ' WHERE ((m.fromNo BETWEEN :fromNo AND :toNo) OR (m.toNo BETWEEN :fromNo AND :toNo)'
            . ' OR (:fromNo BETWEEN m.fromNo AND m.toNo))'
            . ' AND m.irhpPermitStock = :irhpPermitStock AND m.prefix = :prefix'
            . $expectedExtraWhere,
            $qb->getDQL(),
        );
        $this->assertSame(1, $qb->getParameter('irhpPermitStock')->getValue());
        $this->assertSame('UK', $qb->getParameter('prefix')->getValue());
    }

    public static function overlappingProvider(): \Iterator
    {
        yield 'without an excluded range' => [null, ''];
        yield 'excluding a range' => [7, ' AND m.id <> :irhpPermitRange'];
    }
}
