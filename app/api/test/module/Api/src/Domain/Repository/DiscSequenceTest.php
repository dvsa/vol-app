<?php

declare(strict_types=1);

/**
 * Disc Sequence test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Mockery as m;
use Dvsa\Olcs\Api\Domain\Repository\DiscSequence as DiscSequenceRepo;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea as TrafficAreaEntity;

/**
 * Disc Sequence test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class DiscSequenceTest extends RepositoryTestCase
{
    public function setUp(): void
    {
        $this->setUpSut(DiscSequenceRepo::class);
    }

    public function testFetchDiscPrefixesNi(): void
    {
        $mockQb = m::mock(QueryBuilder::class);
        $mockQb->shouldReceive('expr->isNotNull')->with('ta.id')->once()->andReturn('condition1');
        $mockQb->shouldReceive('expr->eq')->with('ta.id', ':taId')->once()->andReturn('condition2');
        $mockQb->shouldReceive('expr->andX')->with('condition1', 'condition2')->once()->andReturn('conditionAnd');
        $mockQb->shouldReceive('andWhere')->with('conditionAnd')->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')
            ->with('taId', TrafficAreaEntity::NORTHERN_IRELAND_TRAFFIC_AREA_CODE)
            ->once()
            ->andReturnSelf();

        $this->queryBuilder->shouldReceive('with')->with('trafficArea', 'ta')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('goodsOrPsv', 'gp')->once()->getMock();
        $this->queryBuilder->shouldReceive('modifyQuery')->with($mockQb)->once()->andReturnSelf();

        $this->em->shouldReceive('getRepository->createQueryBuilder')->with('ds')->once()->andReturn($mockQb);
        $mockQb->shouldReceive('getQuery->getResult')->once()->andReturn(['result']);

        $this->sut->fetchDiscPrefixes('Y', null);
    }

    public function testFetchDiscPrefixes(): void
    {
        $mockQb = m::mock(QueryBuilder::class);
        $mockQb->shouldReceive('expr->isNotNull')->with('ta.id')->once()->andReturn('condition1');
        $mockQb->shouldReceive('expr->neq')->with('ta.id', ':taId')->once()->andReturn('condition2');
        $mockQb->shouldReceive('expr->eq')->with('gp.id', ':operatorType')->once()->andReturn('condition3');
        $mockQb->shouldReceive('expr->andX')
            ->with('condition1', 'condition2', 'condition3')->once()->andReturn('conditionAnd');
        $mockQb->shouldReceive('andWhere')->with('conditionAnd')->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')
            ->with('taId', TrafficAreaEntity::NORTHERN_IRELAND_TRAFFIC_AREA_CODE)
            ->once()
            ->andReturnSelf();
        $mockQb->shouldReceive('setParameter')
            ->with('operatorType', 'lcat_gv')
            ->once()
            ->andReturnSelf();

        $this->queryBuilder->shouldReceive('with')->with('trafficArea', 'ta')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('goodsOrPsv', 'gp')->once()->getMock();
        $this->queryBuilder->shouldReceive('modifyQuery')->with($mockQb)->once()->andReturnSelf();

        $this->em->shouldReceive('getRepository->createQueryBuilder')->with('ds')->once()->andReturn($mockQb);
        $mockQb->shouldReceive('getQuery->getResult')->once()->andReturn(['result']);

        $this->sut->fetchDiscPrefixes('N', 'lcat_gv');
    }
}
