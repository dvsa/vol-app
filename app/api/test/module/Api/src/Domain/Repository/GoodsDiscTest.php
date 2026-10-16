<?php

declare(strict_types=1);

/**
 * Goods Disc test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\ParameterType;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Repository\GoodsDisc as GoodsDiscRepo;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\Vehicle\GoodsDisc as Entity;
use Mockery as m;

/**
 * Goods Disc test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
final class GoodsDiscTest extends RepositoryTestCase
{
    private const string JOINS = ' LEFT JOIN gd.licenceVehicle lv LEFT JOIN lv.licence lvl'
        . ' LEFT JOIN lvl.goodsOrPsv lvlgp LEFT JOIN lvl.licenceType lvllt'
        . ' LEFT JOIN lvl.trafficArea lvlta LEFT JOIN lv.vehicle lvv'
        . ' LEFT JOIN lv.application lva LEFT JOIN lva.licenceType lvalt'
        . ' LEFT JOIN lva.goodsOrPsv lvagp';

    /** Applied on both branches, after the interim/non-interim alternation. */
    private const string COMMON_WHERE = ' AND gd.issuedDate IS NULL AND gd.ceasedDate IS NULL'
        . ' AND lv.removalDate IS NULL AND lvl.status IN(:activeStatuses)';

    private const array ACTIVE_STATUSES = [
        LicenceEntity::LICENCE_STATUS_UNDER_CONSIDERATION,
        LicenceEntity::LICENCE_STATUS_GRANTED,
        LicenceEntity::LICENCE_STATUS_VALID,
        LicenceEntity::LICENCE_STATUS_CURTAILED,
        LicenceEntity::LICENCE_STATUS_SUSPENDED,
    ];

    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(GoodsDiscRepo::class, true);
    }

    /**
     * NI licences are matched on traffic area and licence type only; the operator type check is
     * deliberately skipped. The ORDER BY matters: disc numbers are assigned from this order, so
     * an unstable one would print numbers that disagree with the database.
     */
    public function testFetchDiscsToPrintNi(): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getResult')->with(Query::HYDRATE_ARRAY)->andReturn(['result']);

        $this->assertSame(['result'], $this->sut->fetchDiscsToPrint('Y', 'ltyp_r', 1));

        $this->assertSame(
            'SELECT gd, lv, lvl, lvlgp, lvllt, lvlta, lvv, lva, lvalt, lvagp'
            . ' FROM ' . Entity::class . ' gd' . self::JOINS
            . ' WHERE ((lvlta.isNi = 1 AND gd.isInterim = 1 AND lvalt.id = :applicationLicenceType)'
            . ' OR (lvlta.isNi = 1 AND gd.isInterim = 0 AND lvllt.id = :licenceLicenceType))'
            . self::COMMON_WHERE
            . ' ORDER BY lvl.licNo ASC, gd.id ASC',
            $qb->getDQL(),
        );
        $this->assertSame('ltyp_r', $qb->getParameter('applicationLicenceType')->getValue());
        $this->assertSame('ltyp_r', $qb->getParameter('licenceLicenceType')->getValue());
        $this->assertSame(self::ACTIVE_STATUSES, $qb->getParameter('activeStatuses')->getValue());
        $this->assertSame(1, $qb->getMaxResults());
    }

    /**
     * Non-NI licences additionally check the operator type, against the application on the
     * interim branch and the licence on the non-interim one.
     */
    public function testFetchDiscsToPrint(): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getResult')->with(Query::HYDRATE_ARRAY)->andReturn(['result']);

        $this->assertSame(['result'], $this->sut->fetchDiscsToPrint('N', 'ltyp_r', 1));

        $this->assertSame(
            'SELECT gd, lv, lvl, lvlgp, lvllt, lvlta, lvv, lva, lvalt, lvagp'
            . ' FROM ' . Entity::class . ' gd' . self::JOINS
            . ' WHERE ((lvlta.isNi = 0 AND gd.isInterim = 1 AND lvagp.id = :operatorType'
            . ' AND lvalt.id = :applicationLicenceType)'
            . ' OR (lvlta.isNi = 0 AND gd.isInterim = 0 AND lvlgp.id = :operatorType1'
            . ' AND lvllt.id = :licenceLicenceType))'
            . self::COMMON_WHERE
            . ' ORDER BY lvl.licNo ASC, gd.id ASC',
            $qb->getDQL(),
        );
        $this->assertSame(
            LicenceEntity::LICENCE_CATEGORY_GOODS_VEHICLE,
            $qb->getParameter('operatorType')->getValue(),
        );
        $this->assertSame(
            LicenceEntity::LICENCE_CATEGORY_GOODS_VEHICLE,
            $qb->getParameter('operatorType1')->getValue(),
        );
    }

    /**
     * The 'min' variant shares the filters but joins with plain leftJoin() and selects only the
     * root, so it carries no ORDER BY and no eager-loaded associations.
     */
    public function testFetchDiscsToPrintMin(): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getResult')->with(Query::HYDRATE_ARRAY)->andReturn(['result']);

        $this->assertSame(['result'], $this->sut->fetchDiscsToPrintMin('N', 'ltyp_r'));

        $this->assertSame(
            'SELECT gd FROM ' . Entity::class . ' gd' . self::JOINS
            . ' WHERE ((lvlta.isNi = 0 AND gd.isInterim = 1 AND lvagp.id = :operatorType'
            . ' AND lvalt.id = :applicationLicenceType)'
            . ' OR (lvlta.isNi = 0 AND gd.isInterim = 0 AND lvlgp.id = :operatorType1'
            . ' AND lvllt.id = :licenceLicenceType))'
            . self::COMMON_WHERE,
            $qb->getDQL(),
        );
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('isPrintingProvider')]
    public function testSetIsPrinting(string $method, int $expectedFlag): void
    {
        $this->expectQueryWithData(
            'Discs\GoodsDiscsSetIsPrinting',
            ['isPrinting' => $expectedFlag, 'ids' => [1, 2]],
            ['isPrinting' => ParameterType::INTEGER, 'ids' => ArrayParameterType::INTEGER],
        );

        $this->sut->{$method}([1, 2]);
    }

    public static function isPrintingProvider(): \Iterator
    {
        yield 'on' => ['setIsPrintingOn', 1];
        yield 'off' => ['setIsPrintingOff', 0];
    }

    /**
     * Disc numbers are assigned in the order given, so each id gets the next number up.
     */
    public function testSetIsPrintingOffAndAssignNumbers(): void
    {
        $query = m::mock();
        $query->expects('execute')->with(['id' => 1, 'discNo' => 634]);
        $query->expects('execute')->with(['id' => 32, 'discNo' => 635]);
        $query->expects('execute')->with(['id' => 4, 'discNo' => 636]);

        $this->dbQueryService->shouldReceive('get')
            ->with('Discs\GoodsDiscsSetIsPrintingOffAndDiscNo')
            ->andReturn($query);

        $this->sut->setIsPrintingOffAndAssignNumbers([1, 32, 4], 634);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('rowCountQueryProvider')]
    public function testRowCountQueries(string $method, mixed $arg, string $queryName, array $data): void
    {
        $statement = m::mock();
        $statement->expects('rowCount')->withNoArgs()->andReturn(564);
        $this->expectQueryWithData($queryName, $data, [], $statement);

        $this->assertSame(564, $this->sut->{$method}($arg));
    }

    public static function rowCountQueryProvider(): \Iterator
    {
        yield 'cease for licence' => [
            'ceaseDiscsForLicence',
            123,
            'LicenceVehicle\CeaseDiscsForLicence',
            ['licence' => 123],
        ];
        yield 'cease for licence vehicle' => [
            'ceaseDiscsForLicenceVehicle',
            123,
            'LicenceVehicle\CeaseDiscsForLicenceVehicle',
            ['licenceVehicle' => 123],
        ];
        yield 'cease for application' => [
            'ceaseDiscsForApplication',
            45,
            'LicenceVehicle\CeaseDiscsForApplication',
            ['application' => 45],
        ];
        yield 'create for licence' => [
            'createDiscsForLicence',
            1502,
            'LicenceVehicle\CreateDiscsForLicence',
            ['licence' => 1502],
        ];
    }

    public function testUpdateExistingGoodsDiscs(): void
    {
        $application = m::mock(Application::class);
        $application->shouldReceive('getId')->andReturn(1102);
        $application->shouldReceive('getLicence->getId')->andReturn(321);

        $this->expectQueryWithData('Discs\CeaseGoodsDiscsForApplication', ['application' => 1102, 'licence' => 321]);
        $this->expectQueryWithData(
            'Discs\CreateGoodsDiscs',
            ['application' => 1102, 'licence' => 321, 'isCopy' => 0],
        );

        $this->sut->updateExistingGoodsDiscs($application);
    }

    public function testCountForLicence(): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getSingleScalarResult')->andReturn(1);

        $this->assertSame(['discCount' => 1], $this->sut->countForLicence(1));

        $this->assertSame(
            'SELECT count(gd) FROM ' . Entity::class . ' gd'
            . ' INNER JOIN gd.licenceVehicle lv'
            . ' WHERE lv.licence = :id AND gd.ceasedDate IS NULL',
            $qb->getDQL(),
        );
        $this->assertSame(1, $qb->getParameter('id')->getValue());
        $this->assertSame(1, $qb->getMaxResults());
    }

    public function testCountForLicenceNoResult(): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getSingleScalarResult')->andThrow(new NoResultException());

        $this->assertSame(['discCount' => 0], $this->sut->countForLicence(1));
    }

    public function testCountForLicenceRethrowsOtherExceptions(): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getSingleScalarResult')->andThrow(new \RuntimeException('boom'));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('boom');

        $this->sut->countForLicence(1);
    }
}
