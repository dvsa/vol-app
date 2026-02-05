<?php

declare(strict_types=1);

/**
 * PreviousConvictionTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Dvsa\Olcs\Api\Domain\Repository\PreviousConviction as Repo;
use Mockery as m;

/**
 * PreviousConvictionTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class PreviousConvictionTest extends RepositoryTestCase
{
    public function setUp(): void
    {
        $this->setUpSut(Repo::class);
    }

    public function testFetchByTransportManager(): void
    {
        $mockQb = m::mock(\Doctrine\ORM\QueryBuilder::class);

        $this->em->shouldReceive('getRepository->createQueryBuilder')->with('pc')->once()->andReturn($mockQb);

        $this->queryBuilder->shouldReceive('modifyQuery')->with($mockQb)->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('withRefdata')->with()->once()->andReturnSelf();

        $mockQb->shouldReceive('expr->eq')->with('pc.transportManager', ':tmId')->once()->andReturn('EXPR');
        $mockQb->shouldReceive('andWhere')->with('EXPR')->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')->with('tmId', 123)->once();

        $mockQb->shouldReceive('getQuery->getResult')->once()->andReturn('RESULT');

        $this->assertSame('RESULT', $this->sut->fetchByTransportManager(123));
    }

    public function testApplyListFilters(): void
    {
        $this->setUpSut(Repo::class, true);

        $mockQb = m::mock(\Doctrine\ORM\QueryBuilder::class);

        $mockQ = m::mock(\Dvsa\Olcs\Transfer\Query\QueryInterface::class);
        $mockQ->shouldReceive('getTransportManager')->with()->twice()->andReturn(33);

        $mockQb->shouldReceive('expr->eq')->with('pc.transportManager', ':tmId')->once()->andReturn('EXPR');
        $mockQb->shouldReceive('andWhere')->with('EXPR')->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')->with('tmId', 33)->once();

        $this->sut->applyListFilters($mockQb, $mockQ);
    }
}
