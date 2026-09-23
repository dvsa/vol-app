<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Repository\DocumentAnalysis as Repo;
use Dvsa\Olcs\Api\Entity\Doc\DocumentAnalysis as Entity;
use Dvsa\OlcsTest\Support\TestQueryBuilder;
use Symfony\Component\Uid\UuidV7;

final class DocumentAnalysisTest extends RepositoryTestCase
{
    private const string FROM = ' FROM ' . Entity::class . ' da';

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
}
