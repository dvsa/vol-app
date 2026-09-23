<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Dvsa\Olcs\Api\Domain\Repository\FinancialStandingRate as RateRepo;
use Dvsa\Olcs\Api\Entity\Cases\Cases;
use Dvsa\Olcs\Api\Entity\System\FinancialStandingRate as Entity;

final class FinancialStandingRateTest extends RepositoryTestCase
{
    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(RateRepo::class);
    }

    /**
     * Pins a known defect rather than blessing it: fetchRatesInEffect() calls
     * $this->getQueryBuilder()->withRefdata() with no modifyQuery($qb), and the helper is a
     * shared service. So the refdata joins land on whichever builder the helper last held,
     * and the rate query gets none of them. Fixing the repository will fail this test, which
     * is the intent — see the migration findings.
     */
    public function testFetchRatesInEffectLosesItsRefdataJoinsToTheSharedHelper(): void
    {
        $date = new \DateTime();

        // Stand in for any earlier repository call in the same request. Without one the helper
        // is cold and the method throws RuntimeException('Doctrine Query Builder is not set').
        $strayBuilder = $this->newRealQb();
        $strayBuilder->select('c')->from(Cases::class, 'c');
        $this->queryBuilder->modifyQuery($strayBuilder);

        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('execute')->withNoArgs()->andReturn('RESULT');

        $this->assertSame('RESULT', $this->sut->fetchRatesInEffect($date));

        $this->assertSame(
            'SELECT fsr FROM ' . Entity::class . ' fsr'
            . ' WHERE fsr.deletedDate IS NULL AND fsr.effectiveFrom <= :effectiveFrom'
            . ' ORDER BY fsr.effectiveFrom DESC',
            $qb->getDQL(),
        );
        $this->assertSame($date, $qb->getParameter('effectiveFrom')->getValue());

        $this->assertStringNotContainsString('LEFT JOIN', $qb->getDQL());
        $this->assertStringContainsString('LEFT JOIN', $strayBuilder->getDQL());
    }

    public function testFetchByCategoryTypeAndDate(): void
    {
        $qb = $this->createRealQb()->willReturn(['RESULTS']);

        $this->assertSame(
            ['RESULTS'],
            $this->sut->fetchByCategoryTypeAndDate('lcat_gv', 'ltyp_sn', 'fin_sta_veh_typ_hgv', '2015-09-28'),
        );

        $this->assertSame(
            'SELECT fsr FROM ' . Entity::class . ' fsr'
            . ' WHERE fsr.goodsOrPsv = :goodsOrPsv AND fsr.licenceType = :licenceType'
            . ' AND fsr.vehicleType = :vehicleType AND fsr.effectiveFrom = :date',
            $qb->getDQL(),
        );
        $this->assertSame('lcat_gv', $qb->getParameter('goodsOrPsv')->getValue());
        $this->assertSame('ltyp_sn', $qb->getParameter('licenceType')->getValue());
        $this->assertSame('fin_sta_veh_typ_hgv', $qb->getParameter('vehicleType')->getValue());
        $this->assertSame('2015-09-28', $qb->getParameter('date')->getValue());
    }
}
