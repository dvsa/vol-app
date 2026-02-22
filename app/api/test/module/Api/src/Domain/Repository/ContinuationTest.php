<?php

declare(strict_types=1);

/**
 * Continuation test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Mockery as m;
use Dvsa\Olcs\Api\Domain\Repository\Continuation as Repo;
use Doctrine\ORM\QueryBuilder;

/**
 * Continuation test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class ContinuationTest extends RepositoryTestCase
{
    public function setUp(): void
    {
        $this->setUpSut(Repo::class);
    }

    public function testFetchWithTa(): void
    {
        $qb = m::mock(QueryBuilder::class);

        $this->queryBuilder->shouldReceive('modifyQuery')->with($qb)->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('withRefdata')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('trafficArea', 'ta')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('byId')->with(1)->once()->andReturnSelf();

        $this->em->shouldReceive('getRepository->createQueryBuilder')->with('m')->once()->andReturn($qb);
        $qb->shouldReceive('getQuery->getSingleResult')->once()->andReturn(['result']);
        $this->assertEquals($this->sut->fetchWithTa(1), ['result']);
    }

    public function testFetchContinuation(): void
    {
        $qb = m::mock(QueryBuilder::class);

        $this->queryBuilder->shouldReceive('modifyQuery')->with($qb)->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('withRefdata')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('trafficArea', 'ta')->once()->andReturnSelf();

        $qb->shouldReceive('expr->eq')->with('m.month', ':month')->once()->andReturn('conditionMonth');
        $qb->shouldReceive('andWhere')->with('conditionMonth')->once()->andReturnSelf();
        $qb->shouldReceive('setParameter')->with('month', 1)->once()->andReturnSelf();

        $qb->shouldReceive('expr->eq')->with('m.year', ':year')->once()->andReturn('conditionYear');
        $qb->shouldReceive('andWhere')->with('conditionYear')->once()->andReturnSelf();
        $qb->shouldReceive('setParameter')->with('year', 2015)->once()->andReturnSelf();

        $qb->shouldReceive('expr->eq')->with('ta.id', ':trafficArea')->once()->andReturn('conditionTa');
        $qb->shouldReceive('andWhere')->with('conditionTa')->once()->andReturnSelf();
        $qb->shouldReceive('setParameter')->with('trafficArea', 'B')->once()->andReturnSelf();

        $this->em->shouldReceive('getRepository->createQueryBuilder')->with('m')->once()->andReturn($qb);
        $qb->shouldReceive('getQuery->getResult')->once()->andReturn(['result']);
        $this->assertEquals($this->sut->fetchContinuation(1, 2015, 'B'), ['result']);
    }
}
