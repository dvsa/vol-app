<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Repository\EventHistory as Repo;
use Dvsa\Olcs\Api\Entity\EventHistory\EventHistory as Entity;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Mockery as m;

final class EventHistoryTest extends RepositoryTestCase
{
    private const string FROM = ' FROM ' . Entity::class . ' m';

    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(Repo::class, true);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('singleFilterProvider')]
    public function testFetchByColumn(string $method, string $expectedWhere, string $parameter): void
    {
        $qb = $this->createRealQb()->willReturn(['RESULTS']);

        $this->assertSame(['RESULTS'], $this->sut->{$method}(1));

        $this->assertSame('SELECT m' . self::FROM . ' WHERE ' . $expectedWhere, $qb->getDQL());
        $this->assertSame(1, $qb->getParameter($parameter)->getValue());
    }

    public static function singleFilterProvider(): \Iterator
    {
        // The organisation parameter name carries a typo in the repository.
        yield 'by organisation' => ['fetchByOrganisation', 'm.organisation = :organisaion', 'organisaion'];
        yield 'by transport manager' => [
            'fetchByTransportManager',
            'm.transportManager = :transportManager',
            'transportManager',
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('accountProvider')]
    public function testFetchByAccount(array $args, string $expectedTail, ?int $expectedMaxResults): void
    {
        $qb = $this->createRealQb()->willReturn(['RESULTS']);

        $this->assertSame(['RESULTS'], $this->sut->fetchByAccount(...$args));

        $this->assertSame(
            'SELECT m' . self::FROM . ' WHERE m.account = :account' . $expectedTail,
            $qb->getDQL(),
        );
        $this->assertSame($expectedMaxResults, $qb->getMaxResults());
    }

    public static function accountProvider(): \Iterator
    {
        yield 'account only' => [[1], '', null];
        yield 'with event type' => [
            [1, 'type'],
            ' AND m.eventHistoryType = :eventHistoryType',
            null,
        ];
        yield 'sorted and limited' => [
            [1, null, 'eventDatetime', 'DESC', 10],
            ' ORDER BY m.eventDatetime DESC',
            10,
        ];
    }

    /**
     * Every entity filter is an orWhere, so the list returns history for any of the entities
     * named rather than only rows matching all of them.
     */
    public function testApplyListFilters(): void
    {
        $qb = $this->createRealQb();

        $query = m::mock(QueryInterface::class);
        foreach ([
            'getCase' => 1,
            'getLicence' => 2,
            'getOrganisation' => 3,
            'getTransportManager' => 4,
            'getUser' => 5,
            'getApplication' => 6,
            'getIrhpApplication' => 7,
        ] as $getter => $value) {
            $query->shouldReceive($getter)->andReturn($value);
        }

        $this->sut->applyListFilters($qb, $query);

        $this->assertSame(
            'SELECT m, w0, u, cd, p' . self::FROM
            . ' LEFT JOIN m.eventHistoryType w0 LEFT JOIN m.user u'
            . ' LEFT JOIN u.contactDetails cd LEFT JOIN cd.person p'
            . ' WHERE m.case = :caseId OR m.licence = :licenceId'
            . ' OR m.organisation = :organisationId OR m.transportManager = :transportManagerId'
            . ' OR m.user = :userId OR m.application = :applicationId'
            . ' OR m.irhpApplication = :irhpApplicationId',
            $qb->getDQL(),
        );
        $this->assertSame(1, $qb->getParameter('caseId')->getValue());
        $this->assertSame(7, $qb->getParameter('irhpApplicationId')->getValue());
    }

    public function testApplyListJoins(): void
    {
        $qb = $this->createRealQb();

        $this->sut->applyListJoins($qb);

        $this->assertSame(
            'SELECT m, w0, w1, w2, w3, w4, w5, w6' . self::FROM
            . ' LEFT JOIN m.case w0 LEFT JOIN m.licence w1 LEFT JOIN m.application w2'
            . ' LEFT JOIN m.organisation w3 LEFT JOIN m.transportManager w4'
            . ' LEFT JOIN m.busReg w5 LEFT JOIN m.irhpApplication w6',
            $qb->getDQL(),
        );
    }

    public function testFetchByTask(): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getResult')->with(Query::HYDRATE_ARRAY)->andReturn(['RESULTS']);

        $this->assertSame(['RESULTS'], $this->sut->fetchByTask(1));

        $this->assertSame(
            'SELECT m, eht, u, cd, p' . self::FROM
            . ' LEFT JOIN m.eventHistoryType eht LEFT JOIN m.user u'
            . ' LEFT JOIN u.contactDetails cd LEFT JOIN cd.person p'
            . ' WHERE m.task = :task',
            $qb->getDQL(),
        );
    }

    /**
     * The event types are inlined, and the most recent of them decides the status the licence
     * reverts to. No matching history at all is treated as a return to valid.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('previousStatusProvider')]
    public function testFetchPreviousLicenceStatus(int $eventTypeId, string $expectedStatus): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getSingleScalarResult')->andReturn($eventTypeId);

        $this->assertSame(['status' => $expectedStatus], $this->sut->fetchPreviousLicenceStatus(1));

        $this->assertSame(
            'SELECT eht.id' . self::FROM
            . ' INNER JOIN m.eventHistoryType eht INNER JOIN m.licence l'
            . ' WHERE eht.id IN(7, 31, 75) AND l.id = :licenceId'
            . ' ORDER BY m.eventDatetime DESC',
            $qb->getDQL(),
        );
        $this->assertSame(1, $qb->getMaxResults());
    }

    public static function previousStatusProvider(): \Iterator
    {
        yield 'curtailed' => [7, Licence::LICENCE_STATUS_CURTAILED];
        yield 'suspended' => [31, Licence::LICENCE_STATUS_SUSPENDED];
        yield 'valid' => [75, Licence::LICENCE_STATUS_VALID];
    }

    public function testFetchPreviousLicenceStatusDefaultsToValidWhenThereIsNoHistory(): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getSingleScalarResult')->andThrow(new NoResultException());

        $this->assertSame(
            ['status' => Licence::LICENCE_STATUS_VALID],
            $this->sut->fetchPreviousLicenceStatus(1),
        );
    }

    public function testFetchPreviousLicenceStatusRethrowsOtherExceptions(): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getSingleScalarResult')->andThrow(new \RuntimeException('boom'));

        $this->expectException(\RuntimeException::class);

        $this->sut->fetchPreviousLicenceStatus(1);
    }

    /**
     * Two consecutive versions are diffed, dropping the audit bookkeeping columns and any field
     * whose value did not change.
     */
    public function testFetchEventHistoryDetails(): void
    {
        $this->expectHistoryQuery('table', 1, 2, [
            ['foo' => 'bar2', 'cake' => 'baz2', 'same' => 'value', 'version' => 2],
            ['foo' => 'bar1', 'cake' => 'baz1', 'same' => 'value', 'version' => 1],
        ]);

        $this->assertSame(
            [
                ['newValue' => 'bar2', 'oldValue' => 'bar1', 'name' => 'foo'],
                ['newValue' => 'baz2', 'oldValue' => 'baz1', 'name' => 'cake'],
            ],
            $this->sut->fetchEventHistoryDetails(1, 2, 'table'),
        );
    }

    /**
     * A newly set deleted_date means the row was deleted, so every old value is reported as
     * having been cleared rather than diffed field by field.
     */
    public function testFetchEventHistoryDetailsWithDeletedDate(): void
    {
        $this->expectHistoryQuery('table', 1, 2, [
            ['foo' => 'bar2', 'cake' => 'baz2', 'same' => 'value', 'deleted_date' => '2026-01-28', 'version' => 2],
            ['foo' => 'bar1', 'cake' => 'baz1', 'same' => 'value', 'deleted_date' => null, 'version' => 1],
        ]);

        $this->assertSame(
            [
                ['name' => 'foo', 'oldValue' => 'bar1', 'newValue' => ''],
                ['name' => 'cake', 'oldValue' => 'baz1', 'newValue' => ''],
                ['name' => 'same', 'oldValue' => 'value', 'newValue' => ''],
                // The previous row's deleted_date was null, and it is reported as-is.
                ['name' => 'deleted_date', 'oldValue' => null, 'newValue' => ''],
            ],
            $this->sut->fetchEventHistoryDetails(1, 2, 'table'),
        );
    }

    private function expectHistoryQuery(string $table, int $id, int $version, array $rows): void
    {
        $statement = m::mock();
        $statement->expects('fetchAllAssociative')->andReturn($rows);

        $query = m::mock();
        $query->expects('setHistoryTable')->with($table)->andReturnSelf();
        $query->expects('execute')
            ->with(['id' => $id, 'version' => [$version, $version - 1]])
            ->andReturn($statement);

        $this->dbQueryService->shouldReceive('get')
            ->with('EventHistory\GetEventHistoryDetails')
            ->andReturn($query);
    }
}
