<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\DBAL\LockMode;
use Doctrine\DBAL\Result;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\Query\Bus\ByLicenceRoute;
use Dvsa\Olcs\Api\Domain\Query\Bus\PreviousVariationByRouteNo;
use Dvsa\Olcs\Api\Domain\Repository\Bus as Repo;
use Dvsa\Olcs\Api\Domain\Repository\Query\Bus\Expire as ExpireQuery;
use Dvsa\Olcs\Api\Entity\Bus\BusReg as Entity;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Mockery as m;

final class BusTest extends RepositoryTestCase
{
    private const string FROM = ' FROM ' . Entity::class . ' m';

    private const string REFDATA_SELECT = 'm, w0, w1, w2, w3, w4';

    private const string REFDATA_JOINS = ' LEFT JOIN m.status w0 LEFT JOIN m.revertStatus w1'
        . ' LEFT JOIN m.subsidised w2 LEFT JOIN m.withdrawnReason w3'
        . ' LEFT JOIN m.variationReasons w4';

    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(Repo::class, true);
    }

    public function testFetchUsingId(): void
    {
        $result = m::mock(Entity::class);

        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getResult')->with(Query::HYDRATE_OBJECT)->andReturn([$result]);
        $this->em->expects('lock')->with($result, LockMode::OPTIMISTIC, 1);

        $query = m::mock(QueryInterface::class);
        $query->shouldReceive('getId')->andReturn(15);

        $this->assertSame($result, $this->sut->fetchUsingId($query, Query::HYDRATE_OBJECT, 1));

        $this->assertSame(
            'SELECT ' . self::REFDATA_SELECT . ', w5, w6, w7, w8, w9, w10'
            . self::FROM . self::REFDATA_JOINS
            // withRefdata() already joined subsidised; with('subsidised') joins it again as w9.
            . ' LEFT JOIN m.busNoticePeriod w5 LEFT JOIN m.busServiceTypes w6'
            . ' LEFT JOIN m.trafficAreas w7 LEFT JOIN m.localAuthoritys w8'
            . ' LEFT JOIN m.subsidised w9 LEFT JOIN m.otherServices w10'
            . ' WHERE m.id = :byId',
            $qb->getDQL(),
        );
        $this->assertSame(15, $qb->getParameter('byId')->getValue());
    }

    public function testFetchUsingIdNotFound(): void
    {
        $this->createRealQb()->stubbedQuery()->expects('getResult')->andReturn([]);

        $query = m::mock(QueryInterface::class);
        $query->shouldReceive('getId')->andReturn(15);

        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('Resource not found');

        $this->sut->fetchUsingId($query);
    }

    /**
     * An array-hydrated read is never locked: there is no entity to attach the version to.
     */
    public function testFetchUsingIdDoesNotLockAnArrayResult(): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getResult')->with(Query::HYDRATE_ARRAY)->andReturn([['id' => 15]]);
        $this->em->shouldReceive('lock')->never();

        $query = m::mock(QueryInterface::class);
        $query->shouldReceive('getId')->andReturn(15);

        $this->assertSame(['id' => 15], $this->sut->fetchUsingId($query, Query::HYDRATE_ARRAY, 1));
    }

    /**
     * The inbox rows are restricted in the join condition rather than the WHERE: a LEFT JOIN
     * filtered in the WHERE would drop registrations that have no inbox row at all.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('txcInboxProvider')]
    public function testFetchWithTxcInboxList(
        string $method,
        mixed $owner,
        string $expectedJoinCondition,
        ?string $expectedParameter,
    ): void {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getResult')->with(Query::HYDRATE_OBJECT)->andReturn(['RESULT']);

        $query = m::mock(QueryInterface::class);
        $query->shouldReceive('getId')->andReturn(15);

        $this->assertSame('RESULT', $this->sut->{$method}($query, $owner));

        $this->assertSame(
            'SELECT ' . self::REFDATA_SELECT . ', t' . self::FROM . self::REFDATA_JOINS
            . ' LEFT JOIN m.txcInboxs t WITH ' . $expectedJoinCondition
            . ' WHERE m.id = :byId',
            $qb->getDQL(),
        );

        if ($expectedParameter !== null) {
            $this->assertSame($owner, $qb->getParameter($expectedParameter)->getValue());
        }
    }

    public static function txcInboxProvider(): \Iterator
    {
        // An operator only sees its own rows, which are the ones with no local authority.
        yield 'an organisation' => [
            'fetchWithTxcInboxListForOrganisation',
            7,
            't.localAuthority IS NULL AND t.organisation = :organisation',
            'organisation',
        ];
        yield 'a local authority' => [
            'fetchWithTxcInboxListForLocalAuthority',
            3,
            't.localAuthority = :localAuthority',
            'localAuthority',
        ];
        // No local authority falls back to the same unowned rows an operator sees.
        yield 'no local authority' => [
            'fetchWithTxcInboxListForLocalAuthority',
            null,
            't.localAuthority IS NULL',
            null,
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('txcInboxMethodProvider')]
    public function testFetchWithTxcInboxListNotFound(string $method): void
    {
        $this->createRealQb()->stubbedQuery()->expects('getResult')->andReturn([]);

        $query = m::mock(QueryInterface::class);
        $query->shouldReceive('getId')->andReturn(15);

        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('Resource not found');

        $this->sut->{$method}($query, 7);
    }

    public static function txcInboxMethodProvider(): \Iterator
    {
        yield 'an organisation' => ['fetchWithTxcInboxListForOrganisation'];
        yield 'a local authority' => ['fetchWithTxcInboxListForLocalAuthority'];
    }

    /**
     * The filters are keyed off what the query class can answer, so each query type produces a
     * different WHERE from the same method.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('listFilterProvider')]
    public function testApplyListFilters(
        string $queryClass,
        array $stubs,
        string $expectedWhere,
        array $expectedParameters,
    ): void {
        $qb = $this->createRealQb();

        $query = m::mock($queryClass);

        foreach ($stubs as $method => $value) {
            $query->shouldReceive($method)->andReturn($value);
        }

        $this->sut->applyListFilters($qb, $query);

        $this->assertSame('SELECT m' . self::FROM . ' WHERE ' . $expectedWhere, $qb->getDQL());

        foreach ($expectedParameters as $name => $expected) {
            $this->assertSame($expected, $qb->getParameter($name)->getValue(), sprintf('parameter %s', $name));
        }
    }

    public static function listFilterProvider(): \Iterator
    {
        yield 'the previous variation on a route' => [
            PreviousVariationByRouteNo::class,
            ['getRouteNo' => 22, 'getVariationNo' => 11, 'getLicenceId' => 33],
            'm.routeNo = :byRouteNo AND m.variationNo < :byVariationNo AND m.licence = :byLicence',
            ['byRouteNo' => 22, 'byVariationNo' => 11, 'byLicence' => 33],
        ];
        yield 'a route on a licence, in given statuses' => [
            ByLicenceRoute::class,
            ['getRouteNo' => 22, 'getLicenceId' => 11, 'getBusRegStatus' => ['status', 'status2']],
            'm.routeNo = :byRouteNo AND m.licence = :byLicence AND m.status IN(:byStatus)',
            ['byRouteNo' => 22, 'byLicence' => 11, 'byStatus' => ['status', 'status2']],
        ];
        // An empty status list means every status, not none.
        yield 'a route on a licence, any status' => [
            ByLicenceRoute::class,
            ['getRouteNo' => 22, 'getLicenceId' => 11, 'getBusRegStatus' => []],
            'm.routeNo = :byRouteNo AND m.licence = :byLicence',
            ['byRouteNo' => 22, 'byLicence' => 11],
        ];
        // A query that declares neither only filters on the route number.
        yield 'a query with no variation or licence' => [
            QueryInterface::class,
            ['getRouteNo' => 22],
            'm.routeNo = :byRouteNo',
            ['byRouteNo' => 22],
        ];
    }

    public function testApplyListJoins(): void
    {
        $qb = $this->createRealQb();

        $this->sut->applyListJoins($qb);

        $this->assertSame(
            'SELECT m, w0, w1' . self::FROM
            . ' LEFT JOIN m.busNoticePeriod w0 LEFT JOIN m.status w1',
            $qb->getDQL(),
        );
    }

    public function testExpireRegistrations(): void
    {
        $result = m::mock(Result::class);
        $result->expects('rowCount')->withNoArgs()->andReturn(555);

        $this->expectQueryWithData(ExpireQuery::class, [], [], $result);

        $this->assertSame(555, $this->sut->expireRegistrations());
    }
}
