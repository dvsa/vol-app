<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Result;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermit as Repo;
use Dvsa\Olcs\Api\Domain\Repository\Query\Permits\ExpireIrhpPermits as ExpireIrhpPermitsQuery;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermit as Entity;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitRange as IrhpPermitRangeEntity;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitType as IrhpPermitTypeEntity;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Transfer\Query\IrhpPermit\GetListByIrhpId;
use Dvsa\Olcs\Transfer\Query\IrhpPermit\GetListByLicence;
use Dvsa\Olcs\Transfer\Query\Permits\ReadyToPrint;
use Dvsa\Olcs\Transfer\Query\Permits\ReadyToPrintConfirm;
use Mockery as m;

final class IrhpPermitTest extends RepositoryTestCase
{
    private const string FROM = ' FROM ' . Entity::class . ' m';

    private const string LIST_SELECT = 'SELECT m, w0, ipa';

    private const string LIST_JOINS = ' LEFT JOIN m.status w0 LEFT JOIN m.irhpPermitApplication ipa';

    private const string COUNT_FROM = ' FROM ' . Entity::class . ' ip';

    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(Repo::class, true);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('permitCountProvider')]
    public function testGetPermitCount(?string $emissionsCategoryId, string $expectedExtra): void
    {
        $qb = $this->newRealQb();
        $qb->stubbedQuery()->expects('getSingleScalarResult')->andReturn(5);
        $this->em->expects('createQueryBuilder')->withNoArgs()->andReturn($qb);

        $this->assertSame(5, $this->sut->getPermitCount(1, $emissionsCategoryId));

        $this->assertSame(
            'SELECT count(ip.id)' . self::COUNT_FROM . ' INNER JOIN ip.irhpPermitRange ipr'
            . ' WHERE IDENTITY(ipr.irhpPermitStock) = ?1'
            . ' AND ipr.ssReserve = false AND ipr.lostReplacement = false'
            . $expectedExtra,
            $qb->getDQL(),
        );
    }

    public static function permitCountProvider(): \Iterator
    {
        yield 'all emissions categories' => [null, ''];
        yield 'one emissions category' => [
            RefData::EMISSIONS_CATEGORY_EURO5_REF,
            ' AND IDENTITY(ipr.emissionsCategory) = ?2',
        ];
    }

    public function testGetPermitCountByRange(): void
    {
        $qb = $this->newRealQb();
        $qb->stubbedQuery()->expects('getSingleScalarResult')->andReturn(5);
        $this->em->expects('createQueryBuilder')->withNoArgs()->andReturn($qb);

        $this->assertSame(5, $this->sut->getPermitCountByRange(1));

        $this->assertSame(
            'SELECT count(ip.id)' . self::COUNT_FROM . ' WHERE IDENTITY(ip.irhpPermitRange) = ?1',
            $qb->getDQL(),
        );
    }

    public function testGetEcmtAnnualPermitCountByLicenceAndStockEndYear(): void
    {
        $qb = $this->newRealQb();
        $qb->stubbedQuery()->expects('getSingleScalarResult')->andReturn(5);
        $this->em->expects('createQueryBuilder')->withNoArgs()->andReturn($qb);

        $this->assertSame(5, $this->sut->getEcmtAnnualPermitCountByLicenceAndStockEndYear(7, 2019));

        $this->assertSame(
            'SELECT count(ip.id)' . self::COUNT_FROM
            . ' INNER JOIN ip.irhpPermitRange ipr INNER JOIN ipr.irhpPermitStock ips'
            . ' INNER JOIN ip.irhpPermitApplication ipa INNER JOIN ipa.irhpApplication ia'
            . ' WHERE IDENTITY(ia.licence) = ?1 AND YEAR(ips.validTo) = ?2'
            . ' AND IDENTITY(ips.irhpPermitType) = ?3',
            $qb->getDQL(),
        );
        $this->assertSame(
            IrhpPermitTypeEntity::IRHP_PERMIT_TYPE_ID_ECMT,
            $qb->getParameter(3)->getValue(),
        );
    }

    public function testGetAssignedPermitNumbersByRange(): void
    {
        $qb = $this->newRealQb();
        $qb->stubbedQuery()->expects('getScalarResult')->andReturn([
            ['permitNumber' => 1],
            ['permitNumber' => 2],
        ]);
        $this->em->expects('createQueryBuilder')->withNoArgs()->andReturn($qb);

        $this->assertSame([1, 2], $this->sut->getAssignedPermitNumbersByRange(1));

        $this->assertSame(
            'SELECT ip.permitNumber' . self::COUNT_FROM . ' WHERE IDENTITY(ip.irhpPermitRange) = ?1',
            $qb->getDQL(),
        );
    }

    public function testFetchByNumberAndRange(): void
    {
        $qb = $this->newRealQb();
        $qb->stubbedQuery()->expects('execute')->withNoArgs()->andReturn(['RESULTS']);
        $this->em->expects('createQueryBuilder')->withNoArgs()->andReturn($qb);

        $this->assertSame(['RESULTS'], $this->sut->fetchByNumberAndRange(1, 2));

        $this->assertSame(
            'SELECT ip' . self::COUNT_FROM
            . ' WHERE ip.permitNumber = ?1 AND ip.irhpPermitRange = ?2',
            $qb->getDQL(),
        );
    }

    /**
     * The irhpApplication join is not needed by the filter itself; it exists so the table can be
     * sorted by application id later.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('readyToPrintProvider')]
    public function testFetchListForReadyToPrint(array $data, string $expectedTail): void
    {
        $qb = $this->createRealQb();
        $this->sut->expects('fetchPaginatedList')->andReturn(['RESULTS']);

        $this->assertSame(['RESULTS'], $this->sut->fetchList(ReadyToPrint::create($data)));

        $this->assertSame(self::LIST_SELECT . self::FROM . self::LIST_JOINS . $expectedTail, $qb->getDQL());
        $this->assertSame(Entity::$readyToPrintStatuses, $qb->getParameter('statuses')->getValue());
    }

    public static function readyToPrintProvider(): \Iterator
    {
        $applicationJoin = ' INNER JOIN ipa.irhpApplication ia';

        yield 'no stock' => [[], $applicationJoin . ' WHERE m.status IN(:statuses)'];

        yield 'with stock' => [
            ['irhpPermitStock' => 100],
            $applicationJoin . ' INNER JOIN m.irhpPermitRange ipr INNER JOIN ipr.irhpPermitStock ips'
            . ' WHERE ips.id = :stockId AND m.status IN(:statuses)',
        ];
    }

    /**
     * Each bilateral range type maps to a journey and cabotage pair.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('rangeTypeProvider')]
    public function testFetchListForReadyToPrintWithStockAndRangeType(
        string $rangeType,
        string $expectedJourney,
        bool $expectedCabotage,
    ): void {
        $qb = $this->createRealQb();
        $this->sut->expects('fetchPaginatedList')->andReturn(['RESULTS']);

        $query = ReadyToPrint::create(['irhpPermitStock' => 100, 'irhpPermitRangeType' => $rangeType]);

        $this->assertSame(['RESULTS'], $this->sut->fetchList($query));

        $this->assertStringContainsString(
            'AND ipr.journey = :journey AND ipr.cabotage = :cabotage',
            $qb->getDQL(),
        );
        $this->assertSame($expectedJourney, $qb->getParameter('journey')->getValue());
        $this->assertSame($expectedCabotage, $qb->getParameter('cabotage')->getValue());
    }

    public static function rangeTypeProvider(): \Iterator
    {
        yield 'standard single' => [
            IrhpPermitRangeEntity::BILATERAL_TYPE_STANDARD_SINGLE,
            RefData::JOURNEY_SINGLE,
            false,
        ];
        yield 'standard multiple' => [
            IrhpPermitRangeEntity::BILATERAL_TYPE_STANDARD_MULTIPLE,
            RefData::JOURNEY_MULTIPLE,
            false,
        ];
        yield 'cabotage single' => [
            IrhpPermitRangeEntity::BILATERAL_TYPE_CABOTAGE_SINGLE,
            RefData::JOURNEY_SINGLE,
            true,
        ];
        yield 'cabotage multiple' => [
            IrhpPermitRangeEntity::BILATERAL_TYPE_CABOTAGE_MULTIPLE,
            RefData::JOURNEY_MULTIPLE,
            true,
        ];
    }

    public function testFetchListForReadyToPrintConfirm(): void
    {
        $qb = $this->createRealQb();
        $this->sut->expects('fetchPaginatedList')->andReturn(['RESULTS']);

        $this->assertSame(
            ['RESULTS'],
            $this->sut->fetchList(ReadyToPrintConfirm::create(['ids' => [1, 2, 3]])),
        );

        $this->assertSame(
            self::LIST_SELECT . self::FROM . self::LIST_JOINS
            . ' WHERE m.id IN(:ids)'
            . ' ORDER BY m.permitNumber ASC',
            $qb->getDQL(),
        );
        $this->assertSame([1, 2, 3], $qb->getParameter('ids')->getValue());
    }

    public function testFetchListByIrhpId(): void
    {
        $qb = $this->createRealQb();
        $this->sut->expects('fetchPaginatedList')->andReturn(['RESULTS']);

        $query = GetListByIrhpId::create(['irhpApplication' => 2, 'page' => 1, 'limit' => 10]);

        $this->assertSame(['RESULTS'], $this->sut->fetchList($query));

        $this->assertSame(
            self::LIST_SELECT . self::FROM . self::LIST_JOINS
            . ' WHERE ipa.irhpApplication = :irhpId',
            $qb->getDQL(),
        );
        $this->assertSame(2, $qb->getParameter('irhpId')->getValue());
    }

    /**
     * A specific status wins over validOnly, which in turn narrows the default of all statuses.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('listByLicenceProvider')]
    public function testFetchListByLicence(?string $status, ?bool $validOnly, array $expectedStatuses): void
    {
        $qb = $this->createRealQb();
        $this->sut->expects('fetchPaginatedList')->andReturn(['RESULTS']);

        $query = GetListByLicence::create([
            'licence' => 7,
            'irhpPermitType' => IrhpPermitTypeEntity::IRHP_PERMIT_TYPE_ID_BILATERAL,
            'page' => 1,
            'limit' => 10,
            'status' => $status,
            'validOnly' => $validOnly,
        ]);

        $this->assertSame(['RESULTS'], $this->sut->fetchList($query));

        $this->assertSame(
            self::LIST_SELECT . self::FROM . self::LIST_JOINS
            . ' INNER JOIN ipa.irhpApplication ia INNER JOIN m.irhpPermitRange ipr'
            . ' INNER JOIN ipr.irhpPermitStock ips LEFT JOIN ips.country ipc'
            . ' WHERE ia.licence = :licenceId AND m.status IN(:statuses)'
            . ' AND ips.irhpPermitType = :irhpPermitTypeId'
            . ' ORDER BY ipc.countryDesc ASC, m.expiryDate ASC, ipa.id ASC, m.permitNumber ASC',
            $qb->getDQL(),
        );
        $this->assertSame($expectedStatuses, $qb->getParameter('statuses')->getValue());
    }

    public static function listByLicenceProvider(): \Iterator
    {
        yield 'valid only' => [null, true, Entity::$validStatuses];
        yield 'all' => [null, false, Entity::ALL_STATUSES];
        yield 'specific status' => [Entity::STATUS_PRINTING, null, [Entity::STATUS_PRINTING]];
    }

    public function testGetLivePermitCountsGroupedByStock(): void
    {
        $rows = [['irhpPermitStockId' => 7, 'irhpPermitCount' => 8]];

        $dbalResult = m::mock(Result::class);
        $dbalResult->expects('fetchAllAssociative')->andReturn($rows);

        $connection = m::mock(Connection::class);
        $connection->expects('executeQuery')
            ->with(
                m::type('string'),
                [
                    [
                        Entity::STATUS_PENDING,
                        Entity::STATUS_AWAITING_PRINTING,
                        Entity::STATUS_PRINTING,
                        Entity::STATUS_PRINTED,
                    ],
                    7,
                ],
                [ArrayParameterType::STRING, ParameterType::INTEGER],
            )
            ->andReturn($dbalResult);

        $this->em->expects('getConnection')->withNoArgs()->andReturn($connection);

        $this->assertSame($rows, $this->sut->getLivePermitCountsGroupedByStock(7));
    }

    public function testMarkAsExpired(): void
    {
        $query = m::mock();
        $query->expects('execute')->with([]);

        $this->dbQueryService->expects('get')->with(ExpireIrhpPermitsQuery::class)->andReturn($query);

        $this->sut->markAsExpired();
    }
}
