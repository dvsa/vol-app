<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Repository\TaskAllocationRule as TaskAllocationRuleRepo;
use Dvsa\Olcs\Api\Entity\Task\TaskAllocationRule as Entity;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Mockery as m;

final class TaskAllocationRuleTest extends RepositoryTestCase
{
    protected $sut;

    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(TaskAllocationRuleRepo::class, true);
    }

    /**
     * Every optional parameter has an IS NULL fallback rather than being omitted, so the rule
     * lookup only ever matches rules of exactly the same specificity.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('fetchByParametersDataProvider')]
    public function testFetchByParameters(array $args, string $expectedWhere): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getResult')->with(Query::HYDRATE_OBJECT)->andReturn(['foo', 'bar']);

        $this->assertSame(['foo', 'bar'], $this->sut->fetchByParameters(...$args));

        $this->assertSame(
            'SELECT m FROM ' . Entity::class . ' m WHERE ' . $expectedWhere,
            $qb->getDQL(),
        );
    }

    public static function fetchByParametersDataProvider(): \Iterator
    {
        yield 'all parameters' => [
            [111, 222, 'gv', 'B', true],
            'm.category = :category AND m.subCategory = :subCategory AND m.goodsOrPsv = :operatorType'
            . ' AND m.trafficArea = :trafficArea AND m.isMlh = :isMlh',
        ];
        yield 'no isMlh' => [
            [111, 222, 'gv', 'B', null],
            'm.category = :category AND m.subCategory = :subCategory AND m.goodsOrPsv = :operatorType'
            . ' AND m.trafficArea = :trafficArea AND m.isMlh IS NULL',
        ];
        yield 'no traffic area' => [
            [111, 222, 'gv', null, null],
            'm.category = :category AND m.subCategory = :subCategory AND m.goodsOrPsv = :operatorType'
            . ' AND m.trafficArea IS NULL AND m.isMlh IS NULL',
        ];
        yield 'no operator type' => [
            [111, 222, null, null, null],
            'm.category = :category AND m.subCategory = :subCategory AND m.goodsOrPsv IS NULL'
            . ' AND m.trafficArea IS NULL AND m.isMlh IS NULL',
        ];
        yield 'category only' => [
            [111, null, null, null, null],
            'm.category = :category AND m.subCategory IS NULL AND m.goodsOrPsv IS NULL'
            . ' AND m.trafficArea IS NULL AND m.isMlh IS NULL',
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('fallbackProvider')]
    public function testFetchByParametersFallsBackWithoutSubCategory(array $returnValues, int $expectedCalls): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->shouldReceive('getResult')
            ->with(Query::HYDRATE_OBJECT)
            ->times($expectedCalls)
            ->andReturnValues($returnValues);

        $this->assertSame(['foo'], $this->sut->fetchByParametersWithFallbackWhenSubCategoryNotFound(1, 2));
    }

    public static function fallbackProvider(): \Iterator
    {
        yield 'subcategory returns a result' => [[['foo']], 1];
        yield 'subcategory returns nothing, so retry without it' => [[[], ['foo']], 2];
    }

    public function testBuildDefaultListQuery(): void
    {
        $qb = $this->createRealQb();

        $this->sut->buildDefaultListQuery($qb, m::mock(QueryInterface::class));

        // m.goodsOrPsv is joined twice: w0 by withRefdata and gop explicitly. The explicit
        // alias is the one the HIDDEN criteria select needs. See the migration findings.
        $this->assertSame(
            'SELECT m, w0, cat, gop, ta,'
            . ' cat.description as HIDDEN categoryDescription,'
            . ' gop.id as HIDDEN criteria,'
            . ' ta.name as HIDDEN trafficAreaName'
            . ' FROM ' . Entity::class . ' m'
            . ' LEFT JOIN m.goodsOrPsv w0 LEFT JOIN m.category cat'
            . ' LEFT JOIN m.goodsOrPsv gop LEFT JOIN m.trafficArea ta',
            $qb->getDQL(),
        );
    }
}
