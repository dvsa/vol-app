<?php

declare(strict_types=1);

/**
 * IrfoPsvAuth Repo test
 */

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Mockery as m;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityRepository;
use Dvsa\Olcs\Api\Entity\Irfo\IrfoPsvAuth as Entity;
use Dvsa\Olcs\Api\Domain\Repository\IrfoPsvAuth as Repo;
use Dvsa\Olcs\Transfer\Query\Irfo\IrfoPsvAuthList as IrfoPsvAuthListQry;
use Dvsa\Olcs\Transfer\Query\Irfo\IrfoPsvAuthContinuationList as IrfoPsvAuthContinuationListQry;

/**
 * IrfoPsvAuth Repo test
 */
class IrfoPsvAuthTest extends RepositoryTestCase
{
    public function setUp(): void
    {
        $this->setUpSut(Repo::class);
    }

    public function testFetchById(): void
    {
        $id = 24;
        $mockResult = [0 => 'result'];

        /** @var QueryBuilder $qb */
        $qb = m::mock(QueryBuilder::class);

        $this->queryBuilder->shouldReceive('modifyQuery')
            ->once()
            ->with($qb)
            ->andReturnSelf()
            ->shouldReceive('withRefdata')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('with')
            ->once()
            ->with('irfoPsvAuthType')
            ->andReturnSelf()
            ->shouldReceive('with')
            ->once()
            ->with('irfoPsvAuthNumbers')
            ->andReturnSelf()
            ->shouldReceive('with')
            ->once()
            ->with('countrys')
            ->andReturnSelf()
            ->shouldReceive('byId')
            ->once()
            ->with($id);

        $qb->shouldReceive('getQuery->getResult')
            ->with(Query::HYDRATE_OBJECT)
            ->andReturn($mockResult);

        /** @var EntityRepository $repo */
        $repo = m::mock(EntityRepository::class);
        $repo->shouldReceive('createQueryBuilder')
            ->with('m')
            ->andReturn($qb);

        $this->em->shouldReceive('getRepository')
            ->with(Entity::class)
            ->andReturn($repo);

        $result = $this->sut->fetchById($id);

        $this->assertEquals($result, $mockResult[0]);
    }

    public function testFetchByOrganisation(): void
    {
        $qb = $this->createMockQb('BLAH');

        $this->mockCreateQueryBuilder($qb);

        $qb->shouldReceive('getQuery')->andReturn(
            m::mock()->shouldReceive('execute')
                ->shouldReceive('getResult')
                ->andReturn(['RESULTS'])
                ->getMock()
        );
        $this->assertEquals(['RESULTS'], $this->sut->fetchByOrganisation('ORG1'));

        $expectedQuery = 'BLAH AND m.organisation = [[ORG1]]';
        $this->assertEquals($expectedQuery, $this->query);
    }

    public function testFetchList(): void
    {
        $orgId = 12;

        $this->setUpSut(Repo::class, true);
        $this->sut->shouldReceive('fetchPaginatedList')->andReturn(['RESULTS']);

        $qb = $this->createMockQb('BLAH');
        $this->mockCreateQueryBuilder($qb);

        $this->queryBuilder
            ->shouldReceive('modifyQuery')->with($qb)->andReturnSelf()
            ->shouldReceive('withRefdata')->once()->andReturnSelf()
            ->shouldReceive('with')->with('irfoPsvAuthType')->once()->andReturnSelf()
            ->shouldReceive('paginate')->once()->andReturnSelf();

        $query = IrfoPsvAuthListQry::create(['organisation' => $orgId]);
        $this->assertEquals(['RESULTS'], $this->sut->fetchList($query));

        $expectedQuery = 'BLAH AND m.organisation = [[' . $orgId . ']]';
        $this->assertEquals($expectedQuery, $this->query);
    }

    public function testFetchListForContinuation(): void
    {
        $year = 2016;
        $month = 12;

        $this->setUpSut(Repo::class, true);
        $this->sut->shouldReceive('fetchPaginatedList')->andReturn(['RESULTS']);

        $qb = $this->createMockQb('BLAH');
        $this->mockCreateQueryBuilder($qb);

        $this->queryBuilder
            ->shouldReceive('modifyQuery')->with($qb)->andReturnSelf()
            ->shouldReceive('withRefdata')->once()->andReturnSelf()
            ->shouldReceive('with')->with('organisation', 'o')->once()->andReturnSelf()
            ->shouldReceive('paginate')->once()->andReturnSelf();

        $query = IrfoPsvAuthContinuationListQry::create(['year' => $year, 'month' => $month]);
        $this->assertEquals(['RESULTS'], $this->sut->fetchList($query));

        $expectedQuery = 'BLAH '
            . 'AND m.expiryDate >= [[2016-12-01T00:00:00+00:00]] '
            . 'AND m.expiryDate < [[2017-01-01T00:00:00+00:00]] '
            . 'AND m.status IN ['
                . '"' . Entity::STATUS_APPROVED . '",'
                . '"' . Entity::STATUS_GRANTED . '",'
                . '"' . Entity::STATUS_PENDING . '",'
                . '"' . Entity::STATUS_RENEW . '"'
            . '] '
            . 'AND o.isIrfo = [[true]]';
        $this->assertEquals($expectedQuery, $this->query);
    }
}
