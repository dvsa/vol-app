<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitSectorQuota;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitSectorQuota as IrhpPermitSectorQuotaEntity;
use Mockery as m;

/**
 * IRHP Permit Sector Quota test
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class IrhpPermitSectorQuotaTest extends RepositoryTestCase
{
    public function setUp(): void
    {
        $this->setUpSut(IrhpPermitSectorQuota::class);
    }

    public function testFetchByNonZeroQuota(): void
    {
        $expectedResult = [
            'sectorId' => 4,
            'quotaNumber' => 160
        ];
        $stockId = 5;

        $queryBuilder = m::mock(QueryBuilder::class);
        $this->em->shouldReceive('createQueryBuilder')->once()->andReturn($queryBuilder);

        $queryBuilder->shouldReceive('select')
            ->with('IDENTITY(ipsq.sector) as sectorId, ipsq.quotaNumber')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('from')
            ->with(IrhpPermitSectorQuotaEntity::class, 'ipsq')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('where')
            ->with('ipsq.quotaNumber > 0')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('andWhere')
            ->with('IDENTITY(ipsq.irhpPermitStock) = ?1')
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
}
