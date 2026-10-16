<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\DBAL\LockMode;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\Repository\Cases as Repo;
use Dvsa\Olcs\Api\Entity\Cases\Cases as Entity;
use Dvsa\Olcs\Transfer\Query\Cases\ByLicence;
use Dvsa\Olcs\Transfer\Query\Cases\ByTransportManager;
use Dvsa\Olcs\Transfer\Query as TransferQry;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Mockery as m;

final class CasesTest extends RepositoryTestCase
{
    private const string FROM = ' FROM ' . Entity::class . ' m';

    /** withRefdata() joins caseType, categorys and outcomes. */
    private const string REFDATA = ' LEFT JOIN m.caseType w0 LEFT JOIN m.categorys w1'
        . ' LEFT JOIN m.outcomes w2';

    private const string HIDDEN_CASE_TYPE = 'CONCAT(ct.description, m.id) as HIDDEN caseType';

    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(Repo::class, true);
    }

    /**
     * m.caseType is joined twice: w0 by withRefdata and ct explicitly. The explicit alias is
     * what the HIDDEN caseType select needs — see the migration findings.
     */
    public function testBuildDefaultListQuery(): void
    {
        $qb = $this->createRealQb();

        $this->sut->buildDefaultListQuery($qb, m::mock(QueryInterface::class));

        $this->assertSame(
            'SELECT m, w0, w1, w2, ct, ' . self::HIDDEN_CASE_TYPE . self::FROM . self::REFDATA
            . ' LEFT JOIN m.caseType ct',
            $qb->getDQL(),
        );
    }

    /**
     * The filters are gated on method_exists(), so each one needs a query class that actually
     * declares the getter — a bare QueryInterface mock skips them all.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('simpleListFilterProvider')]
    public function testApplyListFilters(
        string $queryClass,
        string $getter,
        string $expectedWhere,
        string $parameter,
    ): void {
        $qb = $this->createRealQb();

        $query = m::mock($queryClass);
        $query->shouldReceive($getter)->andReturn(42);

        $this->sut->applyListFilters($qb, $query);

        $this->assertSame('SELECT m' . self::FROM . ' WHERE ' . $expectedWhere, $qb->getDQL());
        $this->assertSame(42, $qb->getParameter($parameter)->getValue());
    }

    public static function simpleListFilterProvider(): \Iterator
    {
        yield 'transport manager' => [
            ByTransportManager::class,
            'getTransportManager',
            'm.transportManager = :byTransportManager',
            'byTransportManager',
        ];
        yield 'licence' => [ByLicence::class, 'getLicence', 'm.licence = :byLicence', 'byLicence'];
    }

    /**
     * The open-case report joins licence, application and traffic area, then narrows on the
     * statuses and traffic areas supplied.
     */
    public function testFetchListForTheOpenCaseReport(): void
    {
        $qb = $this->createRealQb();

        $query = m::mock(TransferQry\Cases\Report\OpenList::class)->makePartial();
        $query->shouldReceive('getCaseType')->andReturn('unit_CaseType')
            ->shouldReceive('getApplicationStatus')->andReturn('unit_AppStatus')
            ->shouldReceive('getLicenceStatus')->andReturn('unit_LicStatus')
            ->shouldReceive('getTrafficAreas')->andReturn(['unit_TA']);

        $this->sut->expects('fetchPaginatedList')->andReturn('EXPECT');

        $this->assertSame('EXPECT', $this->sut->fetchList($query));

        $this->assertSame(
            // buildDefaultListQuery() adds the HIDDEN select before applyListJoins() adds the
            // licence, application and traffic-area aliases.
            'SELECT m, w0, w1, w2, ct, ' . self::HIDDEN_CASE_TYPE . ', l, a, ta' . self::FROM . self::REFDATA
            . ' LEFT JOIN m.caseType ct LEFT JOIN m.licence l LEFT JOIN m.application a'
            . ' LEFT JOIN l.trafficArea ta'
            . ' WHERE m.caseType = :CASE_TYPE AND m.closedDate IS NULL'
            . ' AND a.status = :APP_STATUS AND l.status = :LIC_STATUS'
            . ' AND ta.id IN(:trafficAreas)',
            $qb->getDQL(),
        );
        $this->assertSame(['unit_TA'], $qb->getParameter('trafficAreas')->getValue());
    }

    /**
     * 'other' is not a traffic area id — it is removed from the list and turned into an IS NULL
     * alternative, so cases with no traffic area are included. Note the remaining array keeps
     * its original keys.
     */
    public function testFetchListForTheOpenCaseReportWithOtherTrafficArea(): void
    {
        $qb = $this->createRealQb();

        $query = m::mock(TransferQry\Cases\Report\OpenList::class)->makePartial();
        $query->shouldReceive('getCaseType')->andReturnNull()
            ->shouldReceive('getApplicationStatus')->andReturnNull()
            ->shouldReceive('getLicenceStatus')->andReturnNull()
            ->shouldReceive('getTrafficAreas')->andReturn(['other', 'A', 'B']);

        $this->sut->expects('fetchPaginatedList')->andReturn('EXPECT');

        $this->assertSame('EXPECT', $this->sut->fetchList($query));

        $this->assertStringEndsWith(
            ' WHERE m.closedDate IS NULL AND (ta.id IS NULL OR ta.id IN(:trafficAreas))',
            $qb->getDQL(),
        );
        $this->assertSame([1 => 'A', 2 => 'B'], $qb->getParameter('trafficAreas')->getValue());
    }

    public function testFetchWithLicenceUsingId(): void
    {
        $result = m::mock(Entity::class);

        $query = m::mock(QueryInterface::class);
        $query->shouldReceive('getId')->andReturn(1);

        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getResult')->with(Query::HYDRATE_OBJECT)->andReturn([$result]);
        $this->em->expects('lock')->with($result, LockMode::OPTIMISTIC, 1);

        $this->assertSame($result, $this->sut->fetchWithLicenceUsingId($query, Query::HYDRATE_OBJECT, 1));

        $this->assertSame(
            'SELECT m, w0, w1, w2, l, loc, oc, w3' . self::FROM . self::REFDATA
            . ' LEFT JOIN m.licence l LEFT JOIN l.operatingCentres loc'
            . ' LEFT JOIN loc.operatingCentre oc LEFT JOIN oc.address w3'
            . ' WHERE m.id = :byId',
            $qb->getDQL(),
        );
    }

    public function testFetchWithLicenceUsingIdNotFound(): void
    {
        $query = m::mock(QueryInterface::class);
        $query->shouldReceive('getId')->andReturn(1);

        $this->createRealQb()->stubbedQuery()->expects('getResult')->andReturn([]);

        $this->expectException(NotFoundException::class);

        $this->sut->fetchWithLicenceUsingId($query);
    }

    public function testFetchExtended(): void
    {
        $qb = $this->createRealQb()->willReturn(['RESULT']);

        $this->assertSame('RESULT', $this->sut->fetchExtended(24));

        $this->assertSame(
            'SELECT m, l, a, tm' . self::FROM
            . ' LEFT JOIN m.licence l LEFT JOIN m.application a LEFT JOIN m.transportManager tm'
            . ' WHERE m.id = :byId',
            $qb->getDQL(),
        );
        $this->assertSame(24, $qb->getParameter('byId')->getValue());
    }

    public function testFetchExtendedNotFound(): void
    {
        $this->createRealQb()->willReturn([]);

        $this->expectException(NotFoundException::class);

        $this->sut->fetchExtended(24);
    }

    public function testFetchOpenCasesForSurrender(): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getResult')->with(Query::HYDRATE_OBJECT)->andReturn(['RESULTS']);

        $query = m::mock(QueryInterface::class);
        $query->shouldReceive('getId')->andReturn(95);

        $this->assertSame(['RESULTS'], $this->sut->fetchOpenCasesForSurrender($query));

        $this->assertSame(
            'SELECT m, w0, w1, w2, ct, ' . self::HIDDEN_CASE_TYPE . self::FROM . self::REFDATA
            . ' LEFT JOIN m.caseType ct'
            . ' WHERE m.licence = :byLicence AND m.closedDate IS NULL',
            $qb->getDQL(),
        );
        $this->assertSame(95, $qb->getParameter('byLicence')->getValue());
    }

    public function testFetchOpenCasesForApplication(): void
    {
        $qb = $this->createRealQb()->willReturn(['RESULTS']);

        $this->assertSame(['RESULTS'], $this->sut->fetchOpenCasesForApplication(7));

        $this->assertSame(
            'SELECT m, a' . self::FROM . ' LEFT JOIN m.application a'
            . ' WHERE a.id = :byApplication AND m.closedDate IS NULL',
            $qb->getDQL(),
        );
        $this->assertSame(7, $qb->getParameter('byApplication')->getValue());
    }
}
