<?php

declare(strict_types=1);

/**
 * LicenceStatusRuleTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Repository\LicenceStatusRule as Repo;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityRepository;
use Doctrine\DBAL\LockMode;

/**
 * LicenceStatusRuleTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class LicenceStatusRuleTest extends RepositoryTestCase
{
    public function setUp(): void
    {
        $this->setUpSut(Repo::class);
    }

    public function testFetchRevokeCurtailSuspend(): void
    {
        $mockQb = m::mock(QueryBuilder::class);
        $date = new \DateTime();

        $this->em->shouldReceive('getRepository->createQueryBuilder')->with('lsr')->once()->andReturn($mockQb);
        $this->queryBuilder->shouldReceive('modifyQuery')->with($mockQb)->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('withRefdata')->with()->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('licenceStatus')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('licence', 'l')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('l.status')->once()->andReturnSelf();

        $mockQb->shouldReceive('expr->isNull')->with('lsr.startProcessedDate')->once()->andReturn('EXPR1');
        $mockQb->shouldReceive('andWhere')->with('EXPR1')->once()->andReturnSelf();
        $mockQb->shouldReceive('expr->isNull')->with('lsr.deletedDate')->once()->andReturn('EXPR2');
        $mockQb->shouldReceive('andWhere')->with('EXPR2')->once()->andReturnSelf();
        $mockQb->shouldReceive('expr->lte')->with('lsr.startDate', ':startDate')->once()->andReturn('EXPR3');
        $mockQb->shouldReceive('andWhere')->with('EXPR3')->once()->andReturnSelf();

        $mockQb->shouldReceive('setParameter')->with('startDate', $date)->once();

        $mockQb->shouldReceive('getQuery->getResult')->with()->once()->andReturn('RESULT');

        $this->assertSame('RESULT', $this->sut->fetchRevokeCurtailSuspend($date));
    }

    public function testFetchToValid(): void
    {
        $mockQb = m::mock(QueryBuilder::class);
        $date = new \DateTime();

        $this->em->shouldReceive('getRepository->createQueryBuilder')->with('lsr')->once()->andReturn($mockQb);
        $this->queryBuilder->shouldReceive('modifyQuery')->with($mockQb)->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('withRefdata')->with()->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('licenceStatus')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('licence', 'l')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('l.status')->once()->andReturnSelf();

        $mockQb->shouldReceive('expr->isNull')->with('lsr.endProcessedDate')->once()->andReturn('EXPR1');
        $mockQb->shouldReceive('andWhere')->with('EXPR1')->once()->andReturnSelf();
        $mockQb->shouldReceive('expr->isNotNull')->with('lsr.endDate')->once()->andReturn('EXPR2');
        $mockQb->shouldReceive('andWhere')->with('EXPR2')->once()->andReturnSelf();
        $mockQb->shouldReceive('expr->isNull')->with('lsr.deletedDate')->once()->andReturn('EXPR3');
        $mockQb->shouldReceive('andWhere')->with('EXPR3')->once()->andReturnSelf();
        $mockQb->shouldReceive('expr->lte')->with('lsr.endDate', ':endDate')->once()->andReturn('EXPR4');
        $mockQb->shouldReceive('andWhere')->with('EXPR4')->once()->andReturnSelf();

        $mockQb->shouldReceive('setParameter')->with('endDate', $date)->once();

        $mockQb->shouldReceive('getQuery->getResult')->with()->once()->andReturn('RESULT');

        $this->assertSame('RESULT', $this->sut->fetchToValid($date));
    }

    public function testApplyListJoins(): void
    {
        $this->setUpSut(Repo::class, true);

        $mockQb = m::mock(QueryBuilder::class);
        $mockQb->shouldReceive('modifyQuery')->once()->andReturnSelf();

        $this->sut->shouldReceive('getQueryBuilder')->with()->andReturn($mockQb);

        $mockQb->shouldReceive('with')->with('lsr.licence', 'l')->once()->andReturnSelf();
        $mockQb->shouldReceive('with')->with('l.decisions', 'd')->once()->andReturnSelf();

        $this->sut->applyFetchJoins($mockQb);
    }
}
