<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity\Cases\Cases as CasesEntity;
use Dvsa\Olcs\Transfer\Query as TransferQry;
use Dvsa\Olcs\Transfer\Query\Cases\ByLicence;
use Dvsa\Olcs\Transfer\Query\Cases\ByTransportManager;
use Mockery as m;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Domain\Repository\Cases::class)]
class CasesTest extends RepositoryTestCase
{
    /** @var  Repository\Cases | m\MockInterface */
    protected $sut;

    /** @var  \Doctrine\ORM\QueryBuilder | m\MockInterface */
    private $mockDqb;
    /** @var  \Dvsa\Olcs\Transfer\Query\QueryInterface | m\MockInterface */
    private $mockQi;

    public function setUp(): void
    {
        $this->setUpSut(Repository\Cases::class, true);

        $this->mockDqb = m::mock(\Doctrine\ORM\QueryBuilder::class);
        $this->mockQi = m::mock(\Dvsa\Olcs\Transfer\Query\QueryInterface::class);
    }

    /**
     * @param $qb
     *
     * @return m\MockInterface
     */
    public function getMockRepo(mixed $qb): m\MockInterface
    {
        $repo = m::mock(EntityRepository::class);
        $repo->shouldReceive('createQueryBuilder')
            ->with('m')
            ->andReturn($qb);

        return $repo;
    }

    public function testApplyListFiltersTm(): void
    {
        $sut = m::mock(Repository\Cases::class)->makePartial()->shouldAllowMockingProtectedMethods();

        $transportManager = 3;

        $mockQuery = m::mock(ByTransportManager::class);
        $mockQuery->shouldReceive('getTransportManager')
            ->once()
            ->andReturn($transportManager)
            ->getMock();

        $mockQb = m::mock(QueryBuilder::class);
        $mockQb->shouldReceive('expr->eq')->with('m.transportManager', ':byTransportManager')->once()->andReturnSelf();
        $mockQb->shouldReceive('andWhere')->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')->with('byTransportManager', $transportManager)->once()->andReturnSelf();

        $sut->applyListFilters($mockQb, $mockQuery);
    }

    public function testApplyListFiltersLicence(): void
    {
        $sut = m::mock(Repository\Cases::class)->makePartial()->shouldAllowMockingProtectedMethods();

        $licence = 7;

        $mockQuery = m::mock(ByLicence::class);
        $mockQuery->shouldReceive('getLicence')
            ->once()
            ->andReturn($licence)
            ->getMock();

        $mockQb = m::mock(QueryBuilder::class);
        $mockQb->shouldReceive('expr->eq')->with('m.licence', ':byLicence')->once()->andReturnSelf();
        $mockQb->shouldReceive('andWhere')->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')->with('byLicence', $licence)->once()->andReturnSelf();

        $sut->applyListFilters($mockQb, $mockQuery);
    }

    public function testForReportOpenListQry(): void
    {
        /** @var \Dvsa\Olcs\Transfer\Query\QueryInterface |m\MockInterface $mockQry */
        $mockQry = m::mock(TransferQry\Cases\Report\OpenList::class)->makePartial();
        $mockQry
            ->shouldReceive('getCaseType')->twice()->andReturn('unit_CaseType')
            ->shouldReceive('getApplicationStatus')->twice()->andReturn('unit_AppStatus')
            ->shouldReceive('getLicenceStatus')->twice()->andReturn('unit_LicStatus')
            ->shouldReceive('getTrafficAreas')->once()->andReturn(['unit_TA']);

        $qb = $this->createMockQb('{{QUERY}}');
        $this->mockCreateQueryBuilder($qb);

        $this->queryBuilder
            ->shouldReceive('modifyQuery')->with($qb)->once()->andReturnSelf()
            ->shouldReceive('withRefdata')->with()->once()->andReturnSelf()
            ->shouldReceive('with')->with('licence', 'l')->once()->andReturnSelf()
            ->shouldReceive('with')->with('application', 'a')->once()->andReturnSelf()
            ->shouldReceive('with')->with('l.trafficArea', 'ta')->once()->andReturnSelf()
            ->shouldReceive('with')->once()->andReturnSelf()
            ->shouldReceive('paginate')->once()->andReturnSelf();

        $this->sut->shouldReceive('fetchPaginatedList')->andReturn('EXPECT');

        static::assertEquals('EXPECT', $this->sut->fetchList($mockQry));

        $expected = '{{QUERY}}' .
            ' SELECT CONCAT(ct.description, m.id) as HIDDEN caseType' .
            ' AND m.caseType = [[unit_CaseType]]' .
            ' AND m.closedDate IS NULL' .
            ' AND a.status = [[unit_AppStatus]]' .
            ' AND l.status = [[unit_LicStatus]]' .
            ' AND ta.id IN [[["unit_TA"]]]';

        static::assertEquals($expected, $this->query);
    }

