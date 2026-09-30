<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\Repository\TransportManagerApplication as Repo;
use Dvsa\Olcs\Api\Entity\Tm\TransportManagerApplication as Entity;
use Dvsa\Olcs\Transfer\Query\TransportManagerApplication\GetList;

final class TransportManagerApplicationTest extends RepositoryTestCase
{
    private const string FROM = ' FROM ' . Entity::class . ' tma';

    /** withRefdata() joins tmType, tmSignatureType, opSignatureType and tmApplicationStatus. */
    private const string REFDATA_JOINS = ' LEFT JOIN tma.tmType w0 LEFT JOIN tma.tmSignatureType w1'
        . ' LEFT JOIN tma.opSignatureType w2 LEFT JOIN tma.tmApplicationStatus w3';

    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(Repo::class, true);
    }

    /**
     * An explicit select() of scalar columns, so this returns rows rather than entities.
     */
    public function testFetchWithContactDetailsByApplication(): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getResult')->with(Query::HYDRATE_ARRAY)->andReturn(['RESULTS']);

        $this->assertSame(['RESULTS'], $this->sut->fetchWithContactDetailsByApplication(7));

        $this->assertSame(
            'SELECT tma.id, tma.action, tm.id as tmid,'
            . ' tmas.id as tmasid, tmas.description as tmasdesc, hcd.emailAddress,'
            . ' hp.birthDate, hp.forename, hp.familyName'
            . self::FROM
            . ' LEFT JOIN tma.transportManager tm LEFT JOIN tma.tmApplicationStatus tmas'
            . ' LEFT JOIN tm.homeCd hcd LEFT JOIN hcd.person hp'
            . ' WHERE tma.application = :applicationId',
            $qb->getDQL(),
        );
        $this->assertSame(7, $qb->getParameter('applicationId')->getValue());
    }

    public function testFetchDetails(): void
    {
        $qb = $this->createRealQb()->willReturn(['RESULT']);

        $this->assertSame('RESULT', $this->sut->fetchDetails(1));

        $this->assertSame(
            'SELECT tma, w0, w1, w2, w3, a, ol, w4, gop, w5, w6, tm, hcd, hadd, w7, hp, wcd, wadd, w8'
            . self::FROM . self::REFDATA_JOINS
            . ' LEFT JOIN tma.application a LEFT JOIN tma.otherLicences ol LEFT JOIN ol.role w4'
            . ' LEFT JOIN a.goodsOrPsv gop LEFT JOIN a.licence w5 LEFT JOIN a.status w6'
            . ' LEFT JOIN tma.transportManager tm LEFT JOIN tm.homeCd hcd'
            . ' LEFT JOIN hcd.address hadd LEFT JOIN hadd.countryCode w7 LEFT JOIN hcd.person hp'
            . ' LEFT JOIN tm.workCd wcd LEFT JOIN wcd.address wadd LEFT JOIN wadd.countryCode w8'
            . ' WHERE tma.id = :byId',
            $qb->getDQL(),
        );
    }

    public function testJoinTmContactDetailsBindsItsOwnQuery(): void
    {
        $qb = $this->createRealQb();
        $previous = $this->newRealQb();
        $previous->select('other')->from(Entity::class, 'other');
        $this->queryBuilder->modifyQuery($previous);

        $this->sut->joinTmContactDetails($qb);

        $this->assertStringContainsString('LEFT JOIN tma.transportManager tm', $qb->getDQL());
        $this->assertStringContainsString('LEFT JOIN tm.homeCd hcd', $qb->getDQL());
        $this->assertStringContainsString('LEFT JOIN tm.workCd wcd', $qb->getDQL());
        $this->assertSame('SELECT other FROM ' . Entity::class . ' other', $previous->getDQL());
    }

    public function testFetchDetailsNotFound(): void
    {
        $this->createRealQb()->willReturn([]);

        $this->expectException(NotFoundException::class);

        $this->sut->fetchDetails(1);
    }

    public function testApplyListJoins(): void
    {
        $qb = $this->createRealQb();

        $previous = $this->newRealQb();
        $previous->select('other')->from(Entity::class, 'other');
        $this->queryBuilder->modifyQuery($previous);

        $this->sut->applyListJoins($qb);

        $this->assertSame(
            'SELECT tma, a, l' . self::FROM
            . ' LEFT JOIN tma.application a LEFT JOIN a.licence l',
            $qb->getDQL(),
        );
        $this->assertSame('SELECT other FROM ' . Entity::class . ' other', $previous->getDQL());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('listFilterProvider')]
    public function testApplyListFilters(array $data, string $expectedTail): void
    {
        $qb = $this->createRealQb();

        $this->sut->applyListFilters($qb, GetList::create($data));

        $this->assertSame('SELECT tma' . self::FROM . $expectedTail, $qb->getDQL());
    }

    public static function listFilterProvider(): \Iterator
    {
        yield 'by user' => [
            ['user' => 73],
            ' INNER JOIN tma.transportManager tm INNER JOIN tm.users u WHERE u.id = :user',
        ];
        yield 'by application' => [['application' => 73], ' WHERE tma.application = :application'];
        yield 'by transport manager' => [
            ['transportManager' => 73],
            ' WHERE tma.transportManager = :transportManager',
        ];
        // a.status belongs to the join applyListJoins() adds, not to this method.
        yield 'by application statuses' => [
            ['appStatuses' => ['apsts_new']],
            ' WHERE a.status IN(:appStatuses)',
        ];
        // filterByOrgUser only takes effect alongside a user, and pulls in the organisation
        // chain hanging off the licence alias applyListJoins() creates.
        yield 'filtered by organisation user' => [
            ['user' => 73, 'filterByOrgUser' => 'Y'],
            ' INNER JOIN tma.transportManager tm INNER JOIN tm.users u'
            . ' INNER JOIN l.organisation o INNER JOIN o.organisationUsers ou'
            . ' INNER JOIN ou.user ouu'
            . ' WHERE u.id = :user AND ouu.id = :orgUsersUser',
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('forTransportManagerProvider')]
    public function testFetchForTransportManager(
        ?array $statuses,
        bool $includeDeleted,
        string $expectedExtra,
    ): void {
        $qb = $this->createRealQb()->willReturn(['RESULTS']);

        $this->assertSame(
            ['RESULTS'],
            $this->sut->fetchForTransportManager(3, $statuses, $includeDeleted),
        );

        $this->assertSame(
            'SELECT tma, tmt, a, al, alo, ast, tm, tmast' . self::FROM
            . ' LEFT JOIN tma.tmType tmt LEFT JOIN tma.application a LEFT JOIN a.licence al'
            . ' LEFT JOIN al.organisation alo LEFT JOIN a.status ast'
            . ' LEFT JOIN tma.transportManager tm LEFT JOIN tma.tmApplicationStatus tmast'
            . ' WHERE tma.transportManager = :transportManager' . $expectedExtra,
            $qb->getDQL(),
        );
    }

    public static function forTransportManagerProvider(): \Iterator
    {
        yield 'excluding deleted' => [null, false, " AND tma.action <> :action"];
        yield 'including deleted' => [null, true, ''];
        // The statuses are inlined into the IN() rather than bound.
        yield 'narrowed by status' => [
            ['apsts_new'],
            true,
            " AND a.status IN('apsts_new')",
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('ignoreDeletedProvider')]
    public function testFetchByTmAndApplication(bool $ignoreDeleted, string $expectedExtra): void
    {
        $qb = $this->createRealQb()->willReturn(['RESULTS']);

        $this->assertSame(['RESULTS'], $this->sut->fetchByTmAndApplication(3, 7, $ignoreDeleted));

        $this->assertSame(
            'SELECT tma' . self::FROM
            . ' WHERE tma.transportManager = :tmId AND tma.application = :applicationId'
            . $expectedExtra,
            $qb->getDQL(),
        );
    }

    public static function ignoreDeletedProvider(): \Iterator
    {
        yield 'including deleted' => [false, ''];
        yield 'ignoring deleted' => [true, ' AND tma.action <> :action'];
    }

    public function testFetchForResponsibilities(): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getSingleResult')->andReturn('RESULT');

        $this->assertSame('RESULT', $this->sut->fetchForResponsibilities(1));

        $this->assertSame(
            'SELECT tma, a, tmty, al, alo, ast, tm, tmt, tmast' . self::FROM
            . ' LEFT JOIN tma.application a LEFT JOIN tma.tmType tmty LEFT JOIN a.licence al'
            . ' LEFT JOIN al.organisation alo LEFT JOIN a.status ast'
            . ' LEFT JOIN tma.transportManager tm LEFT JOIN tm.tmType tmt'
            . ' LEFT JOIN tma.tmApplicationStatus tmast'
            . ' WHERE tma.id = :byId',
            $qb->getDQL(),
        );
    }

    /**
     * One row per application carrying a conditional count for each action, defaulted to zero
     * so callers always get all three keys.
     */
    public function testFetchStatByAppId(): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getOneOrNullResult')->with(Query::HYDRATE_ARRAY)->andReturn(['A' => 2]);

        $this->assertSame(
            ['action' => ['A' => 2, 'U' => 0, 'D' => 0]],
            $this->sut->fetchStatByAppId(7),
        );

        $this->assertSame(
            "SELECT tma.id, SUM(CASE WHEN tma.action = 'A' THEN 1 ELSE 0 END) AS A,"
            . " SUM(CASE WHEN tma.action = 'U' THEN 1 ELSE 0 END) AS U,"
            . " SUM(CASE WHEN tma.action = 'D' THEN 1 ELSE 0 END) AS D"
            . self::FROM
            . ' WHERE tma.application = :applicationId'
            . ' GROUP BY tma.application',
            $qb->getDQL(),
        );
    }

    public function testFetchStatByAppIdWithNoRows(): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getOneOrNullResult')->andReturnNull();

        $this->assertSame(
            ['action' => ['A' => 0, 'U' => 0, 'D' => 0]],
            $this->sut->fetchStatByAppId(7),
        );
    }
}
