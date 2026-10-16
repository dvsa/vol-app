<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Repository\ConditionUndertaking as Repo;
use Dvsa\Olcs\Api\Entity\Cases\ConditionUndertaking as Entity;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Mockery as m;

final class ConditionUndertakingTest extends RepositoryTestCase
{
    private const string FROM = ' FROM ' . Entity::class . ' m';

    /** withRefdata() joins conditionType, conditionCategory, addedVia and attachedTo. */
    private const string REFDATA_JOINS = ' LEFT JOIN m.conditionType w0'
        . ' LEFT JOIN m.conditionCategory w1 LEFT JOIN m.addedVia w2'
        . ' LEFT JOIN m.attachedTo w3';

    /** The joins shared by the application, variation and licence lists (no withRefdata). */
    private const string LIST_JOINS = ' LEFT JOIN m.attachedTo w0 LEFT JOIN m.conditionType w1'
        . ' LEFT JOIN m.operatingCentre oc LEFT JOIN oc.address add'
        . ' LEFT JOIN add.countryCode w2';

    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(Repo::class, true);
    }

    public function testBuildDefaultQuery(): void
    {
        $qb = $this->createRealQb();

        $this->sut->buildDefaultQuery($qb, 1);

        $this->assertSame(
            'SELECT m, w0, w1, w2, w3, oc, w4' . self::FROM . self::REFDATA_JOINS
            . ' LEFT JOIN m.operatingCentre oc LEFT JOIN oc.address w4'
            . ' WHERE m.id = :byId',
            $qb->getDQL(),
        );
    }

    /**
     * Only live conditions are shown read-only: drafts and fulfilled ones are excluded.
     *
     * This query joins m.attachedTo and m.conditionType twice each — withRefdata() covers both
     * and the method then asks for them explicitly. See the migration findings.
     */
    public function testFetchListForLicenceReadOnly(): void
    {
        $qb = $this->createRealQb()->willReturn(['RESULTS']);

        $this->assertSame(['RESULTS'], $this->sut->fetchListForLicenceReadOnly(7));

        $this->assertSame(
            'SELECT m, w0, w1, w2, w3, w4, w5, oc, w6' . self::FROM . self::REFDATA_JOINS
            . ' LEFT JOIN m.attachedTo w4 LEFT JOIN m.conditionType w5'
            . ' LEFT JOIN m.operatingCentre oc LEFT JOIN oc.address w6'
            . ' WHERE m.licence = :licence AND m.isDraft = 0 AND m.isFulfilled = 0',
            $qb->getDQL(),
        );
        $this->assertSame(7, $qb->getParameter('licence')->getValue());
    }

    public function testApplyListFilters(): void
    {
        $qb = $this->createRealQb();

        $query = m::mock(QueryInterface::class);
        $query->shouldReceive('getCase')->andReturn(1);

        $this->sut->applyListFilters($qb, $query);

        $this->assertSame(
            'SELECT m' . self::FROM . ' WHERE m.case = :byCase',
            $qb->getDQL(),
        );
        $this->assertSame(1, $qb->getParameter('byCase')->getValue());
    }

    public function testApplyListJoins(): void
    {
        $qb = $this->createRealQb();

        $this->sut->applyListJoins($qb);

        $this->assertSame(
            'SELECT m, w0, w1, w2, w3, oc, w4, w5, w6' . self::FROM . self::REFDATA_JOINS
            . ' LEFT JOIN m.operatingCentre oc LEFT JOIN oc.address w4'
            . ' LEFT JOIN m.createdBy w5 LEFT JOIN m.lastModifiedBy w6',
            $qb->getDQL(),
        );
    }

    public function testFetchListForApplication(): void
    {
        $qb = $this->createRealQb()->willReturn(['RESULTS']);

        $this->assertSame(['RESULTS'], $this->sut->fetchListForApplication(1));

        $this->assertSame(
            'SELECT m, w0, w1, oc, add, w2, w3' . self::FROM . self::LIST_JOINS
            . ' LEFT JOIN m.addedVia w3'
            . ' WHERE m.application = :application',
            $qb->getDQL(),
        );
        $this->assertSame(1, $qb->getParameter('application')->getValue());
    }

    /**
     * A variation returns conditions attached to either the application or the licence, so the
     * licence clause is an orWhere.
     */
    public function testFetchListForVariation(): void
    {
        $qb = $this->createRealQb()->willReturn(['RESULTS']);

        $this->assertSame(['RESULTS'], $this->sut->fetchListForVariation(1, 7));

        $this->assertSame(
            'SELECT m, w0, w1, oc, add, w2, w3, w4' . self::FROM . self::LIST_JOINS
            . ' LEFT JOIN m.licConditionVariation w3 LEFT JOIN m.addedVia w4'
            . ' WHERE m.application = :application OR m.licence = :licence'
            . ' ORDER BY m.id ASC',
            $qb->getDQL(),
        );
        $this->assertSame(1, $qb->getParameter('application')->getValue());
        $this->assertSame(7, $qb->getParameter('licence')->getValue());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('conditionTypeProvider')]
    public function testFetchListForLicence(?string $conditionType, string $expectedExtra): void
    {
        $qb = $this->createRealQb()->willReturn(['RESULTS']);

        $this->assertSame(['RESULTS'], $this->sut->fetchListForLicence(7, $conditionType));

        $this->assertSame(
            'SELECT m, w0, w1, oc, add, w2, w3' . self::FROM . self::LIST_JOINS
            . ' LEFT JOIN m.addedVia w3'
            . ' WHERE m.licence = :licence' . $expectedExtra,
            $qb->getDQL(),
        );
    }

    public static function conditionTypeProvider(): \Iterator
    {
        yield 'any type' => [null, ''];
        yield 'one type' => [Entity::TYPE_UNDERTAKING, ' AND m.conditionType = :conditionType'];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('singleFilterProvider')]
    public function testFetchByColumn(string $method, string $expectedWhere, string $parameter): void
    {
        $qb = $this->createRealQb()->willReturn(['RESULTS']);

        $this->assertSame(['RESULTS'], $this->sut->{$method}(1));

        $this->assertSame('SELECT m' . self::FROM . ' WHERE ' . $expectedWhere, $qb->getDQL());
        $this->assertSame(1, $qb->getParameter($parameter)->getValue());
    }

    public static function singleFilterProvider(): \Iterator
    {
        yield 'by s4' => ['fetchListForS4', 'm.s4 = :s4Id', 's4Id'];
        yield 'by licence condition variation' => [
            'fetchListForLicConditionVariation',
            'm.licConditionVariation = :id',
            'id',
        ];
    }

    /**
     * Small-vehicle undertakings are identified by a substring of the free-text notes.
     */
    public function testFetchSmallVehicleUndertakings(): void
    {
        $qb = $this->createRealQb()->willReturn(['RESULTS']);

        $this->assertSame(['RESULTS'], $this->sut->fetchSmallVehilceUndertakings(7));

        $this->assertSame(
            'SELECT m' . self::FROM
            . ' WHERE m.licence = :licence AND m.conditionType = :conditionType'
            . ' AND m.notes LIKE :note',
            $qb->getDQL(),
        );
        $this->assertSame(Entity::TYPE_UNDERTAKING, $qb->getParameter('conditionType')->getValue());
        $this->assertSame(
            '%' . Entity::SMALL_VEHICLE_UNDERTAKINGS . '%',
            $qb->getParameter('note')->getValue(),
        );
    }

    /**
     * The light-goods variant matches the note exactly rather than by substring.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('countProvider')]
    public function testHasLightGoodsVehicleUndertakings(int $count, bool $expected): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getSingleScalarResult')->andReturn($count);

        $this->assertSame($expected, $this->sut->hasLightGoodsVehicleUndertakings(7));

        $this->assertSame(
            'SELECT count(m.id)' . self::FROM
            . ' WHERE m.licence = :licence AND m.conditionType = :conditionType'
            . ' AND m.notes = :note',
            $qb->getDQL(),
        );
        $this->assertSame(
            Entity::LIGHT_GOODS_VEHICLE_UNDERTAKINGS,
            $qb->getParameter('note')->getValue(),
        );
    }

    public static function countProvider(): \Iterator
    {
        yield 'none' => [0, false];
        yield 'some' => [2, true];
    }

    public function testDeleteFromVariations(): void
    {
        $first = m::mock(Entity::class);
        $second = m::mock(Entity::class);

        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getResult')->with(Query::HYDRATE_OBJECT)->andReturn([$first, $second]);

        $this->sut->expects('delete')->with($first);
        $this->sut->expects('delete')->with($second);

        $this->assertSame(2, $this->sut->deleteFromVariations([1, 2]));

        $this->assertSame(
            'SELECT m' . self::FROM . ' WHERE m.licConditionVariation IN(:CU_IDS)',
            $qb->getDQL(),
        );
        $this->assertSame([1, 2], $qb->getParameter('CU_IDS')->getValue());
    }
}