    public function testForReportOpenListQryOtherTa(): void
    {
        $trafficAreas = ['other', 'A', 'B'];

        /** @var \Dvsa\Olcs\Transfer\Query\QueryInterface |m\MockInterface $mockQry */
        $mockQry = m::mock(TransferQry\Cases\Report\OpenList::class)->makePartial();
        $mockQry->shouldReceive('getTrafficAreas')->once()->andReturn($trafficAreas);

        $qb = $this->createMockQb('{{QUERY}}');
        $this->mockCreateQueryBuilder($qb);

        $this->queryBuilder
            ->shouldReceive('modifyQuery')->with($qb)->once()->andReturnSelf()
            ->shouldReceive('withRefdata')->with()->once()->andReturnSelf()
            ->shouldReceive('with')->andReturnSelf()
            ->shouldReceive('paginate')->once()->andReturnSelf();

        $this->sut->shouldReceive('fetchPaginatedList')->andReturn('EXPECT');

        static::assertEquals('EXPECT', $this->sut->fetchList($mockQry));

        $expected = '{{QUERY}}' .
            ' SELECT CONCAT(ct.description, m.id) as HIDDEN caseType' .
            ' AND m.closedDate IS NULL' .
            ' AND (ta.id IS NULL OR ta.id IN [[{"1":"A","2":"B"}]])';

        static::assertEquals($expected, $this->query);
    }

    public function testFetchWithLicenceUsingId(): void
    {
        $this->mockQi->shouldReceive('getId')->andReturn(24);

        $result = m::mock(CasesEntity::class);
        $results = [$result];

        /** @var QueryBuilder $qb */
        $qb = m::mock(QueryBuilder::class);
        $qb->shouldReceive('getQuery->getResult')->with(Query::HYDRATE_OBJECT)->andReturn($results);

        $this->queryBuilder->shouldReceive('modifyQuery')
            ->once()->with($qb)->andReturnSelf()
            ->shouldReceive('withRefdata')->once()->andReturnSelf()
            ->shouldReceive('with')->once()->with('licence', 'l')->andReturnSelf()
            ->shouldReceive('with')->once()->with('l.operatingCentres', 'loc')->andReturnSelf()
            ->shouldReceive('with')->once()->with('loc.operatingCentre', 'oc')->andReturnSelf()
            ->shouldReceive('with')->once()->with('oc.address')->andReturnSelf()
            ->shouldReceive('byId')->once()->with(24);

        /** @var EntityRepository $repo */
        $repo = m::mock(EntityRepository::class);
        $repo->shouldReceive('createQueryBuilder')
            ->with('m')
            ->andReturn($qb);

        $this->em->shouldReceive('getRepository')
            ->with(CasesEntity::class)
            ->andReturn($repo)
            ->shouldReceive('lock')
            ->with($result, LockMode::OPTIMISTIC, 1);

        $this->sut->fetchWithLicenceUsingId($this->mockQi, Query::HYDRATE_OBJECT, 1);
    }

    public function testFetchWithLicence(): void
    {
        $caseId = 1;

        $result = m::mock(CasesEntity::class);
        $results = [$result];

        /** @var QueryBuilder $qb */
        $qb = m::mock(QueryBuilder::class);
        $qb->shouldReceive('getQuery->getResult')->andReturn($results);

        $this->queryBuilder->shouldReceive('modifyQuery')
            ->once()
            ->with($qb)
            ->andReturnSelf()
            ->shouldReceive('with')
            ->with('licence', 'l')
            ->andReturnSelf()
            ->once()
            ->shouldReceive('with')
            ->with('application', 'a')
            ->andReturnSelf()
            ->once()
            ->shouldReceive('with')
            ->with('transportManager', 'tm')
            ->andReturnSelf()
            ->once()
            ->shouldReceive('byId')
            ->with($caseId)
            ->once()
            ->andReturnSelf();

        /** @var EntityRepository $repo */
        $repo = m::mock(EntityRepository::class);
        $repo->shouldReceive('createQueryBuilder')
            ->with('m')
            ->andReturn($qb);

        $this->em->shouldReceive('getRepository')
            ->with(CasesEntity::class)
            ->andReturn($repo)
            ->shouldReceive('lock')
            ->with($result, LockMode::OPTIMISTIC, 1);

        $this->sut->fetchExtended($caseId);
    }

