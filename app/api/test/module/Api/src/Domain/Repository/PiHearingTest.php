<?php

declare(strict_types=1);

/**
 * PiHearing Repo test
 */

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Mockery as m;
use Dvsa\Olcs\Api\Domain\Repository\PiHearing as Repo;
use Dvsa\Olcs\Transfer\Query\Cases\Pi\HearingList as HearingListQry;
use Dvsa\Olcs\Transfer\Query\Cases\Pi\ReportList as ReportListQry;

/**
 * PiHearing Repo test
 */
class PiHearingTest extends RepositoryTestCase
{
    public function setUp(): void
    {
        $this->setUpSut(Repo::class);
    }

    public function testFetchPreviousHearing(): void
    {
        $piId = 123;
        $hearingDate = new \DateTime('2016-02-10');

        $qb = $this->createMockQb('BLAH');

        $this->mockCreateQueryBuilder($qb);
        $this->queryBuilder->shouldReceive('modifyQuery')->with($qb)->andReturnSelf();

        $qb->shouldReceive('getQuery')->andReturn(
            m::mock()->shouldReceive('execute')
                ->shouldReceive('getResult')
                ->andReturn(['RESULT'])
                ->getMock()
        );
        $this->assertEquals('RESULT', $this->sut->fetchPreviousHearing($piId, $hearingDate));

        $expectedQuery = 'BLAH '
            . 'AND m.hearingDate < [[2016-02-10T00:00:00+00:00]] '
            . 'AND m.pi = [[123]] '
            . 'AND m.isAdjourned = [[1]] '
            . 'ORDER BY m.hearingDate DESC '
            . 'LIMIT 1';
        $this->assertEquals($expectedQuery, $this->query);
    }

    public function testFetchList(): void
    {
        $piId = 123;

        $this->setUpSut(Repo::class, true);
        $this->sut->shouldReceive('fetchPaginatedList')->andReturn(['RESULTS']);

        $qb = $this->createMockQb('BLAH');
        $this->mockCreateQueryBuilder($qb);

        $this->queryBuilder
            ->shouldReceive('modifyQuery')->with($qb)->andReturnSelf()
            ->shouldReceive('withRefdata')->once()->andReturnSelf()
            ->shouldReceive('paginate')->once()->andReturnSelf();

        $query = HearingListQry::create(['pi' => $piId]);
        $this->assertEquals(['RESULTS'], $this->sut->fetchList($query));

        $expectedQuery = 'BLAH AND m.pi = [[' . $piId . ']]';
        $this->assertEquals($expectedQuery, $this->query);
    }

    public function testFetchListForReport(): void
    {
        $this->setUpSut(Repo::class, true);
        $this->sut->shouldReceive('fetchPaginatedList')->andReturn(['RESULTS']);

        $qb = $this->createMockQb('BLAH');
        $this->mockCreateQueryBuilder($qb);

        $this->queryBuilder
            ->shouldReceive('modifyQuery')->with($qb)->andReturnSelf()
            ->shouldReceive('withRefdata')->once()->andReturnSelf()
            ->shouldReceive('with')->with('pi', 'p')->once()->andReturnSelf()
            ->shouldReceive('with')->with('p.case', 'c')->once()->andReturnSelf()
            ->shouldReceive('with')->with('c.licence', 'l')->once()->andReturnSelf()
            ->shouldReceive('with')->with('l.organisation', 'o')->once()->andReturnSelf()
            ->shouldReceive('with')->with('l.status', 'lst')->once()->andReturnSelf()
            ->shouldReceive('with')->with('c.transportManager', 'tm')->once()->andReturnSelf()
            ->shouldReceive('with')->with('tm.tmStatus', 'tmst')->once()->andReturnSelf()
            ->shouldReceive('with')->with('tm.homeCd', 'tmhmcd')->once()->andReturnSelf()
            ->shouldReceive('with')->with('tmhmcd.person', 'tmhmcdp')->once()->andReturnSelf()
            ->shouldReceive('with')->with('venue', 'v')->once()->andReturnSelf()
            ->shouldReceive('with')->with('v.address', 'va')->once()->andReturnSelf()
            ->shouldReceive('paginate')->once()->andReturnSelf();

        $query = ReportListQry::create(
            [
                'startDate' => '2016-02-01',
                'endDate' => '2016-02-10',
            ]
        );
        $this->assertEquals(['RESULTS'], $this->sut->fetchList($query));

        $expectedQuery = 'BLAH '
            . 'AND m.hearingDate >= [[2016-02-01T00:00:00+00:00]] '
            . 'AND m.hearingDate <= [[2016-02-10T23:59:59+00:00]]';
        $this->assertEquals($expectedQuery, $this->query);
    }

