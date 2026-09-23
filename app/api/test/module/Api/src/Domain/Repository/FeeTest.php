<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Repository\Fee as Repo;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Entity\Bus\BusReg as BusRegEntity;
use Dvsa\Olcs\Api\Entity\Fee\Fee as Entity;
use Dvsa\Olcs\Api\Entity\Fee\FeeType as FeeTypeEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\System\RefData as RefDataEntity;
use Dvsa\Olcs\Transfer\Query\Fee\FeeList;
use Dvsa\OlcsTest\Support\TestQueryBuilder;
use Mockery as m;

#[\PHPUnit\Framework\Attributes\CoversClass(Repo::class)]
final class FeeTest extends RepositoryTestCase
{
    private const string FROM = ' FROM ' . Entity::class . ' f';

    /** withRefdata() reaches only feeStatus; the other refdata columns hang off the joins. */
    private const string FEE_STATUS_JOIN = ' LEFT JOIN f.feeStatus w0';

    private const string TRANSACTION_SELECT = 'f, w0, ft, t, w1';

    private const string TRANSACTION_JOINS = self::FEE_STATUS_JOIN
        . ' LEFT JOIN f.feeTransactions ft LEFT JOIN ft.transaction t LEFT JOIN t.status w1';

    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(Repo::class, true);
    }

    /**
     * getQueryByApplicationFeeTypeFeeType() calls the shared query-builder helper without
     * modifyQuery() first, so its refdata join and ORDER BY are applied to whichever builder the
     * helper is still holding — never to its own. Nothing here is what the repository intends;
     * this pins the behaviour so the eventual fix has a failing test to flip.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('interimFeeProvider')]
    public function testFetchInterimFeesByApplicationId(bool $outstanding, bool $paid, string $expectedStatus): void
    {
        // Stand in for the previous repository call that left the shared helper warm.
        $unrelated = $this->newRealQb();
        $unrelated->select('f')->from(Entity::class, 'f');
        $this->queryBuilder->modifyQuery($unrelated);

        $refData = $this->expectRefdataReference();

        $qb = $this->createRealQb()->willReturn(['RESULTS']);

        $this->assertSame(['RESULTS'], $this->sut->fetchInterimFeesByApplicationId(33, $outstanding, $paid));

        $this->assertSame(
            'SELECT f' . self::FROM . ' INNER JOIN f.feeType ft'
            . ' WHERE ft.feeType = :feeTypeFeeType AND f.application = :applicationId'
            . $expectedStatus,
            $qb->getDQL(),
        );
        $this->assertSame($refData, $qb->getParameter('feeTypeFeeType')->getValue());
        $this->assertSame(33, $qb->getParameter('applicationId')->getValue());

        // The refdata join and the ordering landed on the unrelated builder instead.
        $this->assertSame(
            'SELECT f, w0' . self::FROM . self::FEE_STATUS_JOIN . ' ORDER BY f.invoicedDate ASC',
            $unrelated->getDQL(),
        );
    }

    public static function interimFeeProvider(): \Iterator
    {
        yield 'any status' => [false, false, ''];
        yield 'outstanding only' => [true, false, ' AND f.feeStatus = :feeStatus'];
        yield 'paid only' => [false, true, ' AND f.feeStatus = :feeStatus'];
        yield 'outstanding or paid' => [true, true, ' AND f.feeStatus IN(:feeStatus)'];
    }

    /**
     * With a cold helper — the first query of the request — the same call throws outright.
     */
    public function testFetchInterimFeesByApplicationIdWithAColdQueryBuilder(): void
    {
        $this->expectRefdataReference();
        $this->createRealQb();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Doctrine Query Builder is not set');

        $this->sut->fetchInterimFeesByApplicationId(33);
    }

    public function testFetchInterimRefunds(): void
    {
        $qb = $this->createRealQb()->willReturn(['RESULTS']);

        $this->assertSame(
            ['RESULTS'],
            $this->sut->fetchInterimRefunds('2015-01-01', '2015-12-31', 'invoicedDate', 'ASC', ['B']),
        );

        $this->assertSame(
            'SELECT f, w0, ftr, l, o' . self::FROM . self::FEE_STATUS_JOIN
            . ' LEFT JOIN f.feeTransactions ftr LEFT JOIN f.licence l LEFT JOIN l.organisation o'
            . ' LEFT JOIN f.application a INNER JOIN f.feeType fty'
            . ' WHERE f.feeStatus IN(:feeStatus)'
            // A refund is only interim once the application it belongs to has been resolved.
            . ' AND COALESCE(a.withdrawnDate, a.refusedDate, a.grantedDate) IS NOT NULL'
            . ' AND fty.feeType = :feeType'
            . ' AND f.invoicedDate >= :after AND f.invoicedDate <= :before'
            . ' AND l.trafficArea IN(:trafficArea)'
            . ' ORDER BY f.invoicedDate ASC',
            $qb->getDQL(),
        );
        $this->assertSame(
            [Entity::STATUS_REFUNDED, Entity::STATUS_REFUND_FAILED, Entity::STATUS_REFUND_PENDING],
            $qb->getParameter('feeStatus')->getValue(),
        );
        $this->assertSame(FeeTypeEntity::FEE_TYPE_GRANTINT, $qb->getParameter('feeType')->getValue());
        $this->assertSame('2015-01-01', $qb->getParameter('after')->getValue());
        $this->assertSame('2015-12-31', $qb->getParameter('before')->getValue());
        $this->assertSame(['B'], $qb->getParameter('trafficArea')->getValue());
    }

    public function testFetchInterimRefundsWithoutADateRangeOrTrafficArea(): void
    {
        $qb = $this->createRealQb()->willReturn([]);

        $this->sut->fetchInterimRefunds(null, null, 'invoicedDate', 'ASC');

        $this->assertStringNotContainsString(':after', $qb->getDQL());
        $this->assertStringNotContainsString(':before', $qb->getDQL());
        $this->assertStringNotContainsString(':trafficArea', $qb->getDQL());
    }

    /**
     * An outstanding fee counts only where it hangs off a live licence or a live application, so
     * this one carries three alternations plus the excluded-application-status guard.
     */
    public function testFetchOutstandingFeesByOrganisationId(): void
    {
        $refData = $this->expectRefdataReference();

        $qb = $this->createRealQb()->willReturn(['RESULTS']);

        $this->assertSame(['RESULTS'], $this->sut->fetchOutstandingFeesByOrganisationId(1));

        $this->assertSame(
            'SELECT ' . self::TRANSACTION_SELECT . self::FROM . self::TRANSACTION_JOINS
            . ' LEFT JOIN f.application a LEFT JOIN f.licence l LEFT JOIN a.licence al'
            . ' LEFT JOIN f.application app'
            . ' WHERE f.feeStatus = :feeStatus'
            . ' AND (l.organisation = :organisationId OR al.organisation = :organisationId)'
            . ' AND (a.status IN(:appStatus) OR l.status IN(:licStatus))'
            . ' AND (f.licence IS NOT NULL OR f.application IS NOT NULL)'
            . ' AND (f.application IS NULL OR app.status NOT IN(:excludedApplicationStatuses))'
            . ' ORDER BY f.invoicedDate ASC',
            $qb->getDQL(),
        );
        $this->assertSame($refData, $qb->getParameter('feeStatus')->getValue());
        $this->assertSame(1, $qb->getParameter('organisationId')->getValue());
        $this->assertSame(
            [
                ApplicationEntity::APPLICATION_STATUS_NOT_SUBMITTED,
                ApplicationEntity::APPLICATION_STATUS_CANCELLED,
                ApplicationEntity::APPLICATION_STATUS_WITHDRAWN,
            ],
            $qb->getParameter('excludedApplicationStatuses')->getValue(),
        );
    }

    public function testFetchOutstandingFeesByOrganisationIdHidingCeasedAndContinuations(): void
    {
        $this->expectRefdataReference();

        $qb = $this->createRealQb()->willReturn([]);

        $this->sut->fetchOutstandingFeesByOrganisationId(1, true, true);

        $this->assertStringContainsString(' INNER JOIN f.feeType ftype', $qb->getDQL());
        $this->assertStringContainsString(' AND l.status NOT IN(:ceasedStatuses)', $qb->getDQL());
        $this->assertStringContainsString(' AND ftype.feeType <> :feeType', $qb->getDQL());
        $this->assertSame(
            [
                LicenceEntity::LICENCE_STATUS_CONTINUATION_NOT_SOUGHT,
                LicenceEntity::LICENCE_STATUS_REVOKED,
                LicenceEntity::LICENCE_STATUS_SURRENDERED,
                LicenceEntity::LICENCE_STATUS_TERMINATED,
            ],
            $qb->getParameter('ceasedStatuses')->getValue(),
        );
        $this->assertSame(RefDataEntity::FEE_TYPE_CONT, $qb->getParameter('feeType')->getValue());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('plainQueryProvider')]
    public function testPlainQueries(
        string $method,
        array $args,
        string $expectedDql,
        array $expectedParameters,
    ): void {
        $refData = $this->expectRefdataReference();

        $qb = $this->createRealQb()->willReturn(['RESULTS']);

        $this->assertSame(['RESULTS'], $this->sut->{$method}(...$args));

        $this->assertSame('SELECT f' . self::FROM . $expectedDql, $qb->getDQL());

        foreach ($expectedParameters as $name => $expected) {
            $this->assertSame(
                $expected === '<refdata>' ? $refData : $expected,
                $qb->getParameter($name)->getValue(),
                sprintf('parameter %s', $name),
            );
        }
    }

    public static function plainQueryProvider(): \Iterator
    {
        yield 'outstanding fees for an application' => [
            'fetchOutstandingFeesByApplicationId',
            [1],
            ' WHERE f.feeStatus = :feeStatus AND f.application = :application',
            ['feeStatus' => '<refdata>', 'application' => 1],
        ];
        yield 'outstanding grant fees for an application' => [
            'fetchOutstandingGrantFeesByApplicationId',
            [1],
            ' INNER JOIN f.feeType ft'
            . ' WHERE f.feeStatus = :feeStatus AND f.application = :application'
            . ' AND ft.feeType = :feeType',
            ['application' => 1, 'feeType' => RefDataEntity::FEE_TYPE_GRANT],
        ];
        yield 'outstanding continuation fees for a licence' => [
            'fetchOutstandingContinuationFeesByLicenceId',
            [1, '2015-01-01'],
            ' INNER JOIN f.feeType ft'
            . ' WHERE f.licence = :licence AND ft.feeType = :feeType'
            . ' AND f.feeStatus = :feeStatus AND f.invoicedDate >= :after',
            ['licence' => 1, 'feeType' => RefDataEntity::FEE_TYPE_CONT, 'after' => '2015-01-01'],
        ];
        // $hasAnyStatus drops the outstanding filter; no $after drops the date bound.
        yield 'continuation fees for a licence in any status' => [
            'fetchOutstandingContinuationFeesByLicenceId',
            [1, null, true],
            ' INNER JOIN f.feeType ft WHERE f.licence = :licence AND ft.feeType = :feeType',
            ['licence' => 1],
        ];
        yield 'a fee by type and application' => [
            'fetchFeeByTypeAndApplicationId',
            ['ft_app', 1],
            ' INNER JOIN f.feeType ft WHERE f.application = :application AND ft.feeType = :feeType',
            ['application' => 1, 'feeType' => 'ft_app'],
        ];
    }

    /**
     * Both by-id fetches eager-load the same associations and order oldest invoice first; only
     * the outstanding filter differs.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('byIdsProvider')]
    public function testFetchByIds(string $method, string $expectedExtra): void
    {
        $this->expectRefdataReference();

        $qb = $this->createRealQb()->willReturn(['RESULTS']);

        $this->assertSame(['RESULTS'], $this->sut->{$method}([1, 2]));

        $this->assertSame(
            'SELECT f, w0, w1, w2, ft, t, w3' . self::FROM . self::FEE_STATUS_JOIN
            . ' LEFT JOIN f.licence w1 LEFT JOIN f.application w2'
            . ' LEFT JOIN f.feeTransactions ft LEFT JOIN ft.transaction t LEFT JOIN t.status w3'
            . ' WHERE' . $expectedExtra . ' f.id IN(:feeIds)'
            . ' ORDER BY f.invoicedDate ASC',
            $qb->getDQL(),
        );
        $this->assertSame([1, 2], $qb->getParameter('feeIds')->getValue());
    }

    public static function byIdsProvider(): \Iterator
    {
        yield 'outstanding only' => ['fetchOutstandingFeesByIds', ' f.feeStatus = :feeStatus AND'];
        yield 'any status' => ['fetchFeesByIds', ''];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('irfoProvider')]
    public function testFetchIrfoFees(string $method, array $args, string $expectedWhere): void
    {
        $this->expectRefdataReference();

        $qb = $this->createRealQb()->willReturn(['RESULTS']);

        $this->assertSame(['RESULTS'], $this->sut->{$method}(...$args));

        $this->assertSame(
            'SELECT f, w0' . self::FROM . self::FEE_STATUS_JOIN
            . ' WHERE ' . $expectedWhere
            . ' ORDER BY f.invoicedDate ASC',
            $qb->getDQL(),
        );
    }

    public static function irfoProvider(): \Iterator
    {
        yield 'goods permit' => [
            'fetchFeesByIrfoGvPermitId',
            [1],
            'f.irfoGvPermit = :irfoGvPermitId',
        ];
        yield 'psv authorisation' => [
            'fetchFeesByIrfoPsvAuthId',
            [1],
            'f.irfoPsvAuth = :irfoPsvAuthId',
        ];
        yield 'psv authorisation, outstanding only' => [
            'fetchFeesByIrfoPsvAuthId',
            [1, true],
            'f.irfoPsvAuth = :irfoPsvAuthId AND f.feeStatus = :feeStatus',
        ];
    }

    /**
     * The feeType join is added before the helper runs, so it precedes the refdata join here
     * rather than following it.
     */
    public function testFetchFeesByPsvAuthIdAndType(): void
    {
        $refData = $this->expectRefdataReference();

        $qb = $this->createRealQb()->willReturn(['RESULTS']);

        $this->assertSame(
            ['RESULTS'],
            $this->sut->fetchFeesByPsvAuthIdAndType(1, RefDataEntity::FEE_TYPE_IRFOPSVAPP),
        );

        $this->assertSame(
            'SELECT f, w0' . self::FROM . ' INNER JOIN f.feeType ft' . self::FEE_STATUS_JOIN
            . ' WHERE ft.feeType = :feeTypeFeeType AND f.irfoPsvAuth = :irfoPsvAuthId'
            . ' ORDER BY f.invoicedDate DESC',
            $qb->getDQL(),
        );
        $this->assertSame($refData, $qb->getParameter('feeTypeFeeType')->getValue());
        $this->assertSame(1, $qb->getParameter('irfoPsvAuthId')->getValue());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('firstOrNullProvider')]
    public function testFetchApplicationFeeByPsvAuthId(array $results, mixed $expected): void
    {
        $this->sut->expects('fetchFeesByPsvAuthIdAndType')
            ->with(1, RefDataEntity::FEE_TYPE_IRFOPSVAPP)
            ->andReturn($results);

        $this->assertSame($expected, $this->sut->fetchApplicationFeeByPsvAuthId(1));
    }

    public static function firstOrNullProvider(): \Iterator
    {
        yield 'a fee' => [['first', 'second'], 'first'];
        yield 'no fees' => [[], null];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('latestFeeProvider')]
    public function testFetchLatestFeeByTypeStatusesAndApplicationId(array $results, mixed $expected): void
    {
        $this->expectRefdataReference();

        $qb = $this->createRealQb()->willReturn($results);

        $this->assertSame(
            $expected,
            $this->sut->fetchLatestFeeByTypeStatusesAndApplicationId('ft_app', ['lfs_ot'], 69),
        );

        $this->assertSame(
            'SELECT f, w0' . self::FROM . self::FEE_STATUS_JOIN
            . ' WHERE f.application = :application AND f.feeStatus IN(:feeStatuses)'
            . ' AND f.feeType = :feeType'
            . ' ORDER BY f.invoicedDate DESC',
            $qb->getDQL(),
        );
        $this->assertSame(69, $qb->getParameter('application')->getValue());
        $this->assertSame(['lfs_ot'], $qb->getParameter('feeStatuses')->getValue());
        $this->assertSame('ft_app', $qb->getParameter('feeType')->getValue());
        $this->assertSame(1, $qb->getMaxResults());
    }

    public static function latestFeeProvider(): \Iterator
    {
        yield 'a fee' => [['latest', 'older'], 'latest'];
        yield 'no fees' => [[], null];
    }

    /**
     * "Latest paid" is decided by the transaction, not the fee, so the ordering is on the
     * transaction's completed date with its id as the tie-break.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('latestPaidProvider')]
    public function testFetchLatestPaid(
        string $method,
        string $expectedDql,
        array $results,
        mixed $expected,
    ): void {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getResult')->with(Query::HYDRATE_OBJECT)->andReturn($results);

        $this->assertSame($expected, $this->sut->{$method}(1));

        $this->assertSame('SELECT f' . self::FROM . $expectedDql, $qb->getDQL());
        $this->assertSame(1, $qb->getMaxResults());
    }

    public static function latestPaidProvider(): \Iterator
    {
        $byApplication = ' INNER JOIN f.feeTransactions ft INNER JOIN ft.transaction t'
            . ' WHERE f.application = :application'
            . ' ORDER BY t.completedDate DESC, t.id DESC';

        $continuation = ' INNER JOIN f.feeTransactions ft INNER JOIN f.feeType ftp'
            . ' INNER JOIN ft.transaction t'
            . ' WHERE f.licence = :licence AND f.feeStatus = :feeStatus AND ftp.feeType = :feeType'
            . ' ORDER BY t.completedDate DESC, t.id DESC';

        yield 'by application' => ['fetchLatestPaidFeeByApplicationId', $byApplication, ['fee'], 'fee'];
        // The two no-result cases disagree: one returns [], the other null.
        yield 'by application, none' => ['fetchLatestPaidFeeByApplicationId', $byApplication, [], []];
        yield 'continuation' => ['fetchLatestPaidContinuationFee', $continuation, ['fee'], 'fee'];
        yield 'continuation, none' => ['fetchLatestPaidContinuationFee', $continuation, [], null];
    }

    /**
     * Every list filter at once. It would return nothing in practice, but it is the only way to
     * see the whole WHERE clause in one place.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('statusProvider')]
    public function testFetchList(?string $status, string $expectedStatusFilter, mixed $expectedStatuses): void
    {
        ['f' => $qb, 'br' => $busRegQb] = $this->createRealQbs([
            Entity::class => 'f',
            BusRegEntity::class => 'br',
        ]);

        $busRegQb->stubbedQuery()->expects('getArrayResult')->andReturn([14, 15, 16]);

        $qb->stubbedQuery()->expects('setHydrationMode')->with(Query::HYDRATE_ARRAY);
        $paginator = m::mock();
        $paginator->expects('getIterator')->withNoArgs()->andReturn('RESULTS');
        $this->sut->expects('getPaginator')->with($qb->stubbedQuery())->andReturn($paginator);

        $query = FeeList::create([
            'application' => 11,
            'licence' => 12,
            'task' => 13,
            'busReg' => 14,
            'irfoGvPermit' => 15,
            'organisation' => 16,
            'irhpApplication' => 17,
            'page' => 1,
            'limit' => 10,
            'sort' => 'id',
            'order' => 'ASC',
            'isMiscellaneous' => 'Y',
            'ids' => [1, 2, 3],
            'status' => $status,
        ]);

        $this->assertSame('RESULTS', $this->sut->fetchList($query));

        $this->assertSame(
            'SELECT f, w0, u, cd, p' . self::FROM . self::FEE_STATUS_JOIN
            . ' LEFT JOIN f.irfoGvPermit igp LEFT JOIN f.irfoPsvAuth ipa'
            . ' INNER JOIN f.feeType ft'
            . ' LEFT JOIN f.createdBy u LEFT JOIN u.contactDetails cd LEFT JOIN cd.person p'
            . ' WHERE f.licence = :licenceId AND f.application = :applicationId'
            . ' AND f.id IN(:byIds)'
            . ' AND (f.irfoGvPermit IS NOT NULL OR f.irfoPsvAuth IS NOT NULL)'
            . ' AND (igp.organisation = :organisationId OR ipa.organisation = :organisationId)'
            . ' AND f.busReg IN(:busRegIds)'
            . ' AND f.task = :taskId AND f.irfoGvPermit = :irfoGvPermitId'
            . ' AND f.irhpApplication = :irhpApplicationId'
            . ' AND ft.isMiscellaneous = :isMiscellaneous'
            . $expectedStatusFilter
            . ' ORDER BY f.id ASC',
            $qb->getDQL(),
        );
        $this->assertSame(12, $qb->getParameter('licenceId')->getValue());
        $this->assertSame(11, $qb->getParameter('applicationId')->getValue());
        $this->assertSame([1, 2, 3], $qb->getParameter('byIds')->getValue());
        $this->assertSame([14, 15, 16], $qb->getParameter('busRegIds')->getValue());
        $this->assertSame(16, $qb->getParameter('organisationId')->getValue());
        $this->assertSame(13, $qb->getParameter('taskId')->getValue());
        $this->assertSame(15, $qb->getParameter('irfoGvPermitId')->getValue());
        $this->assertSame(17, $qb->getParameter('irhpApplicationId')->getValue());
        $this->assertSame('Y', $qb->getParameter('isMiscellaneous')->getValue());

        if ($expectedStatuses !== null) {
            $this->assertSame($expectedStatuses, $qb->getParameter('feeStatus')->getValue());
        }

        // The bus registration filter widens to every registration sharing the route number.
        $this->assertSame(
            'SELECT br2.id FROM ' . BusRegEntity::class . ' br'
            . ' INNER JOIN ' . BusRegEntity::class . ' br2'
            . ' WHERE br.routeNo = br2.routeNo AND br.id = :id',
            $busRegQb->getDQL(),
        );
        $this->assertSame(14, $busRegQb->getParameter('id')->getValue());
    }

    public static function statusProvider(): \Iterator
    {
        yield 'current' => [
            'current',
            ' AND f.feeStatus IN(:feeStatus)',
            [Entity::STATUS_OUTSTANDING],
        ];
        yield 'historical' => [
            'historical',
            ' AND f.feeStatus IN(:feeStatus)',
            [
                Entity::STATUS_PAID,
                Entity::STATUS_CANCELLED,
                Entity::STATUS_REFUNDED,
                Entity::STATUS_REFUND_FAILED,
                Entity::STATUS_REFUND_PENDING,
            ],
        ];
        yield 'all' => ['all', '', null];
        yield 'unset' => [null, '', null];
    }

    /**
     * Listing a licence's fees drops the ones belonging to applications the operator never
     * submitted; fees with no application at all stay. Asking for a specific application instead
     * turns the guard off, so a not-submitted application can still list its own fees.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('excludedStatusProvider')]
    public function testFetchListExcludesUnsubmittedApplicationFees(?int $application, bool $expectedGuard): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('setHydrationMode')->with(Query::HYDRATE_ARRAY);

        $paginator = m::mock();
        $paginator->expects('getIterator')->withNoArgs()->andReturn('RESULTS');
        $this->sut->expects('getPaginator')->with($qb->stubbedQuery())->andReturn($paginator);

        $this->sut->fetchList(FeeList::create(['licence' => 12, 'application' => $application]));

        $guard = ' LEFT JOIN f.application app';

        if ($expectedGuard) {
            $this->assertStringContainsString($guard, $qb->getDQL());
            $this->assertStringContainsString(
                ' AND (f.application IS NULL OR app.status NOT IN(:excludedApplicationStatuses))',
                $qb->getDQL(),
            );
            $this->assertSame(
                [
                    ApplicationEntity::APPLICATION_STATUS_NOT_SUBMITTED,
                    ApplicationEntity::APPLICATION_STATUS_CANCELLED,
                    ApplicationEntity::APPLICATION_STATUS_WITHDRAWN,
                ],
                $qb->getParameter('excludedApplicationStatuses')->getValue(),
            );

            return;
        }

        $this->assertStringNotContainsString($guard, $qb->getDQL());
        $this->assertNull($qb->getParameter('excludedApplicationStatuses'));
    }

    public static function excludedStatusProvider(): \Iterator
    {
        yield 'a licence alone' => [null, true];
        yield 'a licence and an application' => [11, false];
    }

    /**
     * getRefdataReference() resolves through the EntityManager; the repositories only ever pass
     * the reference straight into a parameter, so one shared instance is enough.
     */
    private function expectRefdataReference(): RefDataEntity
    {
        $refData = m::mock(RefDataEntity::class);

        $this->em->shouldReceive('getReference')
            ->with(RefDataEntity::class, m::any())
            ->andReturn($refData);

        return $refData;
    }
}
