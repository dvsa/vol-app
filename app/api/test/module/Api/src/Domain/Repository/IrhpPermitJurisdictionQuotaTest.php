<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitJurisdictionQuota;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitJurisdictionQuota as IrhpPermitJurisdictionQuotaEntity;
use Mockery as m;

/**
 * IRHP Permit Jurisdiction Quota test
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class IrhpPermitJurisdictionQuotaTest extends RepositoryTestCase
{
    public function setUp(): void
    {
        $this->setUpSut(IrhpPermitJurisdictionQuota::class);
    }

    public function testFetchByNonZeroQuota(): void
    {
        $expectedResult = [
            'jurisdictionId' => 8,
            'quotaNumber' => 320
        ];
        $stockId = 5;

        $queryBuilder = m::mock(QueryBuilder::class);
        $this->em->shouldReceive('createQueryBuilder')->once()->andReturn($queryBuilder);

        $queryBuilder->shouldReceive('select')
            ->with('IDENTITY(ipjq.trafficArea) as jurisdictionId, ipjq.quotaNumber')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('from')
            ->with(IrhpPermitJurisdictionQuotaEntity::class, 'ipjq')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('where')
            ->with('ipjq.quotaNumber > 0')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('andWhere')
            ->with('IDENTITY(ipjq.irhpPermitStock) = ?1')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('setParameter')
            ->with(1, $stockId)
            ->once()
            ->andReturnSelf()
            ->shouldReceive('getQuery->getScalarResult')
            ->once()
            ->andReturn($expectedResult);

        $this->assertEquals(
            $expectedResult,
            $this->sut->fetchByNonZeroQuota($stockId)
        );
    }

    public function testFetchByPermitStockId(): void
    {
        /** @var QueryBuilder $qb */
        $qb = m::mock(QueryBuilder::class);

        $mockJurisdictionQuotaRepo = m::mock();

        $this->em
            ->shouldReceive('getRepository')
            ->andReturn($mockJurisdictionQuotaRepo);

        $mockJurisdictionQuotaRepo
            ->shouldReceive('createQueryBuilder')
            ->andReturn($qb);

        $where = m::mock();

        $qb
            ->shouldReceive('andWhere')
            ->once()
            ->with($where)
            ->andReturnSelf();

        $qb
            ->shouldReceive('expr->eq')
            ->once()
            ->with('m.irhpPermitStock', ':irhpPermitStock')
            ->andReturn($where);

        $qb
            ->shouldReceive('setParameter')
            ->once()
            ->with('irhpPermitStock', '1')
            ->andReturnSelf();

        $result = [
            'jurisdictionId' => 8,
            'quotaNumber' => 320
        ];

        $qb
            ->shouldReceive('getQuery->getResult')
            ->once()
            ->andReturn($result);

        $this->assertEquals(
            $result,
            $this->sut->fetchByIrhpPermitStockId(1)
        );
    }
}
