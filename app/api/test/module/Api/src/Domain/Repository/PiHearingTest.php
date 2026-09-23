<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Repository\PiHearing as Repo;
use Dvsa\Olcs\Api\Entity\Pi\PiHearing as Entity;
use Dvsa\Olcs\Transfer\Query\Cases\Pi\HearingList;
use Dvsa\Olcs\Transfer\Query\Cases\Pi\ReportList as ReportListQry;
use Mockery as m;

final class PiHearingTest extends RepositoryTestCase
{
    private const string FROM = ' FROM ' . Entity::class . ' m';

    private const string REPORT_SELECT = 'SELECT m, w0, p, c, l, o, lst, tm, tmst, tmhmcd, tmhmcdp, v, va';

    private const string REPORT_JOINS = ' LEFT JOIN m.presidedByRole w0 LEFT JOIN m.pi p'
        . ' LEFT JOIN p.case c LEFT JOIN c.licence l LEFT JOIN l.organisation o'
        . ' LEFT JOIN l.status lst LEFT JOIN c.transportManager tm LEFT JOIN tm.tmStatus tmst'
        . ' LEFT JOIN tm.homeCd tmhmcd LEFT JOIN tmhmcd.person tmhmcdp'
        . ' LEFT JOIN m.venue v LEFT JOIN v.address va';

    private const string DATE_WHERE = ' WHERE m.hearingDate >= :hearingDateFrom'
        . ' AND m.hearingDate <= :hearingDateTo';

    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(Repo::class, true);
    }

    /**
     * The previous hearing is the most recent adjourned one before the date given.
     */
    public function testFetchPreviousHearing(): void
    {
        $hearing = m::mock(Entity::class);

        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getResult')->with(Query::HYDRATE_OBJECT)->andReturn([$hearing]);

        $this->assertSame($hearing, $this->sut->fetchPreviousHearing(123, '2016-02-10'));

        $this->assertSame(
            'SELECT m' . self::FROM
            . ' WHERE m.hearingDate < :hearingDate AND m.pi = :pi AND m.isAdjourned = :isAdjourned'
            . ' ORDER BY m.hearingDate DESC',
            $qb->getDQL(),
        );
        $this->assertSame(123, $qb->getParameter('pi')->getValue());
        $this->assertSame(1, $qb->getParameter('isAdjourned')->getValue());
        $this->assertSame(1, $qb->getMaxResults());
    }

    public function testFetchPreviousHearingReturnsNullWhenThereIsNone(): void
    {
        $this->createRealQb()->stubbedQuery()->expects('getResult')->andReturn([]);

        $this->assertNull($this->sut->fetchPreviousHearing(123, '2016-02-10'));
    }

    public function testFetchList(): void
    {
        $qb = $this->createRealQb();
        $this->sut->expects('fetchPaginatedList')->andReturn(['RESULTS']);

        $this->assertSame(['RESULTS'], $this->sut->fetchList(HearingList::create(['pi' => 99])));

        $this->assertSame(
            'SELECT m, w0' . self::FROM . ' LEFT JOIN m.presidedByRole w0'
            . ' WHERE m.pi = :byPi',
            $qb->getDQL(),
        );
        $this->assertSame(99, $qb->getParameter('byPi')->getValue());
    }

    /**
     * The report query takes a different filter path: a date window over the whole day, plus an
     * optional traffic-area narrowing where 'other' means "no venue".
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('reportProvider')]
    public function testFetchListForReport(array $trafficAreas, string $expectedExtra): void
    {
        $qb = $this->createRealQb();
        $this->sut->expects('fetchPaginatedList')->andReturn(['RESULTS']);

        $query = ReportListQry::create([
            'startDate' => '2016-02-01',
            'endDate' => '2016-02-10',
            'trafficAreas' => $trafficAreas,
        ]);

        $this->assertSame(['RESULTS'], $this->sut->fetchList($query));

        $this->assertSame(
            self::REPORT_SELECT . self::FROM . self::REPORT_JOINS . self::DATE_WHERE . $expectedExtra,
            $qb->getDQL(),
        );
        $this->assertSame(
            '2016-02-01 00:00:00',
            $qb->getParameter('hearingDateFrom')->getValue()->format('Y-m-d H:i:s'),
        );
        $this->assertSame(
            '2016-02-10 23:59:59',
            $qb->getParameter('hearingDateTo')->getValue()->format('Y-m-d H:i:s'),
        );
    }

    public static function reportProvider(): \Iterator
    {
        yield 'no traffic areas' => [[], ''];
        // The traffic areas are inlined into the IN(), not bound.
        yield 'traffic areas' => [['B', 'C'], " AND v.trafficArea IN('B', 'C')"];
        // 'other' means hearings with no venue at all, ORed with the remaining areas.
        yield 'traffic areas including other' => [
            ['B', 'other'],
            " AND (m.venue IS NULL OR v.trafficArea IN('B'))",
        ];
    }
}
