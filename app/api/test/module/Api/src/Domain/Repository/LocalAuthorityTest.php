<?php

declare(strict_types=1);

/**
 * Local Authority test
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Repository\LocalAuthority as LocalAuthorityRepo;
use Dvsa\Olcs\Api\Entity\Bus\LocalAuthority as LocalAuthorityEntity;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityRepository;

/**
 * Local Authority test
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class LocalAuthorityTest extends RepositoryTestCase
{
    public function setUp(): void
    {
        $this->setUpSut(LocalAuthorityRepo::class, true);
    }

    /**
     * Tests fetchByTxcName
     */
    public function testFetchByTxcName(): void
    {
        $mockResult = ['result'];
        $txcNames = ['name1', 'name2'];

        /** @var Expr $expr */
        $expr = m::mock(QueryBuilder::class);
        $expr->shouldReceive('in')
            ->with('m.txcName', $txcNames)
            ->andReturnSelf();

        $qb = $this->getQueryBuilder($expr, $mockResult);

        /** @var EntityRepository $repo */
        $repo = m::mock(EntityRepository::class);
        $repo->shouldReceive('createQueryBuilder')
            ->with('m')
            ->andReturn($qb);

        $this->em->shouldReceive('getRepository')
            ->with(LocalAuthorityEntity::class)
            ->andReturn($repo);

        $result = $this->sut->fetchByTxcName($txcNames);

        $this->assertEquals($result, $mockResult);
    }

    /**
     * Tests fetchByNaptan
     */
    public function testFetchByNaptan(): void
    {
        $mockResult = ['result'];
        $naptan = ['naptan1', 'naptan2'];

        /** @var Expr $expr */
        $expr = m::mock(QueryBuilder::class);
        $expr->shouldReceive('in')
            ->with('m.naptanCode', $naptan)
            ->andReturnSelf();

        $qb = $this->getQueryBuilder($expr, $mockResult);

        /** @var EntityRepository $repo */
        $repo = m::mock(EntityRepository::class);
        $repo->shouldReceive('createQueryBuilder')
            ->with('m')
            ->andReturn($qb);

        $this->em->shouldReceive('getRepository')
            ->with(LocalAuthorityEntity::class)
            ->andReturn($repo);

        $result = $this->sut->fetchByNaptan($naptan);

        $this->assertEquals($result, $mockResult);
    }

    /**
     * Gets a query builder with expression
     *
     * @param $expr
     * @param $mockResult
     * @return QueryBuilder
     */
    public function getQueryBuilder(mixed $expr, mixed $mockResult): mixed
    {
        /** @var QueryBuilder $qb */
        $qb = m::mock(QueryBuilder::class);

        $qb->shouldReceive('expr')
            ->andReturn($expr);

        $qb->shouldReceive('andWhere')
            ->with($expr)
            ->andReturnSelf();

        $this->queryBuilder->shouldReceive('modifyQuery')
            ->once()
            ->with($qb)
            ->andReturnSelf();

        $qb->shouldReceive('getQuery->execute')
            ->andReturn($mockResult);

        return $qb;
    }
}
