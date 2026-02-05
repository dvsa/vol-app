<?php

declare(strict_types=1);

/**
 * Financial Standing Rate test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityRepository;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Repository\FinancialStandingRate as RateRepo;
use Dvsa\Olcs\Api\Entity\System\FinancialStandingRate;

/**
 * Financial Standing Rate test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class FinancialStandingRateTest extends RepositoryTestCase
{
    public function setUp(): void
    {
        $this->setUpSut(RateRepo::class);
    }

    public function testFetchRatesInEffect(): void
    {
        $date = new \DateTime();

        /** @var QueryBuilder $qb */
        $qb = m::mock(QueryBuilder::class);
        $where1 = m::mock();
        $where2 = m::mock();

        $qb->shouldReceive('expr->isNull')
            ->with('fsr.deletedDate')
            ->andReturn($where1);

        $qb->shouldReceive('expr->lte')
            ->with('fsr.effectiveFrom', ':effectiveFrom')
            ->andReturn($where2);

        $qb
            ->shouldReceive('andWhere')
            ->with($where1)
            ->andReturnSelf()
            ->shouldReceive('andWhere')
            ->with($where2)
            ->andReturnSelf()
            ->shouldReceive('addOrderBy')
            ->with('fsr.effectiveFrom', 'DESC')
            ->andReturnSelf()
            ->shouldReceive('setParameter')
            ->with('effectiveFrom', $date)
            ->shouldReceive('getQuery->execute')
            ->andReturn('RESULT');

        $this->queryBuilder
            ->shouldReceive('withRefdata')
            ->once()
            ->andReturnSelf();

        /** @var EntityRepository $repo */
        $repo = m::mock(EntityRepository::class);
        $repo->shouldReceive('createQueryBuilder')
            ->andReturn($qb);

        $this->em->shouldReceive('getRepository')
            ->with(FinancialStandingRate::class)
            ->andReturn($repo);

        $result = $this->sut->fetchRatesInEffect($date);

        $this->assertEquals('RESULT', $result);
    }

    public function testFetchByCategoryTypeAndDate(): void
    {
        $qb = $this->createMockQb('BLAH');

        $this->mockCreateQueryBuilder($qb);

        $qb->shouldReceive('getQuery')->andReturn(
            m::mock()
                ->shouldReceive('getResult')
                ->andReturn(['RESULTS'])
                ->getMock()
        );

        $results = $this->sut->fetchByCategoryTypeAndDate('lcat_gv', 'ltyp_sn', 'fin_sta_veh_typ_hgv', '2015-09-28');

        $this->assertEquals(['RESULTS'], $results);

        $expectedQuery = 'BLAH AND fsr.goodsOrPsv = [[lcat_gv]] AND '
            . 'fsr.licenceType = [[ltyp_sn]] AND fsr.vehicleType = [[fin_sta_veh_typ_hgv]] AND '
            . 'fsr.effectiveFrom = [[2015-09-28]]';

        $this->assertEquals($expectedQuery, $this->query);
    }
}
