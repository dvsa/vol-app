<?php

declare(strict_types=1);

/**
 * Workshop test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Repository\Workshop as WorkshopRepo;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityRepository;
use Dvsa\Olcs\Api\Entity\Licence\Workshop;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Doctrine\DBAL\LockMode;

/**
 * Workshop test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class WorkshopTest extends RepositoryTestCase
{
    public function setUp(): void
    {
        $this->setUpSut(WorkshopRepo::class);
    }

    public function testFetchUsingId(): void
    {
        $command = m::mock(QueryInterface::class);
        $command->shouldReceive('getId')
            ->andReturn(111);

        /** @var QueryBuilder $qb */
        $qb = m::mock(QueryBuilder::class);
        $qb->shouldReceive('getQuery->getResult')
            ->with(Query::HYDRATE_OBJECT)
            ->andReturn(null);

        $this->queryBuilder->shouldReceive('modifyQuery')
            ->once()
            ->with($qb)
            ->andReturnSelf()
            ->shouldReceive('withRefdata')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('byId')
            ->once()
            ->with(111)
            ->andReturnSelf()
            ->shouldReceive('withContactDetails')
            ->once();

        /** @var EntityRepository $repo */
        $repo = m::mock(EntityRepository::class);
        $repo->shouldReceive('createQueryBuilder')
            ->with('m')
            ->andReturn($qb);

        $this->em->shouldReceive('getRepository')
            ->with(Workshop::class)
            ->andReturn($repo);

        $this->expectException(NotFoundException::class);

        $this->sut->fetchUsingId($command, Query::HYDRATE_OBJECT, 1);
    }

    public function testFetchUsingIdWithResults(): void
    {
        $command = m::mock(QueryInterface::class);
        $command->shouldReceive('getId')
            ->andReturn(111);

        $result = m::mock(Workshop::class);
        $results = [$result];

        /** @var QueryBuilder $qb */
        $qb = m::mock(QueryBuilder::class);
        $qb->shouldReceive('getQuery->getResult')
            ->with(Query::HYDRATE_OBJECT)
            ->andReturn($results);

        $this->queryBuilder->shouldReceive('modifyQuery')
            ->once()
            ->with($qb)
            ->andReturnSelf()
            ->shouldReceive('withRefdata')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('byId')
            ->once()
            ->with(111)
            ->andReturnSelf()
            ->shouldReceive('withContactDetails')
            ->once();

        /** @var EntityRepository $repo */
        $repo = m::mock(EntityRepository::class);
        $repo->shouldReceive('createQueryBuilder')
            ->with('m')
            ->andReturn($qb);

        $this->em->shouldReceive('getRepository')
            ->with(Workshop::class)
            ->andReturn($repo)
            ->shouldReceive('lock')
            ->with($result, LockMode::OPTIMISTIC, 1);

        $this->sut->fetchUsingId($command, Query::HYDRATE_OBJECT, 1);
    }

    public function testFetchForLicence(): void
    {
        $qb = $this->createMockQb('BLAH');
        $this->mockCreateQueryBuilder($qb);

        $this->queryBuilder->shouldReceive('modifyQuery')->with($qb)->once()->andReturnSelf()
            ->shouldReceive('withRefdata')->with()->once()->andReturnSelf()
            ->shouldReceive('with')->with('contactDetails', 'cd')->once()->andReturnSelf()
            ->shouldReceive('with')->with('cd.address')->once()->andReturnSelf();

        $qb->shouldReceive('getQuery->getResult')->with(Query::HYDRATE_OBJECT)->once()->andReturn(['RESULTS']);

        $this->assertEquals(['RESULTS'], $this->sut->fetchForLicence(2017));

        $expectedQuery = 'BLAH AND m.licence = [[2017]]';
        $this->assertEquals($expectedQuery, $this->query);
    }

    public function testApplyFiltersLicence(): void
    {
        $qb = $this->createMockQb('BLAH');
        $query = m::mock(\Dvsa\Olcs\Transfer\Query\Licence\Safety::class);
        $query->shouldReceive('getId')->with()->once()->andReturn(34);

        $this->sut->applyListFilters($qb, $query);

        $expectedQuery = 'BLAH AND m.licence = [[34]]';
        $this->assertEquals($expectedQuery, $this->query);
    }

    public function testApplyFiltersApplication(): void
    {
        $qb = $this->createMockQb('BLAH');
        $query = m::mock(\Dvsa\Olcs\Transfer\Query\Application\Safety::class);
        $query->shouldReceive('getId')->with()->once()->andReturn(134);

        $mockApplication = m::mock();
        $mockApplication->shouldReceive('getLicence->getId')->with()->once()->andReturn(24);
        $this->em->shouldReceive('getReference')->with(Application::class, 134)->once()->andReturn($mockApplication);

        $this->sut->applyListFilters($qb, $query);

        $expectedQuery = 'BLAH AND m.licence = [[24]]';
        $this->assertEquals($expectedQuery, $this->query);
    }
}
