<?php

declare(strict_types=1);

/**
 * PiTest
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Mockery as m;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Api\Domain\Repository\Pi as PiRepo;
use Dvsa\Olcs\Api\Entity\Pi\Pi as PiEntity;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * PiTest
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
class PiTest extends RepositoryTestCase
{
    public function setUp(): void
    {
        $this->setUpSut(PiRepo::class);
    }

    public function testFetchUsingCase(): void
    {
        $command = m::mock(QueryInterface::class);
        $command->shouldReceive('getId')->andReturn(24);

        $result = m::mock(PiEntity::class);
        $results = [$result];

        /** @var QueryBuilder $qb */
        $qb = m::mock(QueryBuilder::class);
        $qb->shouldReceive('expr->eq')->with('m.case', ':byId')->once()->andReturn('EXPR');
        $qb->shouldReceive('andWhere')->with('EXPR')->once()->andReturnSelf();
        $qb->shouldReceive('setParameter')->with('byId', 24)->once()->andReturnSelf();
        $qb->shouldReceive('getQuery->getResult')->with(Query::HYDRATE_OBJECT)->andReturn($results);

        $this->queryBuilder->shouldReceive('modifyQuery')
            ->once()->with($qb)->andReturnSelf()
            ->shouldReceive('withRefdata')->once()->andReturnSelf()
            ->shouldReceive('with')->once()->with('agreedByTc')->andReturnSelf()
            ->shouldReceive('with')->once()->with('assignedTo')->andReturnSelf()
            ->shouldReceive('with')->once()->with('decidedByTc')->andReturnSelf()
            ->shouldReceive('with')->once()->with('reasons')->andReturnSelf()
            ->shouldReceive('with')->once()->with('decisions')->andReturnSelf()
            ->shouldReceive('with')->once()->with('tmDecisions')->andReturnSelf()
            ->shouldReceive('with')->once()->with('piHearings')->andReturnSelf()
            ->shouldReceive('with')->once()->with('case', 'c')->andReturnSelf()
            ->shouldReceive('with')->once()->with('c.transportManager')->andReturnSelf()
            ->shouldReceive('with')->once()->with('piSlaExceptions', 's')->andReturnSelf()
            ->shouldReceive('withCreatedBy')->once()->withNoArgs()->andReturnSelf();

        /** @var EntityRepository $repo */
        $repo = m::mock(EntityRepository::class);
        $repo->shouldReceive('createQueryBuilder')
            ->with('m')
            ->andReturn($qb);

        $this->em->shouldReceive('getRepository')
            ->with(PiEntity::class)
            ->andReturn($repo)
            ->shouldReceive('lock')
            ->with($result, LockMode::OPTIMISTIC, 1);

        $this->sut->fetchUsingCase($command, Query::HYDRATE_OBJECT);
    }
}
