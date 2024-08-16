<?php

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Dvsa\Olcs\Transfer\Query as TransferQry;
use Dvsa\Olcs\Api\Entity;
use Mockery as m;

/**
 * @covers \Dvsa\Olcs\Api\Domain\Repository\Category
 */
class CategoryTest extends RepositoryTestCase
{
    public function setUp(): void
    {
        $this->setUpSut(\Dvsa\Olcs\Api\Domain\Repository\Category::class, true);
    }

    public function testApplyListFiltersInvalidClass()
    {
        $qb = $this->createMockQb('QUERY');

        $this->mockCreateQueryBuilder($qb);

        $this->queryBuilder
            ->shouldReceive('modifyQuery')->times(1)->with($qb)->andReturnSelf()
            ->shouldReceive('withRefdata')->once()->andReturnSelf();

        $this->sut->shouldReceive('fetchPaginatedList')
            ->andReturn('RESULTS');

        $dto = TransferQry\Category\GetList::create([]);

        static::assertEquals('RESULTS', $this->sut->fetchList($dto));
        static::assertEquals('QUERY', $this->query);
    }

    /**
     * @dataProvider dpTestApplyListX
     */
    public function testApplyListX($query, $expect)
    {
        $qb = $this->createMockQb('QUERY');

        $this->mockCreateQueryBuilder($qb);

        $this->queryBuilder
            ->shouldReceive('modifyQuery')->atLeast(1)->with($qb)->andReturnSelf()
            ->shouldReceive('withRefdata')->once()->andReturnSelf();

        $this->sut->shouldReceive('fetchPaginatedList')
            ->andReturn('RESULTS');

        $this->assertEquals(
            'RESULTS',
            $this->sut->fetchList(TransferQry\Category\GetList::create($query))
        );

        $this->assertEquals($expect, $this->query);
    }

    public function dpTestApplyListX()
    {
        return [
            [
                'query' => [
                    'isTaskCategory' => 'Y',
                    'isDocCategory' => 'N',
                    'isScanCategory' => 'Y',
                    'isOnlyWithItems' => 'Y',
                ],
                'expect' => 'QUERY ' .
                    'AND m.isTaskCategory = [[true]] ' .
                    'AND m.isDocCategory = [[false]] ' .
                    'AND m.isScanCategory = [[true]]',
            ],
            [
                'query' => [
                    'isDocCategory' => 'Y',
                    'isOnlyWithItems' => 'N',
                ],
                'expect' => 'QUERY ' .
                    'AND m.isDocCategory = [[true]]',
            ],
            [
                'query' => [
                    'isTaskCategory' => 'N',
                    'isDocCategory' => 'Y',
                    'isScanCategory' => 'N',
                    'isOnlyWithItems' => 'Y',
                ],
                'expect' => $expectedQuery = 'QUERY ' .
                    'SELECT DISTINCT m ' .
                    'INNER JOIN ' . Entity\Doc\DocTemplate::class . ' dct WITH dct.category = m.id ' .
                    'INNER JOIN ' . Entity\Doc\Document::class . ' dc WITH dc.id = dct.document ' .
                    'AND m.isTaskCategory = [[false]] ' .
                    'AND m.isDocCategory = [[true]] ' .
                    'AND m.isScanCategory = [[false]]',
            ],
        ];
    }
}
