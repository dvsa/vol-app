<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Dvsa\Olcs\Api\Domain\Query\Bus\EbsrSubmissionList;
use Mockery as m;
use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Api\Domain\Repository\EbsrSubmission as Repo;

/**
 * EbsrSubmissionTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
final class EbsrSubmissionTest extends RepositoryTestCase
{
    #[\Override]
    public function setUp(): void
    {
        $this->setUpSut(Repo::class);
    }

    public function testFetchByOrganisation(): void
    {
        $qb = $this->createMockQb('BLAH');

        $this->mockCreateQueryBuilder($qb);
        $this->queryBuilder
            ->shouldReceive('modifyQuery')->with($qb)->once()->andReturnSelf()
            ->shouldReceive('withRefdata')->with()->once()->andReturnSelf()
            ->shouldReceive('with')->andReturnSelf();

        $qb->shouldReceive('getQuery')->andReturn(
            m::mock(\Doctrine\ORM\Query::class)->shouldReceive('execute')
                ->shouldReceive('getResult')
                ->andReturn(['RESULTS'])
                ->getMock()
        );

        $this->assertEquals(
            [
                'RESULTS'
            ],
            $this->sut->fetchByOrganisation(
                'ORG1',
                'submission_type',
                'submission_status'
            )
        );

        $expectedQuery = 'BLAH AND m.ebsrSubmissionType = [[submission_type]] AND e.ebsrSubmissionStatus = ' .
            '[[submission_status]] AND m.organisation = [[ORG1]]';
        $this->assertEquals($expectedQuery, $this->query);
    }

    public function testBuildDefaultQuery(): void
    {
        $sut = m::mock(Repo::class)->makePartial()->shouldAllowMockingProtectedMethods();

        $mockQb = m::mock(QueryBuilder::class);
        $mockQi = m::mock(\Dvsa\Olcs\Transfer\Query\QueryInterface::class);

        $sut->shouldReceive('getQueryBuilder')->with()->andReturn($mockQb);

        $mockQb->shouldReceive('modifyQuery')->with($mockQb)->once()->andReturnSelf();
        $mockQb->shouldReceive('withRefdata')->with()->once()->andReturnSelf();
        $mockQb->shouldReceive('with')->with('m.busReg', 'b')->once()->andReturnSelf();
        $mockQb->shouldReceive('with')->with('b.licence', 'l')->once()->andReturnSelf();
        $mockQb->shouldReceive('with')->with('b.otherServices')->once()->andReturnSelf();
        $mockQb->shouldReceive('with')->with('l.organisation')->once()->andReturnSelf();

        $sut->buildDefaultListQuery($mockQb, $mockQi);
    }

    /**
     * tests fetching a list by organisation and status
     */
    public function testFetchForOrganisationByStatus(): void
    {
        $organisation = 3;
        $status = 'status';

        $qb = m::mock(QueryBuilder::class);
        $expr = new \Doctrine\ORM\Query\Expr();
        $qb->shouldReceive('expr')
            ->zeroOrMoreTimes()
            ->andReturn($expr);

        $qb->shouldReceive('setParameter')
            ->zeroOrMoreTimes()
            ->andReturnSelf();

        $qb->shouldReceive('andWhere')
            ->times(2)
            ->andReturnSelf();
        $this->mockCreateQueryBuilder($qb);
        $this->queryBuilder
            ->shouldReceive('modifyQuery')->with($qb)->once()->andReturnSelf();

        $qb->shouldReceive('getQuery')->andReturn(
            m::mock(\Doctrine\ORM\Query::class)->shouldReceive('execute')
                ->shouldReceive('getResult')
                ->andReturn(['RESULTS'])
                ->getMock()
        );

        // organisation clause

        // status clause

        $this->assertEquals(['RESULTS'], $this->sut->fetchForOrganisationByStatus($organisation, $status, 1));
    }

    /**
     * Tests applyListFilters
     */
    public function testApplyListFilters(): void
    {
        $this->setUpSut(Repo::class, true);

        $mockQb = m::mock(QueryBuilder::class);
        $expr = new \Doctrine\ORM\Query\Expr();
        $mockQb->shouldReceive('expr')
        ->zeroOrMoreTimes()
        ->andReturn($expr);

        $mockQb->shouldReceive('setParameter')
        ->zeroOrMoreTimes()
        ->andReturnSelf();

        $mockQb->shouldReceive('andWhere')
        ->times(4)
        ->andReturnSelf();

        // organisation clause

        // status clause

        // subType clause

        // always ignore uploaded status

        $query = EbsrSubmissionList::create(['organisation' => 3, 'subType' => 'bar', 'status' => 'foo']);

        $this->sut->applyListFilters($mockQb, $query);
    }
}
