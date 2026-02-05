<?php

declare(strict_types=1);

/**
 * CompaniesHouseCompany test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\Repository\CompaniesHouseCompany as CompaniesHouseCompanyRepo;
use Dvsa\Olcs\Api\Entity\CompaniesHouse\CompaniesHouseCompany as CompanyEntity;
use Mockery as m;

/**
 * CompaniesHouseCompany test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class CompaniesHouseCompanyTest extends RepositoryTestCase
{
    public function setUp(): void
    {
        $this->setUpSut(CompaniesHouseCompanyRepo::class);
    }

    public function testGetLatestByCompanyNumber(): void
    {
        $companyNumber = '01234567';

        $result = m::mock(CompanyEntity::class);
        $results = [$result];

        /** @var QueryBuilder $qb */
        $qb = m::mock(QueryBuilder::class);

        $where = m::mock();
        $qb->shouldReceive('expr->eq')
            ->with('cc.companyNumber', ':companyNumber')
            ->andReturn($where);
        $qb
            ->shouldReceive('andWhere')
            ->with($where)
            ->once()
            ->andReturnSelf()
            ->shouldReceive('setParameter')
            ->with('companyNumber', $companyNumber)
            ->once()
            ->andReturnSelf()
            ->shouldReceive('setMaxResults')
            ->once()
            ->with(1)
            ->andReturnSelf();

        $this->queryBuilder->shouldReceive('modifyQuery')
            ->once()
            ->with($qb)
            ->andReturnSelf()
            ->shouldReceive('withRefdata')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('order')
            ->with('createdOn', 'DESC')
            ->andReturnSelf();

        $qb->shouldReceive('getQuery->getResult')
            ->andReturn($results);

        /** @var EntityRepository $repo */
        $repo = m::mock(EntityRepository::class);
        $repo->shouldReceive('createQueryBuilder')
            ->andReturn($qb);

        $this->em->shouldReceive('getRepository')
            ->with(CompanyEntity::class)
            ->andReturn($repo);

        $this->sut->getLatestByCompanyNumber($companyNumber);
    }

    public function testGetLatesByCompanyNumberNotFound(): void
    {
        $companyNumber = '01234567';
        $results = [];

        /** @var QueryBuilder $qb */
        $qb = m::mock(QueryBuilder::class);

        $qb->shouldReceive('expr->eq');
        $qb
            ->shouldReceive('andWhere')
            ->andReturnSelf()
            ->shouldReceive('setParameter')
            ->andReturnSelf()
            ->shouldReceive('setMaxResults')
            ->andReturnSelf();

        $this->queryBuilder->shouldReceive('modifyQuery')
            ->andReturnSelf()
            ->shouldReceive('withRefdata')
            ->andReturnSelf()
            ->shouldReceive('order')
            ->andReturnSelf();

        $qb->shouldReceive('getQuery->getResult')
            ->andReturn($results);

        /** @var EntityRepository $repo */
        $repo = m::mock(EntityRepository::class);
        $repo->shouldReceive('createQueryBuilder')
            ->andReturn($qb);

        $this->em->shouldReceive('getRepository')
            ->with(CompanyEntity::class)
            ->andReturn($repo);

        $this->expectException(NotFoundException::class);

        $this->sut->getLatestByCompanyNumber($companyNumber);
    }
}
