<?php

declare(strict_types=1);

/**
 * Task Allocation Rule Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Repository\TaskAllocationRule as TaskAllocationRuleRepo;
use Dvsa\Olcs\Api\Entity\Task\TaskAllocationRule as Entity;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Mockery as m;

final class TaskAllocationRuleTest extends RepositoryTestCase
{
    /** @var TaskAllocationRuleRepo|m\MockInterface */
    protected $sut;

    /**
     * Set up
     */
    #[\Override]
    public function setUp(): void
    {
        $this->setUpSut(TaskAllocationRuleRepo::class);
    }

    /**
     * Test fetch by parameters
     *
     * @param int $category
     * @param string $operatorType
     * @param string $trafficArea
     * @param bool $isMlh
     * @param string $query
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('fetchByParametersDataProvider')]
    public function testFetchByParameters(mixed $category, mixed $subCategory, mixed $operatorType, mixed $trafficArea, mixed $isMlh, mixed $query): void
    {
        $qb = $this->createMockQb('[QUERY]');
        $qb->shouldReceive('getQuery->getResult')
            ->with(Query::HYDRATE_OBJECT)
            ->once()
            ->andReturn(['foo', 'bar']);

        $repo = m::mock(EntityRepository::class);
        $repo->shouldReceive('createQueryBuilder')
            ->with('m')
            ->once()
            ->andReturn($qb);

        $this->em->shouldReceive('getRepository')
            ->with(Entity::class)
            ->once()
            ->andReturn($repo);

        $this->assertEquals(
            ['foo', 'bar'],
            $this->sut->fetchByParameters($category, $subCategory, $operatorType, $trafficArea, $isMlh)
        );
        $this->assertEquals(
            $query,
            $this->query
        );
    }

    /**
     * Param provider
     *
     * @return \Iterator<(int | string), mixed>
     */
    public static function fetchByParametersDataProvider(): \Iterator
    {
        // category, operatorType, trafficArea, isMlh, query
        yield [
            111,
            222,
            'gv',
            'B',
            true,
            '[QUERY] AND m.category = [[111]] AND m.subCategory = [[222]] AND m.goodsOrPsv = [[gv]] AND m.trafficArea = [[B]] ' .
            'AND m.isMlh = [[true]]'
        ];
        yield [
            111,
            222,
            'gv',
            'B',
            null,
            '[QUERY] AND m.category = [[111]] AND m.subCategory = [[222]] AND m.goodsOrPsv = [[gv]] AND m.trafficArea = [[B]] ' .
            'AND m.isMlh IS NULL'
        ];
        yield [
            111,
            222,
            'gv',
            null,
            null,
            '[QUERY] AND m.category = [[111]] AND m.subCategory = [[222]] AND m.goodsOrPsv = [[gv]] AND m.trafficArea IS NULL ' .
            'AND m.isMlh IS NULL'
        ];
        yield [
            111,
            222,
            null,
            null,
            null,
            '[QUERY] AND m.category = [[111]] AND m.subCategory = [[222]] AND m.goodsOrPsv IS NULL AND m.trafficArea IS NULL ' .
            'AND m.isMlh IS NULL'
        ];
        yield [
            111,
            null,
            null,
            null,
            null,
            '[QUERY] AND m.category = [[111]] AND m.subCategory IS NULL AND m.goodsOrPsv IS NULL AND m.trafficArea IS NULL ' .
            'AND m.isMlh IS NULL'
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('fetchByParametersAttemptsLookupWithoutSubCategoryWhenCallReturnsNoResultsDataProvider')]
    public function testFetchByParametersAttemptsLookupWithoutSubCategoryWhenCallReturnsNoResults(array $returnValues, bool $expectSubsequentCall): void
    {
        $qb = $this->createMockQb('[QUERY]');
        $qb->shouldReceive('getQuery->getResult')
            ->with(Query::HYDRATE_OBJECT)
            ->times($expectSubsequentCall ? 2 : 1)
            ->andReturnValues($returnValues);

        $repo = m::mock(EntityRepository::class);
        $repo->shouldReceive('createQueryBuilder')
            ->with('m')
            ->times($expectSubsequentCall ? 2 : 1)
            ->andReturn($qb);

        $this->em->shouldReceive('getRepository')
            ->with(Entity::class)
            ->times($expectSubsequentCall ? 2 : 1)
            ->andReturn($repo);

        $this->assertEquals(
            ['foo'],
            $this->sut->fetchByParametersWithFallbackWhenSubCategoryNotFound(1, 2)
        );
    }

    public static function fetchByParametersAttemptsLookupWithoutSubCategoryWhenCallReturnsNoResultsDataProvider(): \Iterator
    {
        yield 'Subcategory returns result' => [
            [
                ['foo'],
            ],
            false
        ];
        yield 'Subcategory returns no results' => [
            [
                [],
                ['foo'],
            ],
            true
        ];
    }

    /**
     * Test build default list query
     */
    public function testBuildDefaultListQuery(): void
    {
        $qb = $this->createMockQb('[QUERY]');
        $query = m::mock(QueryInterface::class);

        $this->queryBuilder->shouldReceive('modifyQuery')->with($qb)->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('withRefdata')->with()->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('category', 'cat')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('goodsOrPsv', 'gop')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('trafficArea', 'ta')->once()->andReturnSelf();

        $this->sut->buildDefaultListQuery($qb, $query);

        $this->assertSame(
            '[QUERY] SELECT cat.description as HIDDEN categoryDescription SELECT gop.id as HIDDEN criteria SELECT '
            . 'ta.name as HIDDEN trafficAreaName',
            $this->query
        );
    }
}
