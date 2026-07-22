<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Dvsa\Olcs\Api\Entity;
use Dvsa\Olcs\Transfer\Query as TransferQry;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Domain\Repository\SubCategory::class)]
final class SubCategoryTest extends RepositoryTestCase
{
    public const int CATEGORY = 90001;

    #[\Override]
    public function setUp(): void
    {
        $this->setUpSut(\Dvsa\Olcs\Api\Domain\Repository\SubCategory::class, true);
    }

    public function testApplyListFiltersNoFilters(): void
    {
        $qb = $this->createMockQb('QUERY');

        $this->mockCreateQueryBuilder($qb);

        $this->queryBuilder
            ->shouldReceive('modifyQuery')->with($qb)->once()->andReturnSelf()
            ->shouldReceive('order')->zeroOrMoreTimes()->andReturnSelf()
            ->shouldReceive('withRefdata')->once()->andReturnSelf();

        $this->sut->shouldReceive('fetchPaginatedList')->andReturn('RESULTS');

        $dto = TransferQry\SubCategory\GetList::create([]);
        $this->assertEquals('RESULTS', $this->sut->fetchList($dto));

        $this->assertEquals('QUERY', $this->query);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestApplyListX')]
    public function testApplyListX(mixed $query, mixed $expect): void
    {
        $qb = $this->createMockQb('QUERY');

        $this->mockCreateQueryBuilder($qb);

        $this->queryBuilder
            ->shouldReceive('modifyQuery')->with($qb)->atLeast(1)->andReturnSelf()
            ->shouldReceive('order')->with('id', 'ASC', [])->once()->andReturnSelf()
            ->shouldReceive('withRefdata')->once()->andReturnSelf();

        $this->sut->shouldReceive('fetchPaginatedList')
            ->andReturn('RESULTS');

        $this->assertEquals(
            'RESULTS',
            $this->sut->fetchList(TransferQry\SubCategory\GetList::create($query))
        );

        $this->assertEquals($expect, $this->query);
    }

    public static function dpTestApplyListX(): \Iterator
    {
        yield [
            'query' => [
                'isTaskCategory' => 'Y',
                'isDocCategory' => 'N',
                'isScanCategory' => 'Y',
                'isOnlyWithItems' => 'Y',
                'category' => self::CATEGORY,
            ],
            'expect' => 'QUERY ' .
                'AND m.isTask = [[true]] ' .
                'AND m.isDoc = [[false]] ' .
                'AND m.isScan = [[true]] ' .
                'AND m.category = [[' . self::CATEGORY . ']]',
        ];
        yield [
            'query' => [
                'isDocCategory' => 'Y',
                'isOnlyWithItems' => 'N',
            ],
            'expect' => 'QUERY ' .
                'AND m.isDoc = [[true]]',
        ];
        yield [
            'query' => [
                'isTaskCategory' => 'N',
                'isDocCategory' => 'Y',
                'isScanCategory' => 'N',
                'isOnlyWithItems' => 'Y',
                'category' => self::CATEGORY,
            ],
            'expect' => 'QUERY ' .
                'SELECT DISTINCT m ' .
                'INNER JOIN ' . Entity\Doc\DocTemplate::class . ' dct ' .
                'WITH (dct.category = m.category AND dct.subCategory = m.id) ' .
                'INNER JOIN ' . Entity\Doc\Document::class . ' dc WITH dc.id = dct.document ' .
                'AND m.isTask = [[false]] ' .
                'AND m.isDoc = [[true]] ' .
                'AND m.isScan = [[false]] ' .
                'AND m.category = [[' . self::CATEGORY . ']]',
        ];
    }
}
