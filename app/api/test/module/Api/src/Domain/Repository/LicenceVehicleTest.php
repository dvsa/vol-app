<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Repository\LicenceVehicle as Repo;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\Licence\LicenceVehicle as Entity;
use Dvsa\Olcs\Transfer\Query\Application\GoodsVehicles as AppGoodsVehicles;
use Dvsa\Olcs\Transfer\Query\Licence\GoodsVehicles as LicGoodsVehicles;
use Dvsa\Olcs\Transfer\Query\Variation\GoodsVehicles as VarGoodsVehicles;
use Mockery as m;

final class LicenceVehicleTest extends RepositoryTestCase
{
    private const string FROM = ' FROM ' . Entity::class . ' m';

    private const string LVA_JOINS = ' INNER JOIN m.vehicle v LEFT JOIN m.interimApplication in';

    private const array ACTIVE_LICENCE_STATUSES = [
        LicenceEntity::LICENCE_STATUS_CURTAILED,
        LicenceEntity::LICENCE_STATUS_VALID,
        LicenceEntity::LICENCE_STATUS_SUSPENDED,
    ];

    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(Repo::class, true);
    }

    /**
     * A specified-only application list widens to the licence's vehicles too, since a specified
     * vehicle already belongs to the licence rather than the application.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('applicationQueryProvider')]
    public function testCreatePaginatedVehiclesDataForApplicationQuery(
        array $data,
        string $expectedFilters,
        string $expectedOwnership,
    ): void {
        $qb = $this->createRealQb();

        $this->assertSame(
            $qb,
            $this->sut->createPaginatedVehiclesDataForApplicationQuery(
                AppGoodsVehicles::create($data + ['page' => 3, 'limit' => 10]),
                1,
                7,
            ),
        );

        $this->assertSame(
            'SELECT m' . self::FROM . self::LVA_JOINS . $expectedFilters . $expectedOwnership,
            $qb->getDQL(),
        );
    }

    public static function applicationQueryProvider(): \Iterator
    {
        yield 'specified, with a disc, not removed' => [
            ['disc' => 'Y', 'vrm' => 'A', 'includeRemoved' => false, 'specified' => 'Y'],
            ' INNER JOIN m.goodsDiscs gd'
            . ' WHERE gd.ceasedDate IS NULL AND gd.issuedDate IS NOT NULL'
            . ' AND v.vrm LIKE :vrm AND m.removalDate IS NULL AND m.specifiedDate IS NOT NULL',
            ' AND (m.application = :application OR m.licence = :licence)',
        ];

        yield 'unspecified, without a disc, including removed' => [
            ['disc' => 'N', 'includeRemoved' => true, 'specified' => 'N'],
            " LEFT JOIN m.goodsDiscs gd WITH gd.ceasedDate IS NULL AND gd.issuedDate IS NOT NULL"
            . ' WHERE gd.id IS NULL AND m.specifiedDate IS NULL',
            ' AND m.application = :application',
        ];
    }

    /**
     * A variation only ever shows its own vehicles plus the licence's specified ones.
     */
    public function testCreatePaginatedVehiclesDataForVariationQuery(): void
    {
        $qb = $this->createRealQb();

        $this->sut->createPaginatedVehiclesDataForVariationQuery(
            VarGoodsVehicles::create(['page' => 1, 'limit' => 10, 'includeRemoved' => true]),
            1,
            7,
        );

        $this->assertSame(
            // The orX is the only predicate here, so Doctrine leaves it unbracketed at the
            // top level; adding any further andWhere() would bracket it.
            'SELECT m' . self::FROM . self::LVA_JOINS
            . ' WHERE m.application = :application'
            . ' OR (m.licence = :licence AND m.specifiedDate IS NOT NULL)',
            $qb->getDQL(),
        );
    }

    /**
     * The licence list never filters by specified date — it forces specified-only instead.
     */
    public function testCreatePaginatedVehiclesDataForLicenceQuery(): void
    {
        $qb = $this->createRealQb();

        $this->sut->createPaginatedVehiclesDataForLicenceQuery(
            LicGoodsVehicles::create(['page' => 1, 'limit' => 10, 'includeRemoved' => true]),
            7,
        );

        $this->assertSame(
            'SELECT m' . self::FROM . self::LVA_JOINS
            . ' WHERE m.specifiedDate IS NOT NULL AND m.licence = :licence',
            $qb->getDQL(),
        );
    }

    public function testCreatePaginatedVehiclesDataForLicenceQueryPsv(): void
    {
        $qb = $this->createRealQb();

        $this->sut->createPaginatedVehiclesDataForLicenceQueryPsv(
            LicGoodsVehicles::create(['page' => 1, 'limit' => 10, 'includeRemoved' => true]),
            7,
        );

        // The PSV variant joins the vehicle only — no interim application, no disc filtering.
        $this->assertSame(
            'SELECT m' . self::FROM . ' INNER JOIN m.vehicle v'
            . ' WHERE m.specifiedDate IS NOT NULL AND m.licence = :licence',
            $qb->getDQL(),
        );
    }

    public function testCreatePaginatedVehiclesDataForApplicationQueryPsv(): void
    {
        $qb = $this->createRealQb();

        $this->sut->createPaginatedVehiclesDataForApplicationQueryPsv(
            AppGoodsVehicles::create(['page' => 1, 'limit' => 10, 'includeRemoved' => true]),
            1,
            7,
        );

        $this->assertSame(
            'SELECT m' . self::FROM . ' INNER JOIN m.vehicle v'
            . ' WHERE m.licence = :licence'
            . ' AND (m.application = :application OR m.specifiedDate IS NOT NULL)',
            $qb->getDQL(),
        );
    }

    public function testCreatePaginatedVehiclesDataForUnlicensedOperatorQuery(): void
    {
        $qb = $this->createRealQb();

        $this->sut->createPaginatedVehiclesDataForUnlicensedOperatorQuery(
            LicGoodsVehicles::create(['page' => 1, 'limit' => 10]),
            7,
        );

        $this->assertSame(
            'SELECT m' . self::FROM . ' INNER JOIN m.vehicle v'
            . ' WHERE m.licence = :licence'
            . ' ORDER BY m.createdOn ASC',
            $qb->getDQL(),
        );
    }

    /**
     * A duplicate is the same VRM specified and not removed on a different, active goods licence.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('warningSeedProvider')]
    public function testFetchDuplicates(bool $checkWarningSeedDate, string $expectedExtra): void
    {
        $licence = m::mock(LicenceEntity::class);
        $licence->shouldReceive('getId')->andReturn(7);

        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getResult')->with(Query::HYDRATE_OBJECT)->andReturn(['RESULTS']);

        $this->assertSame(
            ['RESULTS'],
            $this->sut->fetchDuplicates($licence, 'ABC123', $checkWarningSeedDate),
        );

        $this->assertSame(
            'SELECT m' . self::FROM . ' INNER JOIN m.vehicle v INNER JOIN m.licence l'
            . ' WHERE v.vrm = :vrm AND m.specifiedDate IS NOT NULL AND m.removalDate IS NULL'
            . ' AND l.id <> :licence AND l.goodsOrPsv = :goods'
            . " AND l.status IN('" . implode("', '", self::ACTIVE_LICENCE_STATUSES) . "')"
            . $expectedExtra,
            $qb->getDQL(),
        );
        $this->assertSame('ABC123', $qb->getParameter('vrm')->getValue());
        $this->assertSame(7, $qb->getParameter('licence')->getValue());
    }

    public static function warningSeedProvider(): \Iterator
    {
        yield 'checking the seed date' => [true, ' AND m.warningLetterSeedDate IS NULL'];
        yield 'ignoring the seed date' => [false, ''];
    }

    /**
     * A warning goes out 28 days after the seed date, once, and only while the vehicle is still
     * on an active licence.
     */
    public function testFetchQueuedForWarning(): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getResult')->with(Query::HYDRATE_OBJECT)->andReturn(['RESULTS']);

        $this->assertSame(['RESULTS'], $this->sut->fetchQueuedForWarning());

        $this->assertSame(
            'SELECT m' . self::FROM . ' INNER JOIN m.licence l'
            . " WHERE l.status IN('" . implode("', '", self::ACTIVE_LICENCE_STATUSES) . "')"
            . ' AND m.warningLetterSeedDate < :seedDate'
            . ' AND m.warningLetterSentDate IS NULL AND m.removalDate IS NULL',
            $qb->getDQL(),
        );
        $this->assertSame(
            new \DateTime()->modify('-28 days')->format('Y-m-d'),
            $qb->getParameter('seedDate')->getValue()->format('Y-m-d'),
        );
    }

    /**
     * Removal follows 15 days after the warning letter.
     */
    public function testFetchForRemoval(): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getResult')->with(Query::HYDRATE_OBJECT)->andReturn(['RESULTS']);

        $this->assertSame(['RESULTS'], $this->sut->fetchForRemoval());

        $this->assertSame(
            'SELECT m' . self::FROM . ' INNER JOIN m.licence l INNER JOIN m.vehicle v'
            . " WHERE l.status IN('" . implode("', '", self::ACTIVE_LICENCE_STATUSES) . "')"
            . ' AND m.warningLetterSentDate < :sentDate AND m.removalDate IS NULL',
            $qb->getDQL(),
        );
        $this->assertSame(
            new \DateTime()->modify('-15 days')->format('Y-m-d'),
            $qb->getParameter('sentDate')->getValue()->format('Y-m-d'),
        );
    }

    public function testFetchByVehicleId(): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getResult')->with(Query::HYDRATE_ARRAY)->andReturn(['RESULTS']);

        $this->assertSame(['RESULTS'], $this->sut->fetchByVehicleId(1));

        $this->assertSame(
            'SELECT m, v, l' . self::FROM . ' LEFT JOIN m.vehicle v LEFT JOIN m.licence l'
            . ' WHERE m.vehicle = :vehicle'
            . ' ORDER BY m.specifiedDate DESC',
            $qb->getDQL(),
        );
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('countProvider')]
    public function testVehicleCounts(string $method, string $expectedWhere): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getSingleScalarResult')->andReturn('4');

        $this->assertSame(4, $this->sut->{$method}(7));

        $this->assertSame(
            'SELECT count(m.id)' . self::FROM . ' WHERE ' . $expectedWhere,
            $qb->getDQL(),
        );
        $this->assertSame(7, $qb->getParameter('licence')->getValue());
    }

    public static function countProvider(): \Iterator
    {
        yield 'all vehicles' => ['fetchAllVehiclesCount', 'm.licence = :licence'];
        yield 'active only' => [
            'fetchActiveVehicleCount',
            'm.licence = :licence AND m.removalDate IS NULL',
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('psvVehiclesProvider')]
    public function testFetchPsvVehiclesByLicenceId(bool $includeRemoved, string $expectedExtra): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getResult')->with(Query::HYDRATE_ARRAY)->andReturn(['RESULTS']);

        $this->assertSame(['RESULTS'], $this->sut->fetchPsvVehiclesByLicenceId(7, $includeRemoved));

        $this->assertSame(
            'SELECT v.vrm, v.makeModel, m.specifiedDate, m.removalDate' . self::FROM
            . ' INNER JOIN m.vehicle v'
            . ' WHERE m.specifiedDate IS NOT NULL AND m.licence = :licence' . $expectedExtra
            . ' ORDER BY m.specifiedDate ASC',
            $qb->getDQL(),
        );
    }

    public static function psvVehiclesProvider(): \Iterator
    {
        yield 'excluding removed' => [false, ' AND m.removalDate IS NULL'];
        yield 'including removed' => [true, ''];
    }

    /**
     * The export joins the most recent goods disc per vehicle via a correlated sub-select in the
     * join condition.
     */
    public function testFetchForExport(): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('toIterable')->andReturn(['RESULTS']);

        // The sub-select is built off the EntityManager directly.
        $this->em->expects('createQueryBuilder')->withNoArgs()->andReturn($this->newRealQb());

        $this->assertSame(['RESULTS'], $this->sut->fetchForExport($qb));

        $this->assertStringContainsString(
            'LEFT JOIN Dvsa\Olcs\Api\Entity\Vehicle\GoodsDisc gd2 WITH gd2.id ='
            . ' (SELECT MAX(gds.id) as maxId FROM Dvsa\Olcs\Api\Entity\Vehicle\GoodsDisc gds'
            . ' WHERE gds.licenceVehicle = m.id)',
            $qb->getDQL(),
        );
    }

    /**
     * PSV vehicles are filtered from an already-loaded collection with a Criteria, not DQL.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('psvCollectionProvider')]
    public function testGetAllPsvVehicles(bool $forApplication, bool $includeRemoved, int $expectedCount): void
    {
        $specified = m::mock(Entity::class)->makePartial();
        $specified->setSpecifiedDate(new \DateTime('2019-01-01'));

        $removed = m::mock(Entity::class)->makePartial();
        $removed->setSpecifiedDate(new \DateTime('2019-01-01'));
        $removed->setRemovalDate(new \DateTime('2019-02-01'));

        $licence = m::mock(LicenceEntity::class)->makePartial();
        $licence->setLicenceVehicles(new ArrayCollection([$specified, $removed]));

        $entity = $licence;
        if ($forApplication) {
            $entity = m::mock(ApplicationEntity::class)->makePartial();
            $entity->shouldReceive('getLicence')->andReturn($licence);
        }

        $this->assertCount(
            $expectedCount,
            $this->sut->getAllPsvVehicles($entity, $includeRemoved),
        );
    }

    public static function psvCollectionProvider(): \Iterator
    {
        yield 'licence, excluding removed' => [false, false, 1];
        yield 'licence, including removed' => [false, true, 2];
        yield 'application, excluding removed' => [true, false, 1];
        yield 'application, including removed' => [true, true, 2];
    }

    public function testMarkDuplicateVehiclesForApplication(): void
    {
        $vehicle = m::mock();
        $vehicle->shouldReceive('getVehicle->getVrm')->andReturn('ABC123');

        $application = m::mock(ApplicationEntity::class);
        $application->shouldReceive('getLicenceVehicles')->andReturn([$vehicle]);
        $application->shouldReceive('getLicence->getId')->andReturn(7);

        $statement = m::mock();
        $statement->expects('rowCount')->andReturn(2);
        $this->expectQueryWithData(
            'LicenceVehicle\MarkDuplicateVrmsForLicence',
            ['vrms' => ['ABC123'], 'licence' => 7],
            [],
            $statement,
        );

        $this->assertSame(2, $this->sut->markDuplicateVehiclesForApplication($application));
    }

    public function testClearSpecifiedDateAndInterimApp(): void
    {
        $application = m::mock(ApplicationEntity::class);
        $application->shouldReceive('getId')->andReturn(1);
        $application->shouldReceive('getLicence->getId')->andReturn(7);

        $this->expectQueryWithData(
            'LicenceVehicle\ClearSpecifiedDateAndInterimAppForLicence',
            ['application' => 1, 'licence' => 7],
        );

        $this->sut->clearSpecifiedDateAndInterimApp($application);
    }

    public function testRemoveAllForLicence(): void
    {
        $this->expectQueryWithData('LicenceVehicle\RemoveAllForLicence', ['licence' => 7]);

        $this->sut->removeAllForLicence(7);
    }

    public function testClearVehicleSection26(): void
    {
        $statement = m::mock();
        $statement->expects('rowCount')->andReturn(3);
        $this->expectQueryWithData(
            'LicenceVehicle\ClearVehicleSection26',
            ['licence' => 7],
            [],
            $statement,
        );

        $this->assertSame(3, $this->sut->clearVehicleSection26(7));
    }
}