    public function testFetchListForReportWithTrafficAreas(): void
    {
        $this->setUpSut(Repo::class, true);
        $this->sut->shouldReceive('fetchPaginatedList')->andReturn(['RESULTS']);

        $qb = $this->createMockQb('BLAH');
        $this->mockCreateQueryBuilder($qb);

        $this->queryBuilder
            ->shouldReceive('modifyQuery')->with($qb)->andReturnSelf()
            ->shouldReceive('withRefdata')->once()->andReturnSelf()
            ->shouldReceive('with')->with('pi', 'p')->once()->andReturnSelf()
            ->shouldReceive('with')->with('p.case', 'c')->once()->andReturnSelf()
            ->shouldReceive('with')->with('c.licence', 'l')->once()->andReturnSelf()
            ->shouldReceive('with')->with('l.organisation', 'o')->once()->andReturnSelf()
            ->shouldReceive('with')->with('l.status', 'lst')->once()->andReturnSelf()
            ->shouldReceive('with')->with('c.transportManager', 'tm')->once()->andReturnSelf()
            ->shouldReceive('with')->with('tm.tmStatus', 'tmst')->once()->andReturnSelf()
            ->shouldReceive('with')->with('tm.homeCd', 'tmhmcd')->once()->andReturnSelf()
            ->shouldReceive('with')->with('tmhmcd.person', 'tmhmcdp')->once()->andReturnSelf()
            ->shouldReceive('with')->with('venue', 'v')->once()->andReturnSelf()
            ->shouldReceive('with')->with('v.address', 'va')->once()->andReturnSelf()
            ->shouldReceive('paginate')->once()->andReturnSelf();

        $query = ReportListQry::create(
            [
                'startDate' => '2016-02-01',
                'endDate' => '2016-02-10',
                'trafficAreas' => ['B']
            ]
        );
        $this->assertEquals(['RESULTS'], $this->sut->fetchList($query));

        $expectedQuery = 'BLAH '
            . 'AND m.hearingDate >= [[2016-02-01T00:00:00+00:00]] '
            . 'AND m.hearingDate <= [[2016-02-10T23:59:59+00:00]] '
            . 'AND v.trafficArea IN ["B"]';
        $this->assertEquals($expectedQuery, $this->query);
    }

    public function testFetchListForReportWithtrafficAreasOther(): void
    {
        $this->setUpSut(Repo::class, true);
        $this->sut->shouldReceive('fetchPaginatedList')->andReturn(['RESULTS']);

        $qb = $this->createMockQb('BLAH');
        $this->mockCreateQueryBuilder($qb);

        $this->queryBuilder
            ->shouldReceive('modifyQuery')->with($qb)->andReturnSelf()
            ->shouldReceive('withRefdata')->once()->andReturnSelf()
            ->shouldReceive('with')->with('pi', 'p')->once()->andReturnSelf()
            ->shouldReceive('with')->with('p.case', 'c')->once()->andReturnSelf()
            ->shouldReceive('with')->with('c.licence', 'l')->once()->andReturnSelf()
            ->shouldReceive('with')->with('l.organisation', 'o')->once()->andReturnSelf()
            ->shouldReceive('with')->with('l.status', 'lst')->once()->andReturnSelf()
            ->shouldReceive('with')->with('c.transportManager', 'tm')->once()->andReturnSelf()
            ->shouldReceive('with')->with('tm.tmStatus', 'tmst')->once()->andReturnSelf()
            ->shouldReceive('with')->with('tm.homeCd', 'tmhmcd')->once()->andReturnSelf()
            ->shouldReceive('with')->with('tmhmcd.person', 'tmhmcdp')->once()->andReturnSelf()
            ->shouldReceive('with')->with('venue', 'v')->once()->andReturnSelf()
            ->shouldReceive('with')->with('v.address', 'va')->once()->andReturnSelf()
            ->shouldReceive('paginate')->once()->andReturnSelf();

        $query = ReportListQry::create(
            [
                'startDate' => '2016-02-01',
                'endDate' => '2016-02-10',
                'trafficAreas' => ['B', 'other']
            ]
        );
        $this->assertEquals(['RESULTS'], $this->sut->fetchList($query));

        $expectedQuery = 'BLAH '
            . 'AND m.hearingDate >= [[2016-02-01T00:00:00+00:00]] '
            . 'AND m.hearingDate <= [[2016-02-10T23:59:59+00:00]] '
            . 'AND (m.venue IS NULL OR v.trafficArea IN ["B"])';
        $this->assertEquals($expectedQuery, $this->query);
    }
}
