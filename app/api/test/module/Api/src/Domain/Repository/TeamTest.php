<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Api\Domain\Query\Team\TeamListByTrafficArea;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Repository\Team as TeamRepo;

/**
 * @see TeamRepo
 */
class TeamTest extends RepositoryTestCase
{
    public function setUp(): void
    {
        $this->setUpSut(TeamRepo::class);
    }

    public function testApplyTrafficAreaListFilterApplied(): void
    {
        $this->setUpSut(TeamRepo::class, true);
        $trafficAreas = ['A', 'B'];
        $queryData = ['trafficAreas' => $trafficAreas];
        $query = TeamListByTrafficArea::create($queryData);

        $expression = 'expr';
        $queryBuilder = m::mock(QueryBuilder::class);
        $queryBuilder->expects('expr->in')->with('m.trafficArea', ':byTrafficAreas')->andReturn($expression);
        $queryBuilder->expects('setParameter')->with('byTrafficAreas', $trafficAreas);
        $queryBuilder->expects('andWhere')->with($expression)->andReturnSelf();

        $this->sut->applyListFilters($queryBuilder, $query);
    }

    public function testFetchByName(): void
    {
        $name = 'foo';

        /** @var QueryBuilder $qb */
        $mockQb = m::mock(QueryBuilder::class);

        $this->em
            ->shouldReceive('getRepository->createQueryBuilder')
            ->once()
            ->andReturn($mockQb);

        $mockQb->shouldReceive('expr->eq')->with('m.name', ':name')->once()->andReturn('EXPR');
        $mockQb->shouldReceive('andWhere')->with('EXPR')->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')->with('name', $name)->once();

        $mockQb->shouldReceive('getQuery->getResult')->andReturn(['result']);

        $this->assertSame(['result'], $this->sut->fetchByName($name));
    }

    public function testFetchWithPrinters(): void
    {
        $id = 1;

        $mockQb = m::mock(QueryBuilder::class);
        $this->em
            ->shouldReceive('getRepository->createQueryBuilder')
            ->once()
            ->andReturn($mockQb);

        $this->queryBuilder->shouldReceive('modifyQuery')
            ->once()
            ->with($mockQb)
            ->andReturnSelf()
            ->shouldReceive('withRefdata')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('byId')
            ->with($id)
            ->once()
            ->andReturnSelf()
            ->shouldReceive('with')
            ->with('teamPrinters', 'tp')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('with')
            ->with('tp.printer', 'tpp')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('with')
            ->with('tp.user', 'pu')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('with')
            ->with('tp.subCategory', 'ps')
            ->once()
            ->andReturnSelf();

        $mockQb->shouldReceive('getQuery->getSingleResult')->andReturn('result');

        $this->assertSame('result', $this->sut->fetchWithPrinters($id, 1));
    }
}
