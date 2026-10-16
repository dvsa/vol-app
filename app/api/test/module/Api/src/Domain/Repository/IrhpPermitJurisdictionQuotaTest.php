<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitJurisdictionQuota as Repo;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitJurisdictionQuota as Entity;

final class IrhpPermitJurisdictionQuotaTest extends RepositoryTestCase
{
    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(Repo::class, true);
    }

    /**
     * Scoring only cares about jurisdictions that actually have permits, so a zero quota is
     * excluded rather than returned as a zero.
     */
    public function testFetchByNonZeroQuota(): void
    {
        $quotas = [['jurisdictionId' => 8, 'quotaNumber' => 320]];

        $qb = $this->newRealQb();
        $qb->stubbedQuery()->expects('getScalarResult')->withNoArgs()->andReturn($quotas);
        $this->em->expects('createQueryBuilder')->withNoArgs()->andReturn($qb);

        $this->assertSame($quotas, $this->sut->fetchByNonZeroQuota(5));

        $this->assertSame(
            'SELECT IDENTITY(ipjq.trafficArea) as jurisdictionId, ipjq.quotaNumber'
            . ' FROM ' . Entity::class . ' ipjq'
            . ' WHERE ipjq.quotaNumber > 0 AND IDENTITY(ipjq.irhpPermitStock) = ?1',
            $qb->getDQL(),
        );
        $this->assertSame(5, $qb->getParameter(1)->getValue());
    }

    public function testFetchByIrhpPermitStockId(): void
    {
        $qb = $this->createRealQb()->willReturn(['RESULTS']);

        $this->assertSame(['RESULTS'], $this->sut->fetchByIrhpPermitStockId(1));

        $this->assertSame(
            'SELECT m FROM ' . Entity::class . ' m WHERE m.irhpPermitStock = :irhpPermitStock',
            $qb->getDQL(),
        );
        $this->assertSame(1, $qb->getParameter('irhpPermitStock')->getValue());
    }
}
