<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\ParameterType;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Repository\PsvDisc as Repo;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\Licence\PsvDisc as Entity;
use Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea as TrafficAreaEntity;
use Dvsa\Olcs\Transfer\Query\Licence\PsvDiscs;
use Mockery as m;

final class PsvDiscTest extends RepositoryTestCase
{
    private const string FROM = ' FROM ' . Entity::class . ' psv';

    private const string LICENCE_JOINS = ' psv.licence l LEFT JOIN l.trafficArea lta'
        . ' LEFT JOIN l.licenceType llt LEFT JOIN l.goodsOrPsv lgp';

    /**
     * PSV discs are printed for GB licences only, hence both the isNi check and the explicit
     * exclusion of the NI traffic area.
     */
    private const string FILTER_WHERE = ' WHERE (lta.isNi = 0 AND llt.id = :licenceType'
        . ' AND lta.id <> :licenceTrafficAreaId AND lgp.id = :goodsOrPsv)'
        . ' AND psv.issuedDate IS NULL AND psv.ceasedDate IS NULL'
        . ' AND l.status IN(:activeStatuses)';

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
        $this->setUpRealSut(Repo::class, true);
    }

    public function testFetchDiscsToPrint(): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getResult')->with(Query::HYDRATE_ARRAY)->andReturn(['RESULTS']);

        $this->assertSame(['RESULTS'], $this->sut->fetchDiscsToPrint('ltyp_sn', 10));

        $this->assertSame(
            'SELECT psv, l, lta, llt, lgp' . self::FROM . ' LEFT JOIN' . self::LICENCE_JOINS
            . self::FILTER_WHERE
            . ' ORDER BY l.licNo ASC',
            $qb->getDQL(),
        );
        $this->assertSame('ltyp_sn', $qb->getParameter('licenceType')->getValue());
        $this->assertSame(
            TrafficAreaEntity::NORTHERN_IRELAND_TRAFFIC_AREA_CODE,
            $qb->getParameter('licenceTrafficAreaId')->getValue(),
        );
        $this->assertSame(
            LicenceEntity::LICENCE_CATEGORY_PSV,
            $qb->getParameter('goodsOrPsv')->getValue(),
        );
        $this->assertSame(self::ACTIVE_STATUSES, $qb->getParameter('activeStatuses')->getValue());
        $this->assertSame(10, $qb->getMaxResults());
    }

    /**
     * The 'min' variant shares the filters but joins directly and selects only the root, so it
     * carries neither the eager-loaded associations nor the ordering.
     */
    public function testFetchDiscsToPrintMin(): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getResult')->with(Query::HYDRATE_ARRAY)->andReturn(['RESULTS']);

        $this->assertSame(['RESULTS'], $this->sut->fetchDiscsToPrintMin('ltyp_sn'));

        $this->assertSame(
            'SELECT psv' . self::FROM . ' LEFT JOIN' . self::LICENCE_JOINS . self::FILTER_WHERE,
            $qb->getDQL(),
        );
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('isPrintingProvider')]
    public function testSetIsPrinting(string $method, int $expectedFlag): void
    {
        $this->expectQueryWithData(
            'Discs\PsvDiscsSetIsPrinting',
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

    public function testSetIsPrintingOffAndAssignNumbers(): void
    {
        $query = m::mock();
        $query->expects('execute')->with(['id' => 1, 'discNo' => 634]);
        $query->expects('execute')->with(['id' => 32, 'discNo' => 635]);

        $this->dbQueryService->shouldReceive('get')
            ->with('Discs\PsvDiscsSetIsPrintingOffAndDiscNo')
            ->andReturn($query);

        $this->sut->setIsPrintingOffAndAssignNumbers([1, 32], 634);
    }

    /**
     * Disc numbers are stored as text, so the list is ordered by a numeric cast rather than the
     * column itself.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('includeCeasedProvider')]
    public function testApplyListFilters(bool $includeCeased, string $expectedExtra): void
    {
        $qb = $this->createRealQb();

        $query = PsvDiscs::create(['id' => 7, 'includeCeased' => $includeCeased]);

        $this->sut->applyListFilters($qb, $query);

        $this->assertSame(
            'SELECT psv, psv.discNo+0 as HIDDEN intDiscNo' . self::FROM
            . ' WHERE' . $expectedExtra . ' psv.licence = :licence'
            . ' ORDER BY intDiscNo ASC',
            $qb->getDQL(),
        );
        $this->assertSame(7, $qb->getParameter('licence')->getValue());
    }

    public static function includeCeasedProvider(): \Iterator
    {
        yield 'excluding ceased' => [false, ' psv.ceasedDate IS NULL AND'];
        yield 'including ceased' => [true, ''];
    }

    public function testCeaseDiscsForLicence(): void
    {
        $statement = m::mock();
        $statement->expects('rowCount')->withNoArgs()->andReturn(5);
        $this->expectQueryWithData('Discs\CeaseDiscsForLicence', ['licence' => 7], [], $statement);

        $this->assertSame(5, $this->sut->ceaseDiscsForLicence(7));
    }

    public function testCreatePsvDiscs(): void
    {
        $query = m::mock();
        $query->expects('executeInsert')->with(7, 3, false)->andReturn('RESULT');

        $this->dbQueryService->expects('get')->with('Discs\CreatePsvDiscs')->andReturn($query);

        $this->assertSame('RESULT', $this->sut->createPsvDiscs(7, 3));
    }

    public function testCountForLicence(): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getSingleScalarResult')->andReturn(4);

        $this->assertSame(['discCount' => 4], $this->sut->countForLicence(7));

        $this->assertSame(
            'SELECT count(psv)' . self::FROM
            . ' WHERE psv.licence = :id AND psv.ceasedDate IS NULL'
            . ' GROUP BY psv.licence',
            $qb->getDQL(),
        );
        $this->assertSame(1, $qb->getMaxResults());
    }

    public function testCountForLicenceNoResult(): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getSingleScalarResult')->andThrow(new NoResultException());

        $this->assertSame(['discCount' => 0], $this->sut->countForLicence(7));
    }

    public function testCountForLicenceRethrowsOtherExceptions(): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getSingleScalarResult')->andThrow(new \RuntimeException('boom'));

        $this->expectException(\RuntimeException::class);

        $this->sut->countForLicence(7);
    }
}
