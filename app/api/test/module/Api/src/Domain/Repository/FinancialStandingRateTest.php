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

    #[\PHPUnit\Framework\Attributes\DataProvider('priorQueryProvider')]
    public function testFetchRatesInEffect(bool $previouslyBound): void
    {
        $date = new \DateTime();
        if ($previouslyBound) {
            $previous = $this->newRealQb();
            $previous->select('c')->from(Cases::class, 'c');
            $this->queryBuilder->modifyQuery($previous);
        }

        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('execute')->withNoArgs()->andReturn('RESULT');

        $this->assertSame('RESULT', $this->sut->fetchRatesInEffect($date));

        $this->assertSame(
            'SELECT fsr, w0, w1, w2 FROM ' . Entity::class . ' fsr'
            . ' LEFT JOIN fsr.licenceType w0 LEFT JOIN fsr.goodsOrPsv w1'
            . ' LEFT JOIN fsr.vehicleType w2'
            . ' WHERE fsr.deletedDate IS NULL AND fsr.effectiveFrom <= :effectiveFrom'
            . ' ORDER BY fsr.effectiveFrom DESC',
            $qb->getDQL(),
        );
        $this->assertSame($date, $qb->getParameter('effectiveFrom')->getValue());
        if ($previouslyBound) {
            $this->assertSame('SELECT c FROM ' . Cases::class . ' c', $previous->getDQL());
        }
    }

    public static function priorQueryProvider(): \Iterator
    {
        yield 'first use' => [false];
        yield 'after another query' => [true];
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
