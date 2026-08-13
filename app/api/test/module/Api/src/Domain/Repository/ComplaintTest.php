<?php

declare(strict_types=1);

/**
 * ComplaintTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Mockery as m;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Api\Domain\Repository\Complaint as ComplaintRepo;
use Dvsa\Olcs\Api\Entity\Cases\Complaint;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * ComplaintTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
final class ComplaintTest extends RepositoryTestCase
{
    #[\Override]
    public function setUp(): void
    {
        $this->setUpSut(ComplaintRepo::class);
    }

    public function testFetchById(): void
    {
        $command = m::mock(QueryInterface::class);
        $command->shouldReceive('getId')->andReturn(111);
        $command->shouldReceive('getIsCompliance')->andReturn(false);

        $result = m::mock(Complaint::class);
        $results = [$result];

        /** @var QueryBuilder $qb */
        $qb = m::mock(QueryBuilder::class);
        $expr = $this->mockExprEq('m.isCompliance', ':byIsCompliance');
        $qb->shouldReceive('expr->eq')->with('m.isCompliance', ':byIsCompliance')->once()->andReturn($expr);
        $qb->shouldReceive('andWhere')->with($expr)->once()->andReturnSelf();
        $qb->shouldReceive('setParameter')->with('byIsCompliance', false)->once()->andReturnSelf();
        $qb->shouldReceive('getQuery->getResult')->with(Query::HYDRATE_OBJECT)->andReturn($results);

        $this->queryBuilder->shouldReceive('modifyQuery')
            ->once()->with($qb)->andReturnSelf()
            ->shouldReceive('withRefdata')->once()->andReturnSelf()
            ->shouldReceive('with')->once()->with('complainantContactDetails', 'cd')->andReturnSelf()
            ->shouldReceive('with')->once()->with('cd.person')->andReturnSelf()
            ->shouldReceive('with')->once()->with('operatingCentres', 'oc')->andReturnSelf()
            ->shouldReceive('with')->once()->with('oc.address')->andReturnSelf()
            ->shouldReceive('byId')->once()->with(111);

        /** @var EntityRepository $repo */
        $repo = m::mock(EntityRepository::class);
        $repo->shouldReceive('createQueryBuilder')
            ->with('m')
            ->andReturn($qb);

        $this->em->shouldReceive('getRepository')
            ->with(Complaint::class)
            ->andReturn($repo)
            ->shouldReceive('lock')
            ->with($result, LockMode::OPTIMISTIC, 1);

        $this->sut->fetchUsingId($command, Query::HYDRATE_OBJECT, 1);
    }

    public function testApplyListJoins(): void
    {
        // mock SUT to allow testing the protected method
        $sut = m::mock(ComplaintRepo::class)->makePartial()->shouldAllowMockingProtectedMethods();

        $mockQb = m::mock(QueryBuilder::class);

        $sut->shouldReceive('getQueryBuilder')->with()->andReturn($mockQb);
        $mockQb->shouldReceive('with')->with('complainantContactDetails', 'ccd')->once()->andReturnSelf();
        $mockQb->shouldReceive('with')->with('ccd.person')->once()->andReturnSelf();
        $mockQb->shouldReceive('with')->with('operatingCentres', 'oc')->once()->andReturnSelf();
        $mockQb->shouldReceive('with')->with('oc.address')->once()->andReturnSelf();

        $sut->applyListJoins($mockQb);
    }

    public function testApplyFiltersCase(): void
    {
        // mock SUT to allow testing the protected method
        $sut = m::mock(ComplaintRepo::class)->makePartial()->shouldAllowMockingProtectedMethods();

        $qb = m::mock(QueryBuilder::class);
        $query = m::mock(QueryInterface::class);

        $query->shouldReceive('getCase')->with()->andReturn(213);
        $query->shouldReceive('getIsCompliance')->with()->andReturn(null);
        $query->shouldReceive('getLicence')->with()->andReturn(null);
        $query->shouldReceive('getApplication')->with()->andReturn(null);

        $expr = $this->mockExprEq('m.case', ':byCase');
        $qb->shouldReceive('expr->eq')->with('m.case', ':byCase')->once()->andReturn($expr);
        $qb->shouldReceive('andWhere')->with($expr)->once()->andReturnSelf();
        $qb->shouldReceive('setParameter')->with('byCase', 213)->once()->andReturnSelf();

        $sut->applyListFilters($qb, $query);
    }

    public function testApplyFiltersCompliance(): void
    {
        // mock SUT to allow testing the protected method
        $sut = m::mock(ComplaintRepo::class)->makePartial()->shouldAllowMockingProtectedMethods();

        $qb = m::mock(QueryBuilder::class);
        $query = m::mock(QueryInterface::class);

        $query->shouldReceive('getCase')->with()->andReturn(null);
        $query->shouldReceive('getIsCompliance')->with()->andReturn(324);
        $query->shouldReceive('getLicence')->with()->andReturn(null);
        $query->shouldReceive('getApplication')->with()->andReturn(null);

        $expr = $this->mockExprEq('m.isCompliance', ':isCompliance');
        $qb->shouldReceive('expr->eq')->with('m.isCompliance', ':isCompliance')->once()->andReturn($expr);
        $qb->shouldReceive('andWhere')->with($expr)->once()->andReturnSelf();
        $qb->shouldReceive('setParameter')->with('isCompliance', 324)->once()->andReturnSelf();

        $sut->applyListFilters($qb, $query);
    }

    public function testApplyFiltersLicence(): void
    {
        // mock SUT to allow testing the protected method
        $sut = m::mock(ComplaintRepo::class)->makePartial()->shouldAllowMockingProtectedMethods();

        $qb = m::mock(QueryBuilder::class);
        $query = m::mock(QueryInterface::class);

        $query->shouldReceive('getCase')->with()->andReturn(null);
        $query->shouldReceive('getIsCompliance')->with()->andReturn(null);
        $query->shouldReceive('getLicence')->with()->andReturn(33);
        $query->shouldReceive('getApplication')->with()->andReturn(null);

        $expr = $this->mockExprEq('ca.licence', ':licence');
        $qb->shouldReceive('expr->eq')->with('ca.licence', ':licence')->once()->andReturn($expr);
        $qb->shouldReceive('andWhere')->with($expr)->once()->andReturnSelf();
        $qb->shouldReceive('setParameter')->with('licence', 33)->once()->andReturnSelf();

        $mockQb = m::mock(\Dvsa\Olcs\Api\Domain\QueryBuilder::class);
        $sut->shouldReceive('getQueryBuilder')->with()->once()->andReturn($mockQb);
        $mockQb->shouldReceive('with')->with('case', 'ca')->once()->andReturnSelf();

        $sut->applyListFilters($qb, $query);
    }

    public function testApplyFiltersApplication(): void
    {
        // mock SUT to allow testing the protected method
        $sut = m::mock(ComplaintRepo::class)->makePartial()->shouldAllowMockingProtectedMethods();

        $qb = m::mock(QueryBuilder::class);
        $query = m::mock(QueryInterface::class);

        $query->shouldReceive('getCase')->with()->andReturn(null);
        $query->shouldReceive('getIsCompliance')->with()->andReturn(null);
        $query->shouldReceive('getLicence')->with()->andReturn(null);
        $query->shouldReceive('getApplication')->with()->andReturn(133);

        $expr = $this->mockExprEq('ca.application', ':application');
        $qb->shouldReceive('expr->eq')->with('ca.application', ':application')->once()->andReturn($expr);
        $qb->shouldReceive('andWhere')->with($expr)->once()->andReturnSelf();
        $qb->shouldReceive('setParameter')->with('application', 133)->once()->andReturnSelf();

        $mockQb = m::mock(\Dvsa\Olcs\Api\Domain\QueryBuilder::class);
        $sut->shouldReceive('getQueryBuilder')->with()->once()->andReturn($mockQb);
        $mockQb->shouldReceive('with')->with('case', 'ca')->once()->andReturnSelf();

        $sut->applyListFilters($qb, $query);
    }
}
