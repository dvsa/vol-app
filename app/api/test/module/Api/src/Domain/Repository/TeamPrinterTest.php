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
class TeamPrinterTest extends RepositoryTestCase
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

        $this->em
            ->shouldReceive('getRepository->createQueryBuilder')
            ->once()
            ->andReturn($mockQb);

        $mockQb->shouldReceive('expr->eq')->with('m.subCategory', ':subCategory')->once()->andReturn('EXPR');
        $mockQb->shouldReceive('andWhere')->with('EXPR')->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')->with('subCategory', 1)->once();

        $mockQb->shouldReceive('expr->eq')->with('m.user', ':user')->once()->andReturn('EXPR1');
        $mockQb->shouldReceive('andWhere')->with('EXPR1')->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')->with('user', 2)->once();

        $mockQb->shouldReceive('expr->eq')->with('m.team', ':team')->once()->andReturn('EXPR3');
        $mockQb->shouldReceive('andWhere')->with('EXPR3')->once()->andReturnSelf();
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

        $this->em
            ->shouldReceive('getRepository->createQueryBuilder')
            ->once()
            ->andReturn($mockQb);

        $mockQb->shouldReceive('expr->isNull')->with('m.subCategory')->once()->andReturn('EXPR');
        $mockQb->shouldReceive('andWhere')->with('EXPR')->once()->andReturnSelf();

        $mockQb->shouldReceive('expr->isNull')->with('m.user')->once()->andReturn('EXPR1');
        $mockQb->shouldReceive('andWhere')->with('EXPR1')->once()->andReturnSelf();

        $mockQb->shouldReceive('expr->eq')->with('m.team', ':team')->once()->andReturn('EXPR3');
        $mockQb->shouldReceive('andWhere')->with('EXPR3')->once()->andReturnSelf();
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

        $qb->shouldReceive('expr->eq')->with('m.team', ':team')->once()->andReturn('team');
        $qb->shouldReceive('andWhere')->with('team')->once()->andReturnSelf();
        $qb->shouldReceive('setParameter')->with('team', 1)->once()->andReturnSelf();

        $qb->shouldReceive('expr->isNull')->with('sc.id')->once()->andReturn('andX1');
        $qb->shouldReceive('expr->isNull')->with('u.id')->once()->andReturn('andX2');
        $qb->shouldReceive('expr->andX')->with('andX1', 'andX2')->once()->andReturn('andExpr');
        $qb->shouldReceive('expr->not')->with('andExpr')->once()->andReturn('notExpr');
        $qb->shouldReceive('andWhere')->with('notExpr')->once()->andReturnSelf();

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
