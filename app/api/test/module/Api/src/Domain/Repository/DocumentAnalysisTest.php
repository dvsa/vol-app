<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Repository\DocumentAnalysis as Repo;
use Dvsa\Olcs\Api\Entity\Doc\DocumentAnalysis as Entity;
use Dvsa\Olcs\Transfer\Query\Document\DocumentAnalysisList;
use Dvsa\OlcsTest\Support\TestQueryBuilder;
use Symfony\Component\Uid\UuidV7;

final class DocumentAnalysisTest extends RepositoryTestCase
{
    private const string FROM = ' FROM ' . Entity::class . ' da';

    /** fetchAnalyses() always fetch-joins the document so callers can read it without a lazy load per row. */
    private const string ANALYSES_SELECT = 'SELECT da, d' . self::FROM . ' INNER JOIN da.document d';

    /** Every status transition is guarded the same way, so a resolved row can never be rewritten. */
    private const string PENDING_GUARD = ' WHERE da.id = :id AND da.status = :pending';

    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(Repo::class, true);
    }

    /**
     * The token is bound as BINARY rather than left to inference: the ORM types any PHP string as
     * STRING, which puts raw uid bytes through the connection's character set.
     */
    public function testFetchByTokenBindsTheTokenAsBinary(): void
    {
        $token = (new UuidV7())->toBinary();
        $entity = new Entity();

        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getOneOrNullResult')
            ->with(Query::HYDRATE_OBJECT)
            ->andReturn($entity);

        $this->assertSame($entity, $this->sut->fetchByToken($token));

        $this->assertSame('SELECT da' . self::FROM . ' WHERE da.token = :token', $qb->getDQL());
        $this->assertSame(Types::BINARY, $qb->getParameter('token')->getType());
        $this->assertSame(1, $qb->getMaxResults());
    }

    public function testFetchByTokenReturnsNullWhenNoRowMatches(): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getOneOrNullResult')->andReturnNull();

        $this->assertNull($this->sut->fetchByToken((new UuidV7())->toBinary()));
    }

    /** A raw uid can contain NUL and other non-UTF-8 bytes; nothing may reinterpret them. */
    public function testFetchByTokenPassesTheTokenBytesThroughUntouched(): void
    {
        $token = hex2bin('0199000000007000800000000000ff00');
        $this->assertIsString($token);

        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getOneOrNullResult')->andReturnNull();

        $this->sut->fetchByToken($token);

        $this->assertSame($token, $qb->getParameter('token')->getValue());
    }

    /**
     * A document is already being analysed if a row is still pending, or succeeded recently
     * enough to be inside the dedupe window.
     */
    public function testFetchDocumentIdsWithActiveAnalysis(): void
    {
        $windowStart = new \DateTimeImmutable('2026-01-01');

        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getArrayResult')
            ->withNoArgs()
            ->andReturn([['documentId' => '4'], ['documentId' => '9']]);

        $this->assertSame(
            [4, 9],
            $this->sut->fetchDocumentIdsWithActiveAnalysis([4, 9], $windowStart),
        );

        $this->assertSame(
            'SELECT IDENTITY(da.document) AS documentId' . self::FROM
            . ' WHERE da.document IN(:documentIds)'
            . ' AND (da.status = :pending'
            . ' OR (da.status = :success AND da.createdOn >= :successWindowStart))',
            $qb->getDQL(),
        );
        $this->assertSame([4, 9], $qb->getParameter('documentIds')->getValue());
        $this->assertSame(Entity::STATUS_PENDING, $qb->getParameter('pending')->getValue());
        $this->assertSame(Entity::STATUS_SUCCESS, $qb->getParameter('success')->getValue());
        $this->assertSame($windowStart, $qb->getParameter('successWindowStart')->getValue());
    }

    /** No ids means nothing to skip; the query is not run at all. */
    public function testFetchDocumentIdsWithActiveAnalysisWithNoDocuments(): void
    {
        $this->assertSame(
            [],
            $this->sut->fetchDocumentIdsWithActiveAnalysis([], new \DateTimeImmutable()),
        );
    }

    public function testFetchStalePending(): void
    {
        $threshold = new \DateTimeImmutable('2026-01-01');

        $qb = $this->createRealQb()->willReturn(['RESULTS']);

        $this->assertSame(['RESULTS'], $this->sut->fetchStalePending($threshold));

        $this->assertSame(
            'SELECT da' . self::FROM
            . ' WHERE da.status = :pending AND da.createdOn < :threshold',
            $qb->getDQL(),
        );
        $this->assertSame($threshold, $qb->getParameter('threshold')->getValue());
    }

    /**
     * The sweeper and the result handler race on the same rows, so each transition is one
     * conditional UPDATE with no read first and no lock.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('transitionProvider')]
    public function testStatusTransitions(
        string $method,
        array $args,
        string $expectedDql,
        array $expectedParameters,
        array $expectedTypes,
    ): void {
        $qb = $this->expectEntityManagerQb();
        $qb->stubbedQuery()->expects('execute')->withNoArgs()->andReturn(1);

        $this->assertSame(1, $this->sut->{$method}(...$args));

        $this->assertSame($expectedDql, $qb->getDQL());

        foreach ($expectedParameters as $name => $expected) {
            $this->assertSame($expected, $qb->getParameter($name)->getValue(), sprintf('parameter %s', $name));
        }

        foreach ($expectedTypes as $name => $expectedType) {
            $this->assertSame($expectedType, $qb->getParameter($name)->getType(), sprintf('type of %s', $name));
        }

        $this->assertInstanceOf(\DateTime::class, $qb->getParameter('now')->getValue());
    }

    public static function transitionProvider(): \Iterator
    {
        yield 'success' => [
            'recordSuccess',
            [5, ['checks' => ['passed' => true]], ['bucket' => 'b', 'key' => 'k']],
            'UPDATE ' . Entity::class . ' da'
            . ' SET da.status = :success, da.result = :result,'
            . ' da.resultMetadata = :metadata, da.completedAt = :now'
            . self::PENDING_GUARD,
            [
                'success' => Entity::STATUS_SUCCESS,
                'result' => ['checks' => ['passed' => true]],
                'metadata' => ['bucket' => 'b', 'key' => 'k'],
                'id' => 5,
                'pending' => Entity::STATUS_PENDING,
            ],
            // The payloads are columns of JSON, not associations to be walked.
            ['result' => Types::JSON, 'metadata' => Types::JSON],
        ];
        yield 'error' => [
            'recordError',
            [7, 'bad JSON'],
            'UPDATE ' . Entity::class . ' da'
            . ' SET da.status = :error, da.errorDetail = :errorDetail, da.completedAt = :now'
            . self::PENDING_GUARD,
            [
                'error' => Entity::STATUS_ERROR,
                'errorDetail' => 'bad JSON',
                'id' => 7,
                'pending' => Entity::STATUS_PENDING,
            ],
            [],
        ];
    }

    /** A row the sweeper resolved first matches nothing, so the handler writes no rows. */
    #[\PHPUnit\Framework\Attributes\DataProvider('noLongerPendingProvider')]
    public function testTransitionsReturnZeroWhenRowNoLongerPending(string $method, array $args): void
    {
        $qb = $this->expectEntityManagerQb();
        $qb->stubbedQuery()->expects('execute')->withNoArgs()->andReturn(0);

        $this->assertSame(0, $this->sut->{$method}(...$args));
    }

    public static function noLongerPendingProvider(): \Iterator
    {
        yield 'success' => ['recordSuccess', [5, [], []]];
        yield 'error' => ['recordError', [7, 'bad JSON']];
        yield 'sweep' => ['sweepStalePending', [new \DateTimeImmutable('2026-01-01')]];
    }

    /**
     * The sweep is the one transition not keyed on a single row: it resolves everything still
     * pending past the threshold.
     */
    public function testSweepStalePending(): void
    {
        $threshold = new \DateTimeImmutable('2026-01-01');

        $qb = $this->expectEntityManagerQb();
        $qb->stubbedQuery()->expects('execute')->withNoArgs()->andReturn(3);

        $this->assertSame(3, $this->sut->sweepStalePending($threshold));

        $this->assertSame(
            'UPDATE ' . Entity::class . ' da'
            . ' SET da.status = :timeout, da.timedOutAt = :now'
            . ' WHERE da.status = :pending AND da.createdOn < :threshold',
            $qb->getDQL(),
        );
        $this->assertSame(Entity::STATUS_TIMEOUT, $qb->getParameter('timeout')->getValue());
        $this->assertSame($threshold, $qb->getParameter('threshold')->getValue());
    }

    private function expectEntityManagerQb(): TestQueryBuilder
    {
        $qb = $this->newRealQb();

        $this->em->expects('createQueryBuilder')->withNoArgs()->andReturn($qb);

        return $qb;
    }

    /**
     * The list goes through the base fetchList(), so paging and ordering come from the query
     * (PagedTrait / OrderedTrait) exactly as they do for DocumentList, and the result is one
     * bounded page rather than every analysis in the table.
     */
    public function testFetchListPagesAndOrdersFromTheQuery(): void
    {
        $query = DocumentAnalysisList::create([
            'application' => 8,
            'page' => 2,
            'limit' => 10,
            'sort' => 'completedAt',
            'order' => 'DESC',
        ]);
        $qb = $this->createRealQb();

        $this->sut->expects('fetchPaginatedList')
            ->with($qb, Query::HYDRATE_OBJECT)
            ->andReturn(new \ArrayIterator(['row1', 'row2']));

        $result = $this->sut->fetchList($query, Query::HYDRATE_OBJECT);

        $this->assertSame(['row1', 'row2'], iterator_to_array($result));

        // Assert the final query so an extra filter or a lost ordering cannot pass unnoticed.
        $this->assertSame(
            self::ANALYSES_SELECT
            . ' WHERE IDENTITY(da.application) = :applicationId'
            . ' ORDER BY da.completedAt DESC',
            $qb->getDQL(),
        );
        $this->assertSame(10, $qb->getFirstResult());
        $this->assertSame(10, $qb->getMaxResults());
        $this->assertCount(1, $qb->getParameters());
        $this->assertSame(8, $qb->getParameter('applicationId')->getValue());
    }

    /**
     * The count runs the same joins and filters as the list (so it matches what the list
     * pages over) but drops the ordering, which only slows a count down.
     */
    public function testFetchCountUsesTheSameFiltersWithoutOrdering(): void
    {
        $query = DocumentAnalysisList::create([
            'application' => 8,
            'status' => Entity::STATUS_SUCCESS,
            'page' => 1,
            'limit' => 10,
            'sort' => 'completedAt',
            'order' => 'DESC',
        ]);
        $qb = $this->createRealQb();

        $this->sut->expects('fetchPaginatedCount')->with($qb)->andReturn(3);

        $this->assertSame(3, $this->sut->fetchCount($query));

        $this->assertSame(
            self::ANALYSES_SELECT
            . ' WHERE IDENTITY(da.application) = :applicationId AND da.status = :status',
            $qb->getDQL(),
        );
        $this->assertSame(8, $qb->getParameter('applicationId')->getValue());
        $this->assertSame(Entity::STATUS_SUCCESS, $qb->getParameter('status')->getValue());
    }

    /**
     * Licence scope follows the analysed document's own licence link (as the documents tab does),
     * or an application on the licence for documents linked only to the application. The left
     * join keeps rows whose application was deleted (application_id SET NULL) but whose document
     * is still linked to the licence.
     */
    public function testFetchListFiltersByLicenceOnly(): void
    {
        $query = DocumentAnalysisList::create(['licence' => 7]);
        $qb = $this->createRealQb();

        $this->sut->expects('fetchPaginatedList')->andReturn(new \ArrayIterator(['row1']));

        $this->assertSame(['row1'], iterator_to_array($this->sut->fetchList($query, Query::HYDRATE_OBJECT)));

        $this->assertSame(
            self::ANALYSES_SELECT
            . ' LEFT JOIN da.application a'
            . ' WHERE IDENTITY(d.licence) = :licenceId OR IDENTITY(a.licence) = :licenceId',
            $qb->getDQL(),
        );
        $this->assertCount(1, $qb->getParameters());
        $this->assertSame(7, $qb->getParameter('licenceId')->getValue());
    }

    /** The OR must stay bracketed, or it would let another status through for the licence. */
    public function testFetchListLicenceFilterCombinesWithStatus(): void
    {
        $query = DocumentAnalysisList::create(['licence' => 7, 'status' => Entity::STATUS_SUCCESS]);
        $qb = $this->createRealQb();

        $this->sut->expects('fetchPaginatedList')->andReturn(new \ArrayIterator([]));

        $this->sut->fetchList($query, Query::HYDRATE_OBJECT);

        $this->assertSame(
            self::ANALYSES_SELECT
            . ' LEFT JOIN da.application a'
            . ' WHERE (IDENTITY(d.licence) = :licenceId OR IDENTITY(a.licence) = :licenceId)'
            . ' AND da.status = :status',
            $qb->getDQL(),
        );
        $this->assertSame(Entity::STATUS_SUCCESS, $qb->getParameter('status')->getValue());
    }

    public function testFetchListFiltersByDocumentOnly(): void
    {
        $query = DocumentAnalysisList::create(['document' => 123]);
        $qb = $this->createRealQb();

        $this->sut->expects('fetchPaginatedList')->andReturn(new \ArrayIterator([]));

        $this->sut->fetchList($query, Query::HYDRATE_OBJECT);

        $this->assertSame(
            self::ANALYSES_SELECT . ' WHERE IDENTITY(da.document) = :documentId',
            $qb->getDQL(),
        );
        $this->assertCount(1, $qb->getParameters());
        $this->assertSame(123, $qb->getParameter('documentId')->getValue());
    }

    public function testFetchListFiltersByStatusOnly(): void
    {
        $query = DocumentAnalysisList::create(['status' => Entity::STATUS_PENDING]);
        $qb = $this->createRealQb();

        $this->sut->expects('fetchPaginatedList')->andReturn(new \ArrayIterator([]));

        $this->sut->fetchList($query, Query::HYDRATE_OBJECT);

        $this->assertSame(
            self::ANALYSES_SELECT . ' WHERE da.status = :status',
            $qb->getDQL(),
        );
        $this->assertCount(1, $qb->getParameters());
        $this->assertSame(Entity::STATUS_PENDING, $qb->getParameter('status')->getValue());
    }

    /**
     * No scope means no WHERE: the query stays reusable for any caller, and it is the page
     * limit (required by the transfer validation) that keeps the result bounded, not a scope.
     */
    public function testFetchListWithNoFiltersSelectsEveryAnalysis(): void
    {
        $query = DocumentAnalysisList::create(['page' => 1, 'limit' => 25]);
        $qb = $this->createRealQb();

        $this->sut->expects('fetchPaginatedList')->andReturn(new \ArrayIterator([]));

        $this->sut->fetchList($query, Query::HYDRATE_OBJECT);

        $this->assertSame(self::ANALYSES_SELECT, $qb->getDQL());
        $this->assertCount(0, $qb->getParameters());
        $this->assertSame(0, $qb->getFirstResult());
        $this->assertSame(25, $qb->getMaxResults());
    }
}
