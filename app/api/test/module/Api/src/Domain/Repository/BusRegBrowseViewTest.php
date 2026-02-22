<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Repository\BusRegBrowseView as Repo;
use Dvsa\Olcs\Transfer\Query\Bus\BusRegBrowseList as BusRegBrowseListQuery;
use Mockery as m;

/**
 * BusRegSearchViewTest
 */
class BusRegBrowseViewTest extends RepositoryTestCase
{
    public function setUp(): void
    {
        $this->setUpSut(Repo::class);
    }

    public function testFetchDistinctList(): void
    {
        $qb = $this->createMockQb('BLAH');

        $this->mockCreateQueryBuilder($qb);

        $qb->shouldReceive('getQuery')->andReturn(
            m::mock()->shouldReceive('execute')
                ->shouldReceive('getResult')
                ->andReturn(['RESULTS'])
                ->getMock()
        );
        $this->assertEquals(['RESULTS'], $this->sut->fetchDistinctList('FIELD1'));

        $expectedQuery = 'BLAH DISTINCT SELECT m.FIELD1';
        $this->assertEquals($expectedQuery, $this->query);
    }

    public function testFetchForExport(): void
    {
        $qb = $this->createMockQb('BLAH');

        $this->mockCreateQueryBuilder($qb);

        $qb->shouldReceive('getQuery')->andReturn(
            m::mock()->shouldReceive('execute')
                ->shouldReceive('iterate')
                ->andReturn(['RESULTS'])
                ->getMock()
        );

        $results = $this->sut->fetchForExport(
            ['FIELD1', 'FIELD2'],
            '2017-2-1',
            ['TA1', 'TA2'],
            'STATUS'
        );
        $this->assertEquals(['RESULTS'], $results);

        $expectedQuery
            = 'BLAH SELECT m.FIELD1, m.FIELD2 '
            . 'AND m.acceptedDate = [[2017-02-01]] '
            . 'AND m.trafficAreaId IN [[["TA1","TA2"]]] '
            . 'AND m.status = [[STATUS]]';
        $this->assertEquals($expectedQuery, $this->query);
    }

    public function testFetchForExportWithoutStatus(): void
    {
        $qb = $this->createMockQb('BLAH');

        $this->mockCreateQueryBuilder($qb);

        $qb->shouldReceive('getQuery')->andReturn(
            m::mock()->shouldReceive('execute')
                ->shouldReceive('iterate')
                ->andReturn(['RESULTS'])
                ->getMock()
        );

        $results = $this->sut->fetchForExport(
            ['FIELD1'],
            '2017-02-01',
            ['TA1']
        );
        $this->assertEquals(['RESULTS'], $results);

        $expectedQuery
            = 'BLAH SELECT m.FIELD1 '
            . 'AND m.acceptedDate = [[2017-02-01]] '
            . 'AND m.trafficAreaId IN [[["TA1"]]]';
        $this->assertEquals($expectedQuery, $this->query);
    }

    public function testFetchList(): void
    {
        $this->setUpSut(Repo::class, true);
        $this->sut->shouldReceive('fetchPaginatedList')->andReturn(['RESULTS']);

        $qb = $this->createMockQb('BLAH');
        $this->mockCreateQueryBuilder($qb);

        $this->queryBuilder
            ->shouldReceive('modifyQuery')->with($qb)->andReturnSelf()
            ->shouldReceive('withRefdata')->once()->andReturnSelf()
            ->shouldReceive('paginate')->once()->andReturnSelf();

        $query = BusRegBrowseListQuery::create(
            [
                'trafficAreas' => ['TA1', 'TA2'],
                'status' => 'STATUS',
                'acceptedDate' => '2017-02-01',
            ]
        );
        $this->assertEquals(['RESULTS'], $this->sut->fetchList($query));

        $expectedQuery
            = 'BLAH '
            . 'AND m.acceptedDate = [[2017-02-01]] '
            . 'AND m.trafficAreaId IN [[["TA1","TA2"]]] '
            . 'AND m.status = [[STATUS]]';
        $this->assertEquals($expectedQuery, $this->query);
    }
}