    public function testFetchWithLicenceNotFound(): void
    {
        $caseId = 1;

        $results = null;

        $this->expectException(NotFoundException::class);
        /** @var QueryBuilder $qb */
        $qb = m::mock(QueryBuilder::class);
        $qb->shouldReceive('getQuery->getResult')->andReturn($results);

        $this->queryBuilder->shouldReceive('modifyQuery')
            ->once()
            ->with($qb)
            ->andReturnSelf()
            ->shouldReceive('with')
            ->with('licence', 'l')
            ->andReturnSelf()
            ->once()
            ->shouldReceive('with')
            ->with('application', 'a')
            ->andReturnSelf()
            ->once()
            ->shouldReceive('with')
            ->with('transportManager', 'tm')
            ->andReturnSelf()
            ->once()
            ->shouldReceive('byId')
            ->with($caseId)
            ->once()
            ->andReturnSelf();

        /** @var EntityRepository $repo */
        $repo = m::mock(EntityRepository::class);
        $repo->shouldReceive('createQueryBuilder')
            ->with('m')
            ->andReturn($qb);

        $this->em->shouldReceive('getRepository')
            ->with(CasesEntity::class)
            ->andReturn($repo);

        $this->sut->fetchExtended($caseId);
    }

    public function testBuildDefaultListQuery(): void
    {
        $this->sut->shouldReceive('getQueryBuilder')->with()->andReturn($this->mockDqb);

        $this->mockDqb
            ->shouldReceive('modifyQuery')->with($this->mockDqb)->once()->andReturnSelf()
            ->shouldReceive('withRefdata')->with()->once()->andReturnSelf()
            ->shouldReceive('with')->with('caseType', 'ct')->once()->andReturnSelf()
            ->shouldReceive('addSelect')
            ->with('CONCAT(ct.description, m.id) as HIDDEN caseType')
            ->once()
            ->andReturnSelf();

        $this->sut->buildDefaultListQuery($this->mockDqb, $this->mockQi);
    }

    public function testFetchOpenCasesForSurrender(): void
    {
        $qb = $this->createMockQb('BLAH');

        $this->mockCreateQueryBuilder($qb);

        $this->queryBuilder
            ->shouldReceive('modifyQuery')->with($qb)->times(2)->andReturnSelf()
            ->shouldReceive('withRefdata')->with()->times(2)->andReturnSelf()
            ->shouldReceive('with')->with('caseType', 'ct')->times(2)->andReturnSelf();

        $qb->shouldReceive('getQuery')->andReturn(
            m::mock()->shouldReceive('execute')
                ->shouldReceive('getResult')
                ->andReturn(['RESULTS'])
                ->getMock()
        );

        $this->mockQi->shouldReceive('getId')->andReturn(95);
        $this->assertEquals(['RESULTS'], $this->sut->fetchOpenCasesForSurrender($this->mockQi));

        $expectedQuery = 'BLAH SELECT CONCAT(ct.description, m.id) as HIDDEN caseType AND m.licence = [[95]] AND m.closedDate IS NULL';
        $this->assertEquals($expectedQuery, $this->query);
        $this->sut->fetchOpenCasesForSurrender($this->mockQi);
    }

    public function testFetchOpenCasesForApplication(): void
    {
        $qb = $this->createMockQb('BLAH');

        $this->mockCreateQueryBuilder($qb);

        $this->queryBuilder
            ->shouldReceive('modifyQuery')->with($qb)->times(1)->andReturnSelf()
            ->shouldReceive('with')->with('application', 'a')->andReturnSelf();

        $qb->shouldReceive('getQuery')->andReturn(
            m::mock()->shouldReceive('execute')
                ->shouldReceive('getResult')
                ->andReturn([$this->createStub(CasesEntity::class), $this->createStub(CasesEntity::class)])
                ->getMock()
        );
        $result = $this->sut->fetchOpenCasesForApplication(1);
        $expectedQuery = 'BLAH AND a.id = [[1]] AND m.closedDate IS NULL';
        $this->assertEquals($expectedQuery, $this->query);
        $this->assertCount(2, $result);
    }
}
