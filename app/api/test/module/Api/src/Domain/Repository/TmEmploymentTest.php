<?php

declare(strict_types=1);

/**
 * TmEmploymentTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Dvsa\Olcs\Api\Domain\Repository\TmEmployment as Repo;
use Mockery as m;

/**
 * TmEmploymentTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class TmEmploymentTest extends RepositoryTestCase
{
    public function setUp(): void
    {
        $this->setUpSut(Repo::class);
    }

    public function testBuildDefaultQuery(): void
    {
        $mockQb = m::mock(\Doctrine\ORM\QueryBuilder::class);

        $this->em->shouldReceive('getRepository->createQueryBuilder')->with('te')->once()->andReturn($mockQb);

        $this->queryBuilder->shouldReceive('modifyQuery')->with($mockQb)->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('withRefdata')->with()->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('byId')->with(834)->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('contactDetails', 'cd')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('cd.address', 'ad')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('ad.countryCode', 'cc')->once()->andReturnSelf();

        $mockQb->shouldReceive('getQuery->getResult')->once()->andReturn(['RESULT']);

        $this->assertSame('RESULT', $this->sut->fetchById(834));
    }

    public function testFetchByTransportManager(): void
    {
        $mockQb = m::mock(\Doctrine\ORM\QueryBuilder::class);

        $this->em->shouldReceive('getRepository->createQueryBuilder')->with('te')->once()->andReturn($mockQb);

        $this->queryBuilder->shouldReceive('modifyQuery')->with($mockQb)->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('withRefdata')->with()->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('contactDetails', 'cd')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('cd.address')->once()->andReturnSelf();

        $mockQb->shouldReceive('expr->eq')->with('te.transportManager', ':tmId')->once()->andReturn('EXPR');
        $mockQb->shouldReceive('andWhere')->with('EXPR')->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')->with('tmId', 534)->once();

        $mockQb->shouldReceive('getQuery->getResult')->once()->andReturn('RESULT');

        $this->assertSame('RESULT', $this->sut->fetchByTransportManager(534));
    }

    public function testApplyListJoins(): void
    {
        $this->setUpSut(Repo::class, true);

        $mockQb = m::mock(\Doctrine\ORM\QueryBuilder::class);

        $this->sut->shouldReceive('getQueryBuilder')->with()->once()->andReturn($mockQb);
        $mockQb->shouldReceive('with')->with('contactDetails', 'cd')->once()->andReturnSelf();
        $mockQb->shouldReceive('with')->with('cd.address', 'add')->once()->andReturnSelf();
        $mockQb->shouldReceive('with')->with('add.countryCode')->once()->andReturnSelf();

        $this->sut->applyListJoins($mockQb);
    }

    public function testApplyListFilters(): void
    {
        $this->setUpSut(Repo::class, true);

        $mockQb = m::mock(\Doctrine\ORM\QueryBuilder::class);
        $mockQi = m::mock(\Dvsa\Olcs\Transfer\Query\QueryInterface::class);

        $mockQi->shouldReceive('getTransportManager')->with()->once()->andReturn(12);

        $mockQb->shouldReceive('expr->eq')->with('te.transportManager', ':transportManager')->once()->andReturn('EXPR');
        $mockQb->shouldReceive('andWhere')->with('EXPR')->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')->with('transportManager', 12)->once();

        $this->sut->applyListFilters($mockQb, $mockQi);
    }
}
