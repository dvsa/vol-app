<?php

declare(strict_types=1);

/**
 * Opposition Repo test
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Api\Entity\Opposition\Opposition;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Repository\Opposition as Repo;
use Doctrine\ORM\EntityRepository;

/**
 * Opposition Repo test
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
class OppositionTest extends RepositoryTestCase
{
    public function setUp(): void
    {
        $this->setUpSut(Repo::class);
    }

    public function testFetchUsingId(): void
    {
        $id = 99;
        $mockResult = [0 => 'result'];

        $command = m::mock(QueryInterface::class);
        $command->shouldReceive('getId')
            ->andReturn($id);

        /** @var QueryBuilder $qb */
        $qb = m::mock(QueryBuilder::class);

        $this->queryBuilder->shouldReceive('modifyQuery')
            ->once()
            ->with($qb)
            ->andReturnSelf()
            ->shouldReceive('withRefdata')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('byId')
            ->once()
            ->with($id)
            ->andReturnSelf()
            ->shouldReceive('with')
            ->once()
            ->with('opposer', 'o')
            ->andReturnSelf()
            ->shouldReceive('with')
            ->once()
            ->with('grounds')
            ->andReturnSelf()
            ->shouldReceive('withPersonContactDetails')
            ->once()
            ->with('o.contactDetails', 'c');

        $qb->shouldReceive('getQuery->getResult')
            ->with(Query::HYDRATE_OBJECT)
            ->andReturn($mockResult);

        /** @var EntityRepository $repo */
        $repo = m::mock(EntityRepository::class);
        $repo->shouldReceive('createQueryBuilder')
            ->with('m')
            ->andReturn($qb);

        $this->em->shouldReceive('getRepository')
            ->with(Opposition::class)
            ->andReturn($repo);

        $result = $this->sut->fetchUsingId($command, Query::HYDRATE_OBJECT);

        $this->assertEquals($result, $mockResult[0]);
    }

    public function testApplyFiltersCase(): void
    {
        // mock SUT to allow testing the protected method
        $sut = m::mock(Repo::class)->makePartial()->shouldAllowMockingProtectedMethods();

        $qb = m::mock(QueryBuilder::class);
        $query = m::mock(QueryInterface::class);

        $query->shouldReceive('getCase')->with()->andReturn(746);
        $query->shouldReceive('getLicence')->with()->andReturn(null);
        $query->shouldReceive('getApplication')->with()->andReturn(null);

        $qb->shouldReceive('expr->eq')->with('m.case', ':byCase')->once()->andReturn('EXPR');
        $qb->shouldReceive('andWhere')->with('EXPR')->once()->andReturnSelf();
        $qb->shouldReceive('setParameter')->with('byCase', 746)->once()->andReturnSelf();

        $sut->applyListFilters($qb, $query);
    }

    public function testApplyFiltersLicence(): void
    {
        // mock SUT to allow testing the protected method
        $sut = m::mock(Repo::class)->makePartial()->shouldAllowMockingProtectedMethods();

        $qb = m::mock(QueryBuilder::class);
        $query = m::mock(QueryInterface::class);

        $query->shouldReceive('getCase')->with()->andReturn(null);
        $query->shouldReceive('getLicence')->with()->andReturn(43);
        $query->shouldReceive('getApplication')->with()->andReturn(null);

        $qb->shouldReceive('expr->eq')->with('ca.licence', ':licence')->once()->andReturn('EXPR');
        $qb->shouldReceive('andWhere')->with('EXPR')->once()->andReturnSelf();
        $qb->shouldReceive('setParameter')->with('licence', 43)->once()->andReturnSelf();

        $sut->applyListFilters($qb, $query);
    }

    public function testApplyFiltersApplication(): void
    {
        // mock SUT to allow testing the protected method
        $sut = m::mock(Repo::class)->makePartial()->shouldAllowMockingProtectedMethods();

        $qb = m::mock(QueryBuilder::class);
        $query = m::mock(QueryInterface::class);

        $query->shouldReceive('getCase')->with()->andReturn(null);
        $query->shouldReceive('getLicence')->with()->andReturn(null);
        $query->shouldReceive('getApplication')->with()->andReturn(543);

        $qb->shouldReceive('expr->eq')->with('ca.application', ':application')->once()->andReturn('EXPR');
        $qb->shouldReceive('andWhere')->with('EXPR')->once()->andReturnSelf();
        $qb->shouldReceive('setParameter')->with('application', 543)->once()->andReturnSelf();

        $sut->applyListFilters($qb, $query);
    }

    public function testFetchByApplicationId(): void
    {
        $applicationId = 69;

        /** @var QueryBuilder $qb */
        $mockQb = m::mock(QueryBuilder::class);

        $this->em
            ->shouldReceive('getRepository->createQueryBuilder')
            ->once()
            ->andReturn($mockQb);

        $this->queryBuilder->shouldReceive('modifyQuery')
            ->once()
            ->with($mockQb)
            ->andReturnSelf()
            ->shouldReceive('with')
            ->with('case', 'c')
            ->andReturnSelf()
            ->shouldReceive('order')
            ->with('createdOn', 'DESC')
            ->andReturnSelf();

        $mockQb
            ->shouldReceive('expr->eq')
            ->with('c.application', ':application')
            ->andReturnSelf();
        $mockQb
            ->shouldReceive('andWhere')
            ->andReturnSelf();
        $mockQb
            ->shouldReceive('setParameter')
            ->with('application', $applicationId)
            ->andReturnSelf();

        $mockQb->shouldReceive('getQuery->getResult')->once()->andReturn('result');

        $this->assertSame(
            'result',
            $this->sut->fetchByApplicationId($applicationId)
        );
    }
}
