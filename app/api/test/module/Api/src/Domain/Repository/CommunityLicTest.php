<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Dvsa\Olcs\Api\Domain\Repository\CommunityLic as Repo;
use Dvsa\Olcs\Api\Entity\CommunityLic\CommunityLic as Entity;
use Dvsa\Olcs\Transfer\Query\CommunityLic\CommunityLicences as CommunityLicencesDTO;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Mockery as m;

final class CommunityLicTest extends RepositoryTestCase
{
    private const string FROM = ' FROM ' . Entity::class . ' m';

    /** The four statuses treated as "in use" by both office-copy and valid-licence lookups. */
    private const string STATUS_ALTERNATION = '(m.status = :pending OR m.status = :active'
        . ' OR m.status = :withdrawn OR m.status = :suspended)';

    private const string SUSPENSION_JOINS = ' INNER JOIN m.communityLicSuspensions s'
        . ' INNER JOIN s.communityLicSuspensionReasons sr';

    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(Repo::class, true);
    }

    public function testFetchOfficeCopy(): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getOneOrNullResult')->andReturn('RESULT');

        $this->assertSame('RESULT', $this->sut->fetchOfficeCopy(1));

        // The office copy is the one with issue number zero.
        $this->assertSame(
            'SELECT m' . self::FROM
            . ' WHERE m.licence = :licence AND m.issueNo = :issueNo AND ' . self::STATUS_ALTERNATION,
            $qb->getDQL(),
        );
        $this->assertSame(1, $qb->getParameter('licence')->getValue());
        $this->assertSame(0, $qb->getParameter('issueNo')->getValue());
        $this->assertStatusParameters($qb);
    }

    public function testFetchValidLicences(): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('execute')->withNoArgs()->andReturn(['RESULTS']);

        $this->assertSame(['RESULTS'], $this->sut->fetchValidLicences(1));

        // Everything except the office copy, so issueNo is compared with <>.
        $this->assertSame(
            'SELECT m' . self::FROM
            . ' WHERE m.issueNo <> :issueNo AND m.licence = :licence AND ' . self::STATUS_ALTERNATION
            . ' ORDER BY m.issueNo ASC',
            $qb->getDQL(),
        );
        $this->assertStatusParameters($qb);
    }

    public function testFetchLicencesByIds(): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('execute')->withNoArgs()->andReturn(['RESULTS']);

        $this->assertSame(['RESULTS'], $this->sut->fetchLicencesByIds([1, 2]));

        $this->assertSame('SELECT m' . self::FROM . ' WHERE m.id IN(:ids)', $qb->getDQL());
        $this->assertSame([1, 2], $qb->getParameter('ids')->getValue());
    }

    /**
     * A comma separated status list becomes one parameter per status, ORed together.
     *
     * Note applyListFilters() calls getStatuses() unguarded, so only the plural list query
     * (CommunityLicences) can be passed — the singular CommunityLicence has no such method.
     */
    public function testApplyListFilters(): void
    {
        $qb = $this->createRealQb();

        $query = CommunityLicencesDTO::create(['licence' => 7, 'statuses' => 'cl_sts_active,cl_sts_pending']);

        $this->sut->applyListFilters($qb, $query);

        $this->assertSame(
            'SELECT m' . self::FROM
            . ' WHERE (m.status = :status0 OR m.status = :status1) AND m.licence = :licence',
            $qb->getDQL(),
        );
        $this->assertSame('cl_sts_active', $qb->getParameter('status0')->getValue());
        $this->assertSame('cl_sts_pending', $qb->getParameter('status1')->getValue());
        $this->assertSame(7, $qb->getParameter('licence')->getValue());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('expireProvider')]
    public function testExpireAllForLicence(?string $status, array $expectedParams): void
    {
        $query = m::mock();
        $query->expects('execute')->with($expectedParams);

        $this->dbQueryService->expects('get')
            ->with('CommunityLicence\ExpireAllForLicence')
            ->andReturn($query);

        $this->sut->expireAllForLicence(1, $status);
    }

    public static function expireProvider(): \Iterator
    {
        yield 'with a status' => ['cl_sts_active', ['licence' => 1, 'status' => 'cl_sts_active']];
        yield 'without a status' => [null, ['licence' => 1]];
    }

    /**
     * A licence is suspendable when an active suspension has started and has not yet ended.
     */
    public function testFetchForSuspension(): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('execute')->withNoArgs()->andReturn(['RESULTS']);

        $this->assertSame(['RESULTS'], $this->sut->fetchForSuspension('2015-01-01'));

        $this->assertSame(
            'SELECT m'
            . self::FROM
            . ' LEFT JOIN m.communityLicSuspensions s'
            . ' LEFT JOIN s.communityLicSuspensionReasons sr'
            . ' INNER JOIN m.licence l'
            . ' WHERE m.status = :status'
            . ' AND (l.status = :licenceStatus'
            . ' OR (s.startDate <= :startDate'
            . ' AND (s.endDate IS NULL OR s.endDate > :endDate)))',
            $qb->getDQL(),
        );

        $this->assertSame(Entity::STATUS_ACTIVE, $qb->getParameter('status')->getValue());
        $this->assertSame(
            Licence::LICENCE_STATUS_SUSPENDED,
            $qb->getParameter('licenceStatus')->getValue(),
        );
        $this->assertSame('2015-01-01', $qb->getParameter('startDate')->getValue());
        $this->assertSame('2015-01-01', $qb->getParameter('endDate')->getValue());
    }

    public function testFetchForActivation(): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('execute')->withNoArgs()->andReturn(['RESULTS']);

        $this->assertSame(['RESULTS'], $this->sut->fetchForActivation('2015-01-01'));

        $this->assertSame(
            'SELECT m' . self::FROM . self::SUSPENSION_JOINS
            . ' WHERE m.status = :status AND s.endDate <= :endDate',
            $qb->getDQL(),
        );
        $this->assertSame(Entity::STATUS_SUSPENDED, $qb->getParameter('status')->getValue());
    }

    /**
     * The count excludes the office copy (issueNo zero) and counts only active licences.
     */
    public function testCountActiveByLicenceId(): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getSingleScalarResult')->andReturn('4');

        $this->assertSame(4, $this->sut->countActiveByLicenceId(7));

        $this->assertSame(
            'SELECT COUNT(m.id)' . self::FROM
            . ' WHERE m.issueNo <> :issueNo AND m.licence = :licence AND m.status = :status',
            $qb->getDQL(),
        );
        $this->assertSame(7, $qb->getParameter('licence')->getValue());
        $this->assertSame(0, $qb->getParameter('issueNo')->getValue());
        $this->assertSame(Entity::STATUS_ACTIVE, $qb->getParameter('status')->getValue());
    }

    private function assertStatusParameters(mixed $qb): void
    {
        $this->assertSame(Entity::STATUS_PENDING, $qb->getParameter('pending')->getValue());
        $this->assertSame(Entity::STATUS_ACTIVE, $qb->getParameter('active')->getValue());
        $this->assertSame(Entity::STATUS_WITHDRAWN, $qb->getParameter('withdrawn')->getValue());
        $this->assertSame(Entity::STATUS_SUSPENDED, $qb->getParameter('suspended')->getValue());
    }
}
