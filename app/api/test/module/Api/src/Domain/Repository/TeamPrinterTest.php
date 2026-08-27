<?php

declare(strict_types=1);

/**
 * TeamPrinter repo test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Repository\TeamPrinter as TeamPrinterRepo;

/**
 * TeamPrinter repo test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
final class TeamPrinterTest extends RepositoryTestCase
{
    public function testFetchByDetails(): void
    {
        $this->setUpSut(TeamPrinterRepo::class);

        $command = m::mock(QueryInterface::class)
            ->shouldReceive('getSubCategory')
            ->andReturn(1)
            ->twice()
            ->shouldReceive('getUser')
            ->andReturn(2)
            ->twice()
            ->shouldReceive('getTeam')
            ->andReturn(3)
            ->once()
            ->getMock();

        /** @var QueryBuilder $qb */
        $mockQb = m::mock(QueryBuilder::class);
        $expr = new \Doctrine\ORM\Query\Expr();
        $mockQb->shouldReceive('expr')
            ->zeroOrMoreTimes()
            ->andReturn($expr);

        $mockQb->shouldReceive('andWhere')
            ->times(3)
            ->andReturnSelf();

        $this->em
            ->shouldReceive('getRepository->createQueryBuilder')
            ->once()
            ->andReturn($mockQb);

        $mockQb->shouldReceive('setParameter')->with('subCategory', 1)->once();

        $mockQb->shouldReceive('setParameter')->with('user', 2)->once();

        $mockQb->shouldReceive('setParameter')->with('team', 3)->once();

        $mockQb->shouldReceive('getQuery->getResult')->andReturn(['result']);

        $this->assertSame(['result'], $this->sut->fetchByDetails($command));
    }

    public function testFetchByDetailsNoUserAndSubCategory(): void
    {
        $this->setUpSut(TeamPrinterRepo::class);

        $command = m::mock(QueryInterface::class)
            ->shouldReceive('getSubCategory')
            ->andReturn(null)
            ->once()
            ->shouldReceive('getUser')
            ->andReturn(null)
            ->once()
            ->shouldReceive('getTeam')
            ->andReturn(3)
            ->once()
            ->getMock();

        /** @var QueryBuilder $qb */
        $mockQb = m::mock(QueryBuilder::class);
        $expr = new \Doctrine\ORM\Query\Expr();
        $mockQb->shouldReceive('expr')
            ->zeroOrMoreTimes()
            ->andReturn($expr);

        $mockQb->shouldReceive('andWhere')
            ->times(3)
            ->andReturnSelf();

        $this->em
            ->shouldReceive('getRepository->createQueryBuilder')
            ->once()
            ->andReturn($mockQb);

        $mockQb->shouldReceive('setParameter')->with('team', 3)->once();

        $mockQb->shouldReceive('getQuery->getResult')->andReturn(['result']);

        $this->assertSame(['result'], $this->sut->fetchByDetails($command));
    }

    public function testApplyListJoins(): void
    {
        $this->setUpSut(TeamPrinterRepo::class, true);

        $mockQb = m::mock(QueryBuilder::class);

        $this->sut->shouldReceive('getQueryBuilder')->with()->andReturn($mockQb);
        $mockQb->shouldReceive('modifyQuery')->once()->andReturnSelf();
        $mockQb->shouldReceive('with')->with('subCategory', 'sc')->once()->andReturnSelf();
        $mockQb->shouldReceive('with')->with('user', 'u')->once()->andReturnSelf();
        $mockQb->shouldReceive('with')->with('sc.category', 'scc')->once()->andReturnSelf();
        $mockQb->shouldReceive('with')->with('team', 't')->once()->andReturnSelf();
        $mockQb->shouldReceive('with')->with('u.contactDetails', 'ucd')->once()->andReturnSelf();
        $mockQb->shouldReceive('with')->with('ucd.person', 'ucdp')->once()->andReturnSelf();

        $this->sut->applyListJoins($mockQb);
    }

    public function testApplyListFilters(): void
    {
        $this->setUpSut(TeamPrinterRepo::class, true);

        $query = m::mock(QueryInterface::class)
            ->shouldReceive('getTeam')
            ->andReturn(1)
            ->getMock();

        /** @var QueryBuilder $qb */
        $qb = m::mock(QueryBuilder::class);
        $expr = new \Doctrine\ORM\Query\Expr();
        $qb->shouldReceive('expr')
            ->zeroOrMoreTimes()
            ->andReturn($expr);

        $qb->shouldReceive('andWhere')
            ->times(2)
            ->andReturnSelf();

        $qb->shouldReceive('setParameter')->with('team', 1)->once()->andReturnSelf();

        $qb->shouldReceive('addSelect')->with('CONCAT(ucdp.forename, ucdp.familyName) as HIDDEN userSort')
            ->once()->andReturnSelf();
        $qb->shouldReceive('addSelect')->with('CONCAT(scc.description, sc.subCategoryName) as HIDDEN catSort')
            ->once()->andReturnSelf();
        $qb->shouldReceive('addOrderBy')->with('t.name', 'ASC')->once()->andReturnSelf();
        $qb->shouldReceive('addOrderBy')->with('userSort', 'ASC')->once()->andReturnSelf();
        $qb->shouldReceive('addOrderBy')->with('catSort', 'ASC')->once()->andReturnSelf();

        $this->assertNull($this->sut->applyListFilters($qb, $query));
    }
}
