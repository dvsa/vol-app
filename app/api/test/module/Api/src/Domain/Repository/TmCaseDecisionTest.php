<?php

/**
 * TmCaseDecision Repo test
 */

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Mockery as m;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityRepository;
use Dvsa\Olcs\Api\Entity\Tm\TmCaseDecision;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Api\Domain\Repository\TmCaseDecision as Repo;

/**
 * TmCaseDecision Repo test
 */
class TmCaseDecisionTest extends RepositoryTestCase
{
    public function setUp(): void
    {
        $this->setUpSut(Repo::class);
    }

    public function dpFetchLatestUsingCaseDataProvider()
    {
        return [
            'Decision exists' => [
                'exepected' => 'result',
                'mockResult' => [0 => 'result']
            ],
            'Decision does not exist' => [
                'exepected' => false ,
                'mockResult' => []
            ],
        ];
    }

    /**
     * @dataProvider dpFetchLatestUsingCaseDataProvider
     */
    public function testFetchLatestUsingCase($expected, $mockResult)
    {
        $case = 24;

        $command = m::mock(QueryInterface::class);
        $command->shouldReceive('getCase')
            ->andReturn($case);

        /** @var Expr $expr */
        $expr = m::mock(QueryBuilder::class);
        $expr->shouldReceive('eq')
            ->with(m::type('string'), ':byCase')
            ->andReturnSelf();

        /** @var QueryBuilder $qb */
        $qb = m::mock(QueryBuilder::class);

        $qb->shouldReceive('expr')
            ->andReturn($expr);

        $qb->shouldReceive('setParameter')
            ->with('byCase', $case)
            ->andReturnSelf();

        $qb->shouldReceive('andWhere')
            ->with($expr)
            ->andReturnSelf();

        $qb->shouldReceive('orderBy')
            ->with(m::type('string'), 'DESC')
            ->andReturnSelf();

        $this->queryBuilder->shouldReceive('modifyQuery')
            ->once()
            ->with($qb)
            ->andReturnSelf()
            ->shouldReceive('withRefdata')
            ->once()
            ->andReturnSelf();

        $qb->shouldReceive('getQuery->getResult')
            ->with(Query::HYDRATE_OBJECT)
            ->andReturn($mockResult);

        /** @var EntityRepository $repo */
        $repo = m::mock(EntityRepository::class);
        $repo->shouldReceive('createQueryBuilder')
            ->with('m')
            ->andReturn($qb);

        $this->em->shouldReceive('getRepository')
            ->with(TmCaseDecision::class)
            ->andReturn($repo);

        $result = $this->sut->fetchLatestUsingCase($command, Query::HYDRATE_OBJECT);

        $this->assertEquals($expected, $result);
    }
}
