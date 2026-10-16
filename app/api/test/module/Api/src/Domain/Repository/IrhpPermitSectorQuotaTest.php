<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitSectorQuota as Repo;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitSectorQuota as Entity;

final class IrhpPermitSectorQuotaTest extends RepositoryTestCase
{
    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(Repo::class, true);
    }

    /** A sector with no permits is excluded rather than returned as a zero. */
    public function testFetchByNonZeroQuota(): void
    {
        $quotas = [['sectorId' => 4, 'quotaNumber' => 160]];

        $qb = $this->newRealQb();
        $qb->stubbedQuery()->expects('getScalarResult')->withNoArgs()->andReturn($quotas);
        $this->em->expects('createQueryBuilder')->withNoArgs()->andReturn($qb);

        $this->assertSame($quotas, $this->sut->fetchByNonZeroQuota(5));

        $this->assertSame(
            'SELECT IDENTITY(ipsq.sector) as sectorId, ipsq.quotaNumber'
            . ' FROM ' . Entity::class . ' ipsq'
            . ' WHERE ipsq.quotaNumber > 0 AND IDENTITY(ipsq.irhpPermitStock) = ?1',
            $qb->getDQL(),
        );
        $this->assertSame(5, $qb->getParameter(1)->getValue());
    }
}
