<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Dvsa\Olcs\Transfer\Query\BusRegSearchView\BusRegSearchViewList;
use Dvsa\Olcs\Transfer\Query\Bus\SearchViewList;
use Dvsa\Olcs\Api\Domain\Query\BusRegSearchView\BusRegSearchViewList as LocalAuthoritySearchViewList;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Repository\BusRegSearchView as Repo;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityRepository;
use Dvsa\Olcs\Api\Entity\View\BusRegSearchView as Entity;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Entity\Bus\BusReg;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\Expr\Comparison;
use Doctrine\ORM\Query\Expr\Func;

/**
 * BusRegSearchViewTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
final class BusRegSearchViewTest extends RepositoryTestCase
{
    #[\Override]
    public function setUp(): void
    {
        $this->setUpSut(Repo::class);
    }

    public function testFetchByRegNo(): void
    {
        $qb = m::mock(QueryBuilder::class);
        $repo = m::mock(EntityRepository::class);

        $this->em->shouldReceive('getRepository')->with(Entity::class)->andReturn($repo);

        $repo->shouldReceive('createQueryBuilder')->with('m')->once()->andReturn($qb);

        $this->queryBuilder->shouldReceive('modifyQuery')->with($qb)->once()->andReturnSelf();

        $qb->shouldReceive('setParameter')->with('regNo', 'REG0001')->once()->andReturnSelf();

        $expr = m::mock(Expr::class);
        $comparison = m::mock(Comparison::class);
        $query = m::mock(Query::class);

        $qb->shouldReceive('expr')
            ->andReturn($expr);

        $expr->shouldReceive('eq')
            ->with('m.regNo', ':regNo')
            ->once()
            ->andReturn($comparison);

        $qb->shouldReceive('where')
            ->with($comparison)
            ->once()
            ->andReturnSelf();

        $query->shouldReceive('getResult')
            ->once()
            ->andReturn(['RESULTS']);

        $qb->shouldReceive('getQuery')
            ->once()
            ->andReturn($query);

        $this->assertSame('RESULTS', $this->sut->fetchByRegNo('REG0001'));
    }

    public function testFetchByRegNoNotFound(): void
    {
        $qb = m::mock(QueryBuilder::class);
        $repo = m::mock(EntityRepository::class);

        $this->em->shouldReceive('getRepository')->with(Entity::class)->andReturn($repo);

        $repo->shouldReceive('createQueryBuilder')->with('m')->once()->andReturn($qb);

        $this->queryBuilder->shouldReceive('modifyQuery')->with($qb)->once()->andReturnSelf();

        $qb->shouldReceive('setParameter')->with('regNo', 'REG0001')->once()->andReturnSelf();

        $expr = m::mock(Expr::class);
        $comparison = m::mock(Comparison::class);
        $query = m::mock(Query::class);

        $qb->shouldReceive('expr')
            ->andReturn($expr);

        $expr->shouldReceive('eq')
            ->with('m.regNo', ':regNo')
            ->once()
            ->andReturn($comparison);

        $qb->shouldReceive('where')
            ->with($comparison)
            ->once()
            ->andReturnSelf();

        $query->shouldReceive('getResult')
            ->once()
            ->andReturn([]);

        $qb->shouldReceive('getQuery')
            ->once()
            ->andReturn($query);

        $this->expectException(NotFoundException::class);

        $this->sut->fetchByRegNo('REG0001');
    }

    public function testFetchActiveByLicence(): void
    {
        $activeStatuses = [
            BusReg::STATUS_NEW,
            BusReg::STATUS_VAR,
            BusReg::STATUS_REGISTERED,
            BusReg::STATUS_CANCEL,
        ];

        $qb = m::mock(QueryBuilder::class);
        $repo = m::mock(EntityRepository::class);

        $this->em->shouldReceive('getRepository')
            ->with(Entity::class)
            ->andReturn($repo);

        $repo->shouldReceive('createQueryBuilder')
            ->with('m')
            ->once()
            ->andReturn($qb);

        $this->queryBuilder->shouldReceive('modifyQuery')
            ->with($qb)
            ->once()
            ->andReturnSelf();

        $expr = m::mock(Expr::class);
        $licenceCondition = m::mock(Comparison::class);
        $statusCondition = m::mock(Func::class);
        $query = m::mock(Query::class);

        $qb->shouldReceive('expr')
            ->andReturn($expr);

        $expr->shouldReceive('eq')
            ->with('m.licId', ':licence')
            ->once()
            ->andReturn($licenceCondition);

        $qb->shouldReceive('where')
            ->with($licenceCondition)
            ->once()
            ->andReturnSelf();

        $qb->shouldReceive('setParameter')
            ->with('licence', '611')
            ->once()
            ->andReturnSelf();

        $expr->shouldReceive('in')
            ->with('m.busRegStatus', ':activeStatuses')
            ->once()
            ->andReturn($statusCondition);

        $qb->shouldReceive('andWhere')
            ->with($statusCondition)
            ->once()
            ->andReturnSelf();

        $qb->shouldReceive('setParameter')
            ->with('activeStatuses', $activeStatuses)
            ->once()
            ->andReturnSelf();

        $query->shouldReceive('getResult')
            ->once()
            ->andReturn(['RESULTS']);

        $qb->shouldReceive('getQuery')
            ->once()
            ->andReturn($query);

        $this->assertSame(['RESULTS'], $this->sut->fetchActiveByLicence(611));
    }

    /**
     * @param $context
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideContextGroupBys')]
    public function testFetchDistinctList(mixed $context, mixed $expected): void
    {
        $qb = m::mock(QueryBuilder::class);
        $repo = m::mock(EntityRepository::class);

        $this->em->shouldReceive('getRepository')->with(Entity::class)->andReturn($repo);

        $repo->shouldReceive('createQueryBuilder')->with('m')->once()->andReturn($qb);

        $qb->shouldReceive('distinct')->andReturnSelf();
        $qb->shouldReceive('select')->with($expected)->andReturnSelf();
        $qb->shouldReceive('getQuery->getResult')->once()->andReturn(['RESULTS']);

        $mockQuery = m::mock(QueryInterface::class);
        $mockQuery->shouldReceive('getContext')->andReturn($context);

        $this->assertSame(['RESULTS'], $this->sut->fetchDistinctList($mockQuery));
    }

    /**
     * @param $context
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideContextGroupBys')]
    public function testFetchDistinctListWithOrganisationId(mixed $context, mixed $expected): void
    {
        $organisationId = 1;

        $qb = m::mock(QueryBuilder::class);
        $repo = m::mock(EntityRepository::class);

        $expr = m::mock(Expr::class);
        $comparison = m::mock(Comparison::class);
        $query = m::mock(Query::class);

        $this->em->shouldReceive('getRepository')->with(Entity::class)->andReturn($repo);

        $repo->shouldReceive('createQueryBuilder')->with('m')->once()->andReturn($qb);

        $qb->shouldReceive('distinct')->andReturnSelf();
        $qb->shouldReceive('select')->with($expected)->andReturnSelf();

        $qb->shouldReceive('expr')
            ->andReturn($expr);

        $expr->shouldReceive('eq')
            ->with('m.organisationId', ':organisationId')
            ->once()
            ->andReturn($comparison);

        $qb->shouldReceive('andWhere')
            ->with($comparison)
            ->once()
            ->andReturnSelf();

        $query->shouldReceive('getResult')
            ->once()
            ->andReturn(['RESULTS']);

        $qb->shouldReceive('getQuery')
            ->once()
            ->andReturn($query);

        $qb->shouldReceive('setParameter')->with('organisationId', $organisationId)->once()->andReturnSelf();

        $mockQuery = m::mock(QueryInterface::class);
        $mockQuery->shouldReceive('getContext')->andReturn($context);

        $this->assertSame(['RESULTS'], $this->sut->fetchDistinctList($mockQuery, $organisationId));
    }

    /**
     * @param string $context to determine what data to return
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideContextGroupBys')]
    public function testFetchDistinctListWithLocalAuthorityId(mixed $context, mixed $expected): void
    {
        $localAuthorityId = 1;

        $qb = m::mock(QueryBuilder::class);
        $repo = m::mock(EntityRepository::class);

        $expr = m::mock(Expr::class);
        $comparison = m::mock(Comparison::class);
        $query = m::mock(Query::class);

        $this->em->shouldReceive('getRepository')->with(Entity::class)->andReturn($repo);

        $repo->shouldReceive('createQueryBuilder')->with('m')->once()->andReturn($qb);

        $qb->shouldReceive('distinct')->andReturnSelf();
        $qb->shouldReceive('select')->with($expected)->andReturnSelf();

        $qb->shouldReceive('expr')
            ->andReturn($expr);

        $expr->shouldReceive('eq')
            ->with('m.localAuthorityId', ':localAuthorityId')
            ->once()
            ->andReturn($comparison);

        $qb->shouldReceive('andWhere')
            ->with($comparison)
            ->once()
            ->andReturnSelf();

        $query->shouldReceive('getResult')
            ->once()
            ->andReturn(['RESULTS']);

        $qb->shouldReceive('getQuery')
            ->once()
            ->andReturn($query);
        $qb->shouldReceive('setParameter')->with('localAuthorityId', $localAuthorityId)->once()->andReturnSelf();

        $mockQuery = m::mock(QueryInterface::class);
        $mockQuery->shouldReceive('getContext')->andReturn($context);

        $this->assertSame(['RESULTS'], $this->sut->fetchDistinctList($mockQuery, null, $localAuthorityId));
    }

    /**
     * Data provider maps the relevant group by clauses that should be applied to the query given a certain context
     *
     * @return \Iterator<(int | string), mixed>
     */
    public static function provideContextGroupBys(): \Iterator
    {
        yield [
            'licence', ['m.licId', 'm.licNo'],
        ];
        yield [
            'organisation', ['m.organisationId', 'm.organisationName']
        ];
        yield [
            'busRegStatus', ['m.busRegStatus', 'm.busRegStatusDesc']
        ];
    }

    /**
     * Test applyListFilters when logged in as an Operator
     */
    public function testApplyListFiltersOperator(): void
    {
        $this->setUpSut(Repo::class, true);

        $mockQb = m::mock(QueryBuilder::class);

        $expr = m::mock(Expr::class);
        $condition1 = m::mock(Comparison::class);
        $condition2 = m::mock(Comparison::class);
        $condition3 = m::mock(Comparison::class);

        $mockQb->shouldReceive('expr')
            ->andReturn($expr);

        $expr->shouldReceive('eq')
            ->with('m.licId', ':licId')
            ->once()
            ->andReturn($condition1);

        $mockQb->shouldReceive('andWhere')
            ->with($condition1)
            ->once()
            ->andReturnSelf();

        $mockQb->shouldReceive('setParameter')
            ->with('licId', '1234')
            ->once()
            ->andReturnSelf();

        $expr->shouldReceive('eq')
            ->with('m.busRegStatus', ':busRegStatus')
            ->once()
            ->andReturn($condition2);

        $mockQb->shouldReceive('andWhere')
            ->with($condition2)
            ->once()
            ->andReturnSelf();

        $mockQb->shouldReceive('setParameter')
            ->with('busRegStatus', 'foo')
            ->once()
            ->andReturnSelf();

        $expr->shouldReceive('eq')
            ->with('m.organisationId', ':organisationId')
            ->once()
            ->andReturn($condition3);

        $mockQb->shouldReceive('andWhere')
            ->with($condition3)
            ->once()
            ->andReturnSelf();

        $mockQb->shouldReceive('setParameter')
            ->with('organisationId', 342)
            ->once()
            ->andReturnSelf();

        $mockQb->shouldReceive('groupBy')
            ->with('m.id')
            ->once()
            ->andReturnSelf();

        $mockQ = BusRegSearchViewList::create(
            [
                'licId' => '1234',
                'busRegStatus' => 'foo',
                'organisationId' => 342
            ]
        );

        $this->sut->applyListFilters($mockQb, $mockQ);
    }

    /**
     * Test applyListFilters when using status (to comply with bus reg main page)
     */
    public function testApplyListFiltersAlternativeStatus(): void
    {
        $this->setUpSut(Repo::class, true);

        $mockQb = m::mock(QueryBuilder::class);
        $expr = m::mock(Expr::class);
        $condition = m::mock(Comparison::class);

        $mockQb->shouldReceive('expr')
            ->andReturn($expr);

        $expr->shouldReceive('eq')
            ->with('m.busRegStatus', ':status')
            ->once()
            ->andReturn($condition);

        $mockQb->shouldReceive('andWhere')
            ->with($condition)
            ->once()
            ->andReturnSelf();

        $mockQb->shouldReceive('setParameter')
            ->with('status', 'bar')
            ->once()
            ->andReturnSelf();

        $mockQb->shouldReceive('groupBy')
            ->with('m.id')
            ->once()
            ->andReturnSelf();

        $mockQ = SearchViewList::create(['status' => 'bar']);

        $this->sut->applyListFilters($mockQb, $mockQ);
    }

    /**
     * Test applyListFilters when logged in as an LA
     */
    public function testApplyListFiltersLocalAuthority(): void
{
    $this->setUpSut(Repo::class, true);

    $mockQb = m::mock(QueryBuilder::class);
    $expr = m::mock(\Doctrine\ORM\Query\Expr::class);

    $expr->shouldReceive('eq')
        ->zeroOrMoreTimes()
        ->andReturnUsing(
            fn ($left, $right) =>
                new \Doctrine\ORM\Query\Expr\Comparison($left, '=', $right)
        );

    $mockQb->shouldReceive('expr')
        ->zeroOrMoreTimes()
        ->andReturn($expr);

    $mockQb->shouldReceive('andWhere')
        ->zeroOrMoreTimes()
        ->andReturnSelf();

    $mockQb->shouldReceive('setParameter')
        ->with('licId', '1234')
        ->once()
        ->andReturnSelf();

    $mockQb->shouldReceive('setParameter')
        ->with('busRegStatus', 'foo')
        ->once()
        ->andReturnSelf();

    $mockQb->shouldReceive('setParameter')
        ->with('localAuthorityId', 234)
        ->once()
        ->andReturnSelf();

    $mockQb->shouldReceive('groupBy')
        ->with('m.id')
        ->once()
        ->andReturnSelf();

    $mockQ = LocalAuthoritySearchViewList::create(
        [
            'licId' => '1234',
            'busRegStatus' => 'foo',
            'localAuthorityId' => 234,
        ]
    );

    $this->sut->applyListFilters($mockQb, $mockQ);
}
}
