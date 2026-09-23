<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Query\Bus\TxcInboxList;
use Dvsa\Olcs\Api\Domain\Repository\TxcInbox as Repo;
use Dvsa\Olcs\Api\Entity\Ebsr\TxcInbox as Entity;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Mockery as m;

final class TxcInboxTest extends RepositoryTestCase
{
    private const string FROM = ' FROM ' . Entity::class . ' m';

    private const string BUS_REG_JOIN = ' LEFT JOIN m.busReg b';

    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(Repo::class, true);
    }

    public function testFetchByOrganisation(): void
    {
        $qb = $this->createRealQb()->willReturn(['RESULTS']);

        $this->assertSame(['RESULTS'], $this->sut->fetchByOrganisation(1));

        $this->assertSame(
            'SELECT m' . self::FROM . ' WHERE m.organisation = :organisation',
            $qb->getDQL(),
        );
        $this->assertSame(1, $qb->getParameter('organisation')->getValue());
    }

    /**
     * An operator only ever sees its own inbox rows, which are the ones with no local authority.
     */
    public function testFetchListForOrganisationByBusReg(): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getResult')->with(Query::HYDRATE_OBJECT)->andReturn(['RESULTS']);

        $this->assertSame(['RESULTS'], $this->sut->fetchListForOrganisationByBusReg(1, 2));

        $this->assertSame(
            'SELECT m, b' . self::FROM . self::BUS_REG_JOIN
            . ' WHERE b.id = :busReg AND m.localAuthority IS NULL'
            . ' AND m.organisation = :organisation',
            $qb->getDQL(),
        );
        $this->assertSame(1, $qb->getParameter('busReg')->getValue());
        $this->assertSame(2, $qb->getParameter('organisation')->getValue());
    }

    /**
     * With no local authority the query degenerates to the operator's own rows; with one it also
     * restricts to unread files.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('localAuthorityProvider')]
    public function testFetchListForLocalAuthorityByBusReg(?int $localAuthorityId, string $expectedExtra): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getResult')->with(Query::HYDRATE_OBJECT)->andReturn(['RESULTS']);

        $this->assertSame(
            ['RESULTS'],
            $this->sut->fetchListForLocalAuthorityByBusReg(1, $localAuthorityId),
        );

        $this->assertSame(
            'SELECT m, b' . self::FROM . self::BUS_REG_JOIN
            . ' WHERE b.id = :busReg' . $expectedExtra,
            $qb->getDQL(),
        );
    }

    public static function localAuthorityProvider(): \Iterator
    {
        yield 'no local authority' => [null, ' AND m.localAuthority IS NULL'];
        yield 'a local authority' => [
            3,
            // The '0' is passed as a literal operand, so it lands in the DQL unquoted.
            ' AND m.fileRead = 0 AND m.localAuthority = :localAuthority',
        ];
    }

    public function testBuildDefaultListQuery(): void
    {
        $qb = $this->createRealQb();

        $this->sut->buildDefaultListQuery($qb, m::mock(QueryInterface::class));

        $this->assertSame(
            'SELECT m, b, e, l, w0, w1' . self::FROM . self::BUS_REG_JOIN
            . ' LEFT JOIN b.ebsrSubmissions e LEFT JOIN b.licence l'
            . ' LEFT JOIN b.otherServices w0 LEFT JOIN l.organisation w1',
            $qb->getDQL(),
        );
    }

    /**
     * Unread files only — fileRead is always applied, whatever else the query asks for.
     */
    public function testApplyListFilters(): void
    {
        $qb = $this->createRealQb();

        $query = TxcInboxList::create(['localAuthority' => 3, 'subType' => 'bar', 'status' => 'foo']);

        $this->sut->applyListFilters($qb, $query);

        $this->assertSame(
            'SELECT m' . self::FROM
            . ' WHERE m.localAuthority = :localAuthority AND b.status = :status'
            . ' AND e.ebsrSubmissionType = :ebsrSubmissionType'
            . ' AND m.fileRead = 0',
            $qb->getDQL(),
        );
        $this->assertSame(3, $qb->getParameter('localAuthority')->getValue());
        $this->assertSame('foo', $qb->getParameter('status')->getValue());
        $this->assertSame('bar', $qb->getParameter('ebsrSubmissionType')->getValue());
    }

    public function testFetchLinkedToDocument(): void
    {
        $qb = $this->createRealQb()->willReturn(['RESULTS']);

        $this->assertSame(['RESULTS'], $this->sut->fetchLinkedToDocument(5));

        $this->assertSame(
            'SELECT m' . self::FROM
            . ' WHERE m.zipDocument = :documentId OR m.routeDocument = :documentId'
            . ' OR m.pdfDocument = :documentId',
            $qb->getDQL(),
        );
        $this->assertSame(5, $qb->getParameter('documentId')->getValue());
    }

    public function testFetchByIdsForLocalAuthority(): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getResult')->with(Query::HYDRATE_OBJECT)->andReturn(['RESULTS']);

        $this->assertSame(['RESULTS'], $this->sut->fetchByIdsForLocalAuthority([1, 2], 3));

        // The ids are inlined into the IN() rather than bound.
        $this->assertSame(
            'SELECT m' . self::FROM
            . ' WHERE m.localAuthority = :localAuthority AND m.id IN(1, 2)',
            $qb->getDQL(),
        );
        $this->assertSame(3, $qb->getParameter('localAuthority')->getValue());
    }

    public function testFetchByIdsForLocalAuthorityWithNoIds(): void
    {
        $this->assertSame([], $this->sut->fetchByIdsForLocalAuthority([], 3));
    }
}
