<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Dvsa\Olcs\Api\Domain\Repository\SeriousInfringement as SiRepo;
use Dvsa\Olcs\Transfer\Query\Cases\Si\SiList as SiListQry;

/**
 * SeriousInfringementTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class SeriousInfringementTest extends RepositoryTestCase
{
    public function setUp(): void
    {
        $this->setUpSut(SiRepo::class, true);
    }

    public function testApplyListFilters(): void
    {
        $qb = $this->createMockQb('QUERY');

        $this->mockCreateQueryBuilder($qb);

        $this->queryBuilder
            ->shouldReceive('modifyQuery')->with($qb)->once()->andReturnSelf()
            ->shouldReceive('withRefdata')->once()->andReturnSelf();

        $dto = SiListQry::create(['case' => 812]);
        $this->sut->shouldReceive('fetchPaginatedList')->andReturn('RESULTS');

        $this->assertEquals('RESULTS', $this->sut->fetchList($dto));

        $expectedQuery = 'QUERY AND m.case = [[812]]';

        $this->assertEquals($expectedQuery, $this->query);
    }
}
