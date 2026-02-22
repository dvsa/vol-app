<?php

declare(strict_types=1);

/**
 * Inspection Request test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Api\Entity\Inspection\InspectionRequest;
use Dvsa\Olcs\Transfer\Query\InspectionRequest as Qry;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Repository\InspectionRequest as InspectionRequestRepo;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;

/**
 * Inspection Request test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class InspectionRequestTest extends RepositoryTestCase
{
    public function setUp(): void
    {
        $this->setUpSut(InspectionRequestRepo::class, true);
    }

    /**
     * testFetchForInspectionRequest
     */
    public function testFetchForInspectionRequest(): void
    {

        $qb = $this->createMockQb('QUERY');
        $qb->shouldReceive('getQuery->getSingleResult')->once()->andReturn(1);
        $this->mockCreateQueryBuilder($qb);
        $inspectionRequestId = 1;
        $this->queryBuilder->shouldReceive('modifyQuery')
            ->once()
            ->with($qb)
            ->andReturnSelf()
            ->shouldReceive('with')
            ->with('licence', 'l')
            ->andReturnSelf()
            ->once()
            ->shouldReceive('with')
            ->with('l.licenceType', 'lt')
            ->andReturnSelf()
            ->once()
            ->shouldReceive('with')
            ->with('l.organisation', 'l_o')
            ->andReturnSelf()
            ->once()
            ->shouldReceive('with')
            ->with('l_o.organisationPersons', 'l_o_p')
            ->andReturnSelf()
            ->once()
            ->shouldReceive('with')
            ->with('l_o_p.person', 'l_o_p_p')
            ->andReturnSelf()
            ->once()
            ->shouldReceive('with')
            ->with('l_o.tradingNames', 'l_o_tn')
            ->andReturnSelf()
            ->once()
            ->shouldReceive('with')
            ->with('l.correspondenceCd', 'l_ccd')
            ->andReturnSelf()
            ->once()
            ->shouldReceive('with')
            ->with('l_ccd.address', 'l_ccd_a')
            ->andReturnSelf()
            ->once()
            ->shouldReceive('with')
            ->with('l_ccd.phoneContacts', 'l_ccd_pc')
            ->andReturnSelf()
            ->once()
            ->shouldReceive('with')
            ->with('l_ccd_pc.phoneContactType', 'l_ccd_pc_pct')
            ->andReturnSelf()
            ->once()
            ->shouldReceive('with')
            ->with('l.enforcementArea', 'l_ea')
            ->andReturnSelf()
            ->once()
            ->shouldReceive('with')
            ->with('operatingCentre', 'oc')
            ->andReturnSelf()
            ->once()
            ->shouldReceive('with')
            ->with('oc.address', 'oc_a')
            ->andReturnSelf()
            ->once()
            ->shouldReceive('with')
            ->with('application', 'a')
            ->andReturnSelf()
            ->once()
            ->shouldReceive('with')
            ->with('a.licence', 'a_l')
            ->andReturnSelf()
            ->once()
            ->shouldReceive('with')
            ->with('a.licenceType', 'a_lt')
            ->andReturnSelf()
            ->once()
            ->shouldReceive('byId')
            ->with($inspectionRequestId)
            ->andReturnSelf()
            ->once()
            ->shouldReceive('withRefdata')
            ->andReturnSelf()
            ->once();

        $this->sut->fetchForInspectionRequest($inspectionRequestId, Query::HYDRATE_OBJECT);

        //  check query
        $expect = 'QUERY AND l_ea.id != [[EA-N]]';

        static::assertEquals($expect, $this->query);
    }

    public function testFetchLicenceOperatingCentreCount(): void
    {
        $inspectionRequestId = 1;

        /** @var QueryBuilder $qb */
        $qb = m::mock(QueryBuilder::class);
        $qb->shouldReceive('select')->with('COUNT(m)');

        $qb->shouldReceive('getQuery->getSingleResult')->with(Query::HYDRATE_SINGLE_SCALAR)->once()->andReturn(115);

        $this->queryBuilder->shouldReceive('modifyQuery')
            ->once()
            ->with($qb)
            ->andReturnSelf()
            ->shouldReceive('with')
            ->with('licence', 'l')
            ->andReturnSelf()
            ->once()
            ->shouldReceive('with')
            ->with('l.operatingCentres', 'l_oc')
            ->andReturnSelf()
            ->once()
            ->shouldReceive('with')
            ->with('l_oc.operatingCentre', 'l_oc_oc')
            ->andReturnSelf()
            ->once()
            ->shouldReceive('byId')
            ->with($inspectionRequestId)
            ->andReturnSelf()
            ->once();

        /** @var EntityRepository $repo */
        $repo = m::mock(EntityRepository::class);
        $repo->shouldReceive('createQueryBuilder')
            ->andReturn($qb);

        $this->em->shouldReceive('getRepository')
            ->with(InspectionRequest::class)
            ->andReturn($repo);

        $result = $this->sut->fetchLicenceOperatingCentreCount($inspectionRequestId);
        $this->assertEquals(115, $result);
    }

    public function testFetchPage(): void
    {
        $licenceId = 1;

        $query = m::mock(QueryInterface::class);
        $query->shouldReceive('getId')
            ->andReturn($licenceId);

        /** @var QueryBuilder $qb */
        $qb = m::mock(QueryBuilder::class);

        $qb->shouldReceive('expr->eq')->with('m.licence', ':licence')->once()->andReturn('licence');
        $qb->shouldReceive('andWhere')->with('licence')->once()->andReturnSelf();
        $qb->shouldReceive('setParameter')->with('licence', $licenceId)->once()->andReturnSelf();

        $this->queryBuilder->shouldReceive('modifyQuery')
            ->twice()
            ->with($qb)
            ->andReturnSelf()
            ->shouldReceive('with')
            ->with('licence', 'l')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('with')
            ->with('application', 'a')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('withRefdata')
            ->once()
            ->andReturnSelf();

        /** @var EntityRepository $repo */
        $repo = m::mock(EntityRepository::class);
        $repo->shouldReceive('createQueryBuilder')
            ->andReturn($qb);

        $this->em->shouldReceive('getRepository')
            ->with(InspectionRequest::class)
            ->andReturn($repo);

        $this->sut
            ->shouldReceive('fetchPaginatedList')
            ->with($qb, Query::HYDRATE_OBJECT)
            ->andReturn(['foo'])
            ->once()
            ->shouldReceive('fetchPaginatedCount')
            ->with($qb)
            ->andReturn(1)
            ->once()
            ->getMock();

        $this->assertEquals(['result' => ['foo'], 'count' => 1], $this->sut->fetchPage($query, $licenceId));
    }

    public function testApplyListFilters(): void
    {
        $licenceId = 1;

        $query = m::mock(QueryInterface::class);
        $query->shouldReceive('getLicence')
            ->andReturn($licenceId);

        /** @var QueryBuilder $qb */
        $qb = m::mock(QueryBuilder::class);

        $qb->shouldReceive('expr->eq')->with('m.licence', ':licence')->once()->andReturn('licence');
        $qb->shouldReceive('andWhere')->with('licence')->once()->andReturnSelf();
        $qb->shouldReceive('setParameter')->with('licence', $licenceId)->once()->andReturnSelf();

        $this->assertNull($this->sut->applyListFilters($qb, $query));
    }

    public function testApplyListJoins(): void
    {
        /** @var QueryBuilder $qb */
        $qb = m::mock(QueryBuilder::class);

        $this->queryBuilder->shouldReceive('modifyQuery')
            ->once()
            ->with($qb)
            ->andReturnSelf()
            ->shouldReceive('with')
            ->with('licence', 'l')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('with')
            ->with('application', 'a')
            ->once()
            ->andReturnSelf();

        $this->assertNull($this->sut->applyListJoins($qb));
    }
}
