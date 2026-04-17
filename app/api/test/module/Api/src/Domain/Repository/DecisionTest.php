<?php

declare(strict_types=1);

/**
 * Decision repo test
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Mockery as m;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityRepository;
use Dvsa\Olcs\Api\Domain\Repository\Decision as Repo;
use Dvsa\Olcs\Transfer\Query\Decision\DecisionList;

/**
 * Decision repo test
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
class DecisionTest extends RepositoryTestCase
{
    public function setUp(): void
    {
        $this->setUpSut(Repo::class, true);
    }

    public function testApplyListFilters(): void
    {
        $this->sut->shouldReceive('fetchPaginatedList')->andReturn(['RESULTS']);

        $qb = $this->createMockQb('BLAH');
        $this->mockCreateQueryBuilder($qb);

        $this->queryBuilder
            ->shouldReceive('modifyQuery')->with($qb)->andReturnSelf()
            ->shouldReceive('withRefdata')->once()->andReturnSelf();

        $query = DecisionList::create(['isNi' => 'Y', 'goodsOrPsv' => 'lcat_psv']);
        $this->assertEquals(['RESULTS'], $this->sut->fetchList($query));

        $expectedQuery = 'BLAH '
            . 'AND m.isNi = [[true]] '
            . 'AND m.goodsOrPsv = [[lcat_psv]]';
        $this->assertEquals($expectedQuery, $this->query);
    }

    public function testApplyListFiltersForTm(): void
    {
        $this->sut->shouldReceive('fetchPaginatedList')->andReturn(['RESULTS']);

        $qb = $this->createMockQb('BLAH');
        $this->mockCreateQueryBuilder($qb);

        $this->queryBuilder
            ->shouldReceive('modifyQuery')->with($qb)->andReturnSelf()
            ->shouldReceive('withRefdata')->once()->andReturnSelf();

        $query = DecisionList::create(['isNi' => 'Y', 'goodsOrPsv' => 'NULL']);
        $this->assertEquals(['RESULTS'], $this->sut->fetchList($query));

        $expectedQuery = 'BLAH '
            . 'AND m.isNi = [[true]] '
            . 'AND m.goodsOrPsv IS NULL';
        $this->assertEquals($expectedQuery, $this->query);
    }
}
