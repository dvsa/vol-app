<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Repository\InspectionRequest as InspectionRequestRepo;
use Dvsa\Olcs\Api\Entity\EnforcementArea\EnforcementArea;
use Dvsa\Olcs\Api\Entity\Inspection\InspectionRequest as Entity;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Mockery as m;

final class InspectionRequestTest extends RepositoryTestCase
{
    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(InspectionRequestRepo::class, true);
    }

    public function testFetchForInspectionRequest(): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getSingleResult')->with(Query::HYDRATE_ARRAY)->andReturn(['RESULT']);

        $this->assertSame(['RESULT'], $this->sut->fetchForInspectionRequest(1));

        $this->assertSame(
            'SELECT m, l, lt, l_o, l_o_p, l_o_p_p, l_o_tn, l_ccd, l_ccd_a, l_ccd_pc, l_ccd_pc_pct,'
            . ' l_ea, oc, oc_a, a, a_l, a_lt, w0, w1, w2 FROM ' . Entity::class . ' m'
            . ' LEFT JOIN m.licence l LEFT JOIN l.licenceType lt LEFT JOIN l.organisation l_o'
            . ' LEFT JOIN l_o.organisationPersons l_o_p LEFT JOIN l_o_p.person l_o_p_p'
            . ' LEFT JOIN l_o.tradingNames l_o_tn LEFT JOIN l.correspondenceCd l_ccd'
            . ' LEFT JOIN l_ccd.address l_ccd_a LEFT JOIN l_ccd.phoneContacts l_ccd_pc'
            . ' LEFT JOIN l_ccd_pc.phoneContactType l_ccd_pc_pct LEFT JOIN l.enforcementArea l_ea'
            . ' LEFT JOIN m.operatingCentre oc LEFT JOIN oc.address oc_a LEFT JOIN m.application a'
            . ' LEFT JOIN a.licence a_l LEFT JOIN a.licenceType a_lt'
            . ' LEFT JOIN m.requestType w0 LEFT JOIN m.resultType w1 LEFT JOIN m.reportType w2'
            . ' WHERE m.id = :byId AND l_ea.id <> :enforcementArea',
            $qb->getDQL(),
        );
        $this->assertSame(1, $qb->getParameter('byId')->getValue());
        $this->assertSame(
            EnforcementArea::NORTHERN_IRELAND_ENFORCEMENT_AREA_CODE,
            $qb->getParameter('enforcementArea')->getValue(),
        );
    }

    /**
     * A Northern Ireland enforcement area matches nothing, which the repository treats as an
     * empty result rather than an error.
     */
    public function testFetchForInspectionRequestReturnsEmptyWhenNothingMatches(): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getSingleResult')->andThrow(new NoResultException());

        $this->assertSame([], $this->sut->fetchForInspectionRequest(1));
    }

    public function testFetchLicenceOperatingCentreCount(): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getSingleResult')->with(Query::HYDRATE_SINGLE_SCALAR)->andReturn(115);

        $this->assertSame(115, $this->sut->fetchLicenceOperatingCentreCount(1));

        // select('COUNT(m)') runs last and replaces the joined select list; the joins remain.
        $this->assertSame(
            'SELECT COUNT(m) FROM ' . Entity::class . ' m'
            . ' LEFT JOIN m.licence l LEFT JOIN l.operatingCentres l_oc'
            . ' LEFT JOIN l_oc.operatingCentre l_oc_oc'
            . ' WHERE m.id = :byId',
            $qb->getDQL(),
        );
    }

    public function testApplyListFilters(): void
    {
        $qb = $this->createRealQb();

        $query = m::mock(QueryInterface::class);
        $query->shouldReceive('getLicence')->andReturn(1);

        $this->sut->applyListFilters($qb, $query);

        $this->assertSame(
            'SELECT m FROM ' . Entity::class . ' m WHERE m.licence = :licence',
            $qb->getDQL(),
        );
        $this->assertSame(1, $qb->getParameter('licence')->getValue());
    }

    public function testApplyListJoins(): void
    {
        $qb = $this->createRealQb();

        $this->sut->applyListJoins($qb);

        $this->assertSame(
            'SELECT m, l, a FROM ' . Entity::class . ' m LEFT JOIN m.licence l LEFT JOIN m.application a',
            $qb->getDQL(),
        );
    }

    public function testFetchPage(): void
    {
        $query = m::mock(QueryInterface::class);
        $query->shouldReceive('getId')->andReturn(1);
        $query->shouldReceive('getLicence')->andReturn(1);

        $qb = $this->createRealQb();

        $this->sut->expects('fetchPaginatedList')->with($qb, Query::HYDRATE_OBJECT)->andReturn(['foo']);
        $this->sut->expects('fetchPaginatedCount')->with($qb)->andReturn(1);

        $this->assertSame(['result' => ['foo'], 'count' => 1], $this->sut->fetchPage($query, 1));

        $this->assertSame(
            // buildDefaultListQuery() runs withRefdata() first, so the refdata joins precede
            // the licence and application ones fetchPage() adds afterwards.
            'SELECT m, w0, w1, w2, l, a FROM ' . Entity::class . ' m'
            . ' LEFT JOIN m.requestType w0 LEFT JOIN m.resultType w1 LEFT JOIN m.reportType w2'
            . ' LEFT JOIN m.licence l LEFT JOIN m.application a'
            . ' WHERE m.licence = :licence',
            $qb->getDQL(),
        );
    }
}
