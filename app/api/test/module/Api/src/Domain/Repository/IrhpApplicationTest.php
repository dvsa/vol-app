<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result as DbalResult;
use Dvsa\Olcs\Api\Domain\Repository\IrhpApplication as Repo;
use Dvsa\Olcs\Api\Domain\Repository\Query\Permits\ExpireIrhpApplications as ExpireIrhpApplicationsQuery;
use Dvsa\Olcs\Api\Entity\IrhpInterface;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication as Entity;
use Dvsa\Olcs\Api\Entity\Permits\IrhpCandidatePermit as IrhpCandidatePermitEntity;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermit;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitType;
use Dvsa\OlcsTest\Support\TestQueryBuilder;
use Mockery as m;

final class IrhpApplicationTest extends RepositoryTestCase
{
    private const string FROM = ' FROM ' . Entity::class . ' ia';

    /**
     * Every scoring query walks the same chain from a candidate permit up to its application; only
     * the order of the joins and the extra ones differ.
     */
    private const string CANDIDATE_PERMIT_FROM = ' FROM ' . IrhpCandidatePermitEntity::class . ' icp'
        . ' INNER JOIN icp.irhpPermitApplication ipa INNER JOIN ipa.irhpPermitWindow ipw'
        . ' INNER JOIN ipa.irhpApplication epa';

    private const string EMISSIONS_SELECT = 'icp.id, IDENTITY(icp.requestedEmissionsCategory) as emissions_category';

    /** The scope window is always identified by its stock, never by the window itself. */
    private const string IN_STOCK = ' WHERE IDENTITY(ipw.irhpPermitStock) = ?1';

    private const array SCORING_LICENCE_PARAMETERS = [
        'status' => IrhpInterface::STATUS_UNDER_CONSIDERATION,
        'licenceType1' => LicenceEntity::LICENCE_TYPE_RESTRICTED,
        'licenceType2' => LicenceEntity::LICENCE_TYPE_STANDARD_INTERNATIONAL,
        'licenceType3' => LicenceEntity::LICENCE_TYPE_STANDARD_NATIONAL,
        'licenceStatus1' => LicenceEntity::LICENCE_STATUS_VALID,
        'licenceStatus2' => LicenceEntity::LICENCE_STATUS_SUSPENDED,
        'licenceStatus3' => LicenceEntity::LICENCE_STATUS_CURTAILED,
    ];

    /** Shared by both scope queries and by applyScope(). */
    private const string IN_WINDOWS_OF_STOCK = 'where e.id in ('
        . '    select irhp_application_id from irhp_permit_application where irhp_permit_window_id in ('
        . '        select id from irhp_permit_window where irhp_permit_stock_id = :stockId'
        . '    )'
        . ') ';

    private const string LICENCE_IS_LIVE = 'and l.licence_type in (:licenceType1, :licenceType2, :licenceType3) '
        . 'and l.status in (:licenceStatus1, :licenceStatus2, :licenceStatus3)';

    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(Repo::class, true);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('repositoryQueryProvider')]
    public function testRepositoryQueries(
        string $method,
        array $args,
        string $expectedWhere,
        array $expectedParameters,
    ): void {
        $qb = $this->createRealQb()->willReturn(['RESULTS']);

        $this->assertSame(['RESULTS'], $this->sut->{$method}(...$args));

        $this->assertSame('SELECT ia' . self::FROM . $expectedWhere, $qb->getDQL());

        foreach ($expectedParameters as $name => $expected) {
            $this->assertSame($expected, $qb->getParameter($name)->getValue(), sprintf('parameter %s', $name));
        }
    }

    public static function repositoryQueryProvider(): \Iterator
    {
        yield 'by licence' => [
            'fetchByLicence',
            [1],
            // fetchByX() inlines the value rather than binding it.
            ' WHERE ia.licence = 1',
            [],
        ];
        yield 'by window' => [
            'fetchByWindowId',
            [1, ['s1', 's2']],
            ' INNER JOIN ia.irhpPermitApplications ipa INNER JOIN ipa.irhpPermitWindow ipw'
            . ' WHERE ipw.id = :windowId AND ia.status IN(:appStatuses)',
            ['windowId' => 1, 'appStatuses' => ['s1', 's2']],
        ];
        yield 'for the roadworthiness report' => [
            'fetchForRoadworthinessReport',
            ['2020-12-25', '2020-12-31'],
            ' WHERE ia.irhpPermitType IN(:irhpPermitTypes)'
            . ' AND ia.status NOT IN(:excludeStatuses)'
            . ' AND (ia.dateReceived BETWEEN :startDate AND :endDate)',
            [
                'irhpPermitTypes' => IrhpPermitType::CERTIFICATE_TYPES,
                'excludeStatuses' => [
                    IrhpInterface::STATUS_NOT_YET_SUBMITTED,
                    IrhpInterface::STATUS_CANCELLED,
                    IrhpInterface::STATUS_WITHDRAWN,
                ],
                'startDate' => '2020-12-25',
                'endDate' => '2020-12-31',
            ],
        ];
        yield 'valid roadworthiness' => [
            'fetchAllValidRoadworthiness',
            [],
            ' WHERE ia.status = :status AND ia.irhpPermitType IN(:irhpPermitTypes)',
            [
                'status' => IrhpInterface::STATUS_VALID,
                'irhpPermitTypes' => IrhpPermitType::CERTIFICATE_TYPES,
            ],
        ];
        yield 'not yet submitted bilaterals' => [
            'fetchNotYetSubmittedBilateralApplications',
            [],
            ' WHERE ia.status = :status AND ia.irhpPermitType = :irhpPermitType',
            [
                'status' => IrhpInterface::STATUS_NOT_YET_SUBMITTED,
                'irhpPermitType' => IrhpPermitType::IRHP_PERMIT_TYPE_ID_BILATERAL,
            ],
        ];
    }

    public function testFetchAllAwaitingFee(): void
    {
        $applications = [m::mock(Entity::class), m::mock(Entity::class)];

        $qb = $this->expectEntityManagerQb();
        $qb->stubbedQuery()->expects('getResult')->withNoArgs()->andReturn($applications);

        $this->assertSame($applications, $this->sut->fetchAllAwaitingFee());

        $this->assertSame('SELECT ia' . self::FROM . ' WHERE ia.status = :status', $qb->getDQL());
        $this->assertSame(IrhpInterface::STATUS_AWAITING_FEE, $qb->getParameter('status')->getValue());
    }

    /**
     * The scoring queries are built straight off the EntityManager rather than the repository, so
     * they are rooted on the candidate permit and use positional parameters.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('scoringQueryProvider')]
    public function testScoringQueries(
        string $method,
        array $args,
        string $resultMethod,
        string $expectedDql,
        array $expectedParameters,
    ): void {
        $qb = $this->expectEntityManagerQb();
        $qb->stubbedQuery()->expects($resultMethod)->withNoArgs()->andReturn(['RESULTS']);

        $this->assertSame(['RESULTS'], $this->sut->{$method}(...$args));

        $this->assertSame($expectedDql, $qb->getDQL());

        foreach ($expectedParameters as $position => $expected) {
            $this->assertSame($expected, $qb->getParameter($position)->getValue());
        }
    }

    public static function scoringQueryProvider(): \Iterator
    {
        yield 'score ordered by sector' => [
            'getScoreOrderedBySectorInScope',
            [1, 2],
            'getScalarResult',
            'SELECT ' . self::EMISSIONS_SELECT . self::CANDIDATE_PERMIT_FROM . self::IN_STOCK
            . ' AND IDENTITY(epa.sectors) = ?2 AND epa.inScope = 1'
            . ' ORDER BY icp.randomizedScore DESC',
            [1 => 1, 2 => 2],
        ];
        yield 'unsuccessful, score ordered' => [
            'getUnsuccessfulScoreOrderedInScope',
            [1],
            'getScalarResult',
            'SELECT ' . self::EMISSIONS_SELECT . self::CANDIDATE_PERMIT_FROM . self::IN_STOCK
            . ' AND icp.successful = 0 AND epa.inScope = 1'
            . ' ORDER BY icp.randomizedScore DESC',
            [1 => 1],
        ];
        // The traffic-area filter brings its own join in, after the shared chain.
        yield 'unsuccessful in one traffic area' => [
            'getUnsuccessfulScoreOrderedInScope',
            [1, 2],
            'getScalarResult',
            'SELECT ' . self::EMISSIONS_SELECT . self::CANDIDATE_PERMIT_FROM
            . ' INNER JOIN epa.licence l' . self::IN_STOCK
            . ' AND icp.successful = 0 AND epa.inScope = 1 AND IDENTITY(l.trafficArea) = ?2'
            . ' ORDER BY icp.randomizedScore DESC',
            [1 => 1, 2 => 2],
        ];
        yield 'successful, score ordered' => [
            'getSuccessfulScoreOrderedInScope',
            [1],
            'getResult',
            'SELECT icp' . self::CANDIDATE_PERMIT_FROM . self::IN_STOCK
            . ' AND icp.successful = 1 AND epa.inScope = 1'
            . ' ORDER BY icp.randomizedScore DESC',
            [1 => 1],
        ];
        yield 'deviation source values' => [
            'fetchDeviationSourceValues',
            [1],
            'getScalarResult',
            'SELECT icp.id as candidatePermitId, l.licNo, epa.id as applicationId,'
            . '(ipa.requiredEuro5 + ipa.requiredEuro6) as permitsRequired'
            . self::CANDIDATE_PERMIT_FROM . ' INNER JOIN epa.licence l'
            . self::IN_STOCK . ' AND epa.inScope = 1',
            [1 => 1],
        ];
    }

    /**
     * The scoring report is the only query that reaches past the licence to the organisation and
     * traffic area, and the only one to substitute a placeholder for a missing sector.
     */
    public function testFetchScoringReport(): void
    {
        $qb = $this->expectEntityManagerQb();
        $qb->stubbedQuery()->expects('getScalarResult')->withNoArgs()->andReturn(['RESULTS']);

        $this->assertSame(['RESULTS'], $this->sut->fetchScoringReport(1));

        $this->assertSame(
            'SELECT icp.id as candidatePermitId, epa.id as applicationId, o.name as organisationName,'
            . ' icp.applicationScore as candidatePermitApplicationScore,'
            . ' icp.intensityOfUse as candidatePermitIntensityOfUse,'
            . ' icp.randomFactor as candidatePermitRandomFactor,'
            . ' icp.randomizedScore as candidatePermitRandomizedScore,'
            . ' IDENTITY(icp.requestedEmissionsCategory) as candidatePermitRequestedEmissionsCategory,'
            . ' IDENTITY(icp.assignedEmissionsCategory) as candidatePermitAssignedEmissionsCategory,'
            . ' IDENTITY(epa.internationalJourneys) as applicationInternationalJourneys,'
            . " COALESCE(s.name, 'N/A') as applicationSectorName,"
            . ' l.licNo as licenceNo, ta.id as trafficAreaId, ta.name as trafficAreaName,'
            . ' icp.successful as candidatePermitSuccessful,'
            . ' IDENTITY(icp.irhpPermitRange) as candidatePermitRangeId'
            . self::CANDIDATE_PERMIT_FROM
            . ' INNER JOIN epa.licence l LEFT JOIN epa.sectors s'
            . ' INNER JOIN l.trafficArea ta INNER JOIN l.organisation o'
            . self::IN_STOCK . ' AND epa.status = ?2 AND epa.inScope = 1',
            $qb->getDQL(),
        );
        $this->assertSame(1, $qb->getParameter(1)->getValue());
        $this->assertSame(IrhpInterface::STATUS_UNDER_CONSIDERATION, $qb->getParameter(2)->getValue());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('countQueryProvider')]
    public function testCountQueries(
        string $method,
        array $args,
        string $expectedDql,
        array $expectedParameters,
        mixed $result,
        mixed $expected,
    ): void {
        $qb = $this->expectEntityManagerQb();
        $qb->stubbedQuery()->expects('getSingleScalarResult')->withNoArgs()->andReturn($result);

        $this->assertSame($expected, $this->sut->{$method}(...$args));

        $this->assertSame($expectedDql, $qb->getDQL());

        foreach ($expectedParameters as $position => $expectedValue) {
            $this->assertSame($expectedValue, $qb->getParameter($position)->getValue());
        }
    }

    public static function countQueryProvider(): \Iterator
    {
        // The devolved-administration count identifies the administration by traffic area.
        $daCount = 'SELECT count(icp.id) FROM ' . IrhpCandidatePermitEntity::class . ' icp'
            . ' INNER JOIN icp.irhpPermitApplication ipa INNER JOIN ipa.irhpApplication epa'
            . ' INNER JOIN ipa.irhpPermitWindow ipw INNER JOIN epa.licence l'
            . self::IN_STOCK
            . ' AND icp.successful = 1 AND IDENTITY(l.trafficArea) = ?2 AND epa.inScope = 1';

        $successful = 'SELECT count(icp)' . self::CANDIDATE_PERMIT_FROM . self::IN_STOCK
            . ' AND icp.successful = 1 AND epa.inScope = 1';

        yield 'devolved administration' => [
            'getSuccessfulDaCountInScope',
            [1, 2],
            $daCount,
            [1 => 1, 2 => 2],
            5,
            5,
        ];
        // Only this one guards against a null count; the others pass it straight back.
        yield 'devolved administration, no rows' => [
            'getSuccessfulDaCountInScope',
            [1, 2],
            $daCount,
            [1 => 1, 2 => 2],
            null,
            0,
        ];
        yield 'successful' => ['getSuccessfulCountInScope', [1], $successful, [1 => 1], 5, 5];
        yield 'successful in one emissions category' => [
            'getSuccessfulCountInScope',
            [1, 'ec'],
            $successful . ' AND IDENTITY(icp.assignedEmissionsCategory) = ?2',
            [1 => 1, 2 => 'ec'],
            5,
            5,
        ];
    }

    public function testMarkAsExpired(): void
    {
        $this->expectQueryWithData(ExpireIrhpApplicationsQuery::class, []);

        $this->sut->markAsExpired();
    }

    /**
     * The scope queries run as raw SQL against the connection, so they are pinned as literal
     * strings — nothing else checks them.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('rawReadProvider')]
    public function testRawReads(
        string $method,
        array $args,
        string $expectedSql,
        array $expectedParameters,
        array $rows,
        mixed $expected,
    ): void {
        $this->expectConnectionQuery('executeQuery', $expectedSql, $expectedParameters, $rows);

        $this->assertSame($expected, $this->sut->{$method}(...$args));
    }

    public static function rawReadProvider(): \Iterator
    {
        yield 'application ids awaiting scoring' => [
            'fetchApplicationIdsAwaitingScoring',
            [14],
            'select e.id from irhp_application e '
            . 'inner join licence as l on e.licence_id = l.id '
            . self::IN_WINDOWS_OF_STOCK
            . 'and e.status = :status '
            . self::LICENCE_IS_LIVE,
            ['stockId' => 14] + self::SCORING_LICENCE_PARAMETERS,
            [['id' => 14], ['id' => 15], ['id' => 16]],
            [14, 15, 16],
        ];
        yield 'in scope, under consideration' => [
            'fetchInScopeUnderConsiderationApplicationIds',
            [14],
            'select e.id from irhp_application e '
            . self::IN_WINDOWS_OF_STOCK
            . 'and e.in_scope = 1 '
            . 'and e.status = :status',
            ['stockId' => 14, 'status' => IrhpInterface::STATUS_UNDER_CONSIDERATION],
            [['id' => 14], ['id' => 15]],
            [14, 15],
        ];
        yield 'application to country associations' => [
            'fetchApplicationIdToCountryIdAssociations',
            [14],
            'select e.id as applicationId, eacl.country_id as countryId '
            . 'from irhp_application_country_link eacl '
            . 'inner join irhp_application as e on e.id = eacl.irhp_application_id '
            . self::IN_WINDOWS_OF_STOCK
            . 'and e.in_scope = 1 ',
            ['stockId' => 14],
            [['applicationId' => 102, 'countryId' => 'AT']],
            [['applicationId' => 102, 'countryId' => 'AT']],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('hasInScopeProvider')]
    public function testHasInScopeUnderConsiderationApplications(array $applicationIds, bool $expected): void
    {
        $this->sut->expects('fetchInScopeUnderConsiderationApplicationIds')
            ->with(14)
            ->andReturn($applicationIds);

        $this->assertSame($expected, $this->sut->hasInScopeUnderConsiderationApplications(14));
    }

    public static function hasInScopeProvider(): \Iterator
    {
        yield 'some' => [[1, 2, 3], true];
        yield 'none' => [[], false];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('scopeUpdateProvider')]
    public function testScopeUpdates(string $method, string $expectedSql, array $expectedParameters): void
    {
        $this->expectConnectionQuery('executeStatement', $expectedSql, $expectedParameters, 1);

        $this->sut->{$method}(7);
    }

    public static function scopeUpdateProvider(): \Iterator
    {
        yield 'clear' => [
            'clearScope',
            'update irhp_application e '
            . 'set e.in_scope = 0 '
            . 'where e.id in ('
            . '    select irhp_application_id from irhp_permit_application where irhp_permit_window_id in ('
            . '        select id from irhp_permit_window where irhp_permit_stock_id = :stockId'
            . '    )'
            . ')',
            ['stockId' => 7],
        ];
        // Applying scope re-checks the licence, so an operator whose licence lapsed between runs
        // drops out of the next scoring round.
        yield 'apply' => [
            'applyScope',
            'update irhp_application as e '
            . 'inner join licence as l on e.licence_id = l.id '
            . 'set e.in_scope = 1 '
            . self::IN_WINDOWS_OF_STOCK
            . 'and e.status = :status '
            . self::LICENCE_IS_LIVE,
            ['stockId' => 7] + self::SCORING_LICENCE_PARAMETERS,
        ];
    }

    /**
     * The summaries are assembled by string concatenation, one bound parameter per status, with
     * the filter column and the ORDER BY columns escaped rather than bound.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('summaryProvider')]
    public function testSummaries(
        string $method,
        array $args,
        string $expectedSql,
        array $expectedParameters,
    ): void {
        $rows = [['applicationRef' => 'OB123 / 1']];
        $this->expectConnectionQuery('executeQuery', $expectedSql, $expectedParameters, $rows);

        $this->assertSame($rows, $this->sut->{$method}(...$args));
    }

    public static function summaryProvider(): \Iterator
    {
        $applicationsSummary = 'select '
            . "concat (l.lic_no, ' / ', ia.id) as applicationRef, "
            . 'sum(ifnull(ipa.permits_required, 0) + ifnull(ipa.required_euro5, 0)'
            . ' + ifnull(ipa.required_euro6, 0) + ifnull(ipa.required_standard, 0)'
            . ' + ifnull(ipa.required_cabotage, 0)) as permitsRequired, '
            . 'ia.id as id, '
            . 'ia.irhp_permit_type_id as typeId, '
            . 'ia.status as statusId, '
            . 'ia.date_received as dateReceived, '
            . 'srd.description as statusDescription, '
            . 'trd.description as typeDescription, '
            . 'ips.period_name_key as periodNameKey, '
            . 'ips.valid_to as stockValidTo, '
            . 'l.id as licenceId '
            . 'from '
            . 'irhp_application ia '
            . 'inner join licence l on ia.licence_id = l.id '
            . 'inner join ref_data srd on ia.status = srd.id '
            . 'left join irhp_permit_application ipa on ipa.irhp_application_id = ia.id '
            . 'inner join irhp_permit_type ipt on ia.irhp_permit_type_id = ipt.id '
            . 'inner join ref_data trd on ipt.name = trd.id '
            . 'left join irhp_permit_window ipw on ipa.irhp_permit_window_id = ipw.id '
            . 'left join irhp_permit_stock ips on ipw.irhp_permit_stock_id = ips.id '
            . 'where %s = :filterByColumnValue '
            . 'and ia.status in (%s) '
            . 'group by ia.id'
            . ' order by %s';

        $selfserveStatuses = [
            IrhpInterface::STATUS_NOT_YET_SUBMITTED,
            IrhpInterface::STATUS_UNDER_CONSIDERATION,
            IrhpInterface::STATUS_AWAITING_FEE,
            IrhpInterface::STATUS_FEE_PAID,
            IrhpInterface::STATUS_ISSUING,
        ];

        yield 'selfserve issued permits' => [
            'fetchSelfserveIssuedPermitsSummary',
            [7],
            'select '
            . "concat (l.lic_no, ' / ', ia.id) as applicationRef, "
            . 'ia.id as id, '
            . 'ia.irhp_permit_type_id as typeId, '
            . 'ia.status as statusId, '
            . 'l.id as licenceId, '
            . 'l.lic_no as licNo, '
            . 'srd.description as statusDescription, '
            . 'trd.description as typeDescription, '
            . 'count(ip.id) as validPermitCount '
            . 'from '
            . 'irhp_application ia '
            . 'inner join licence l on ia.licence_id = l.id '
            . 'inner join ref_data srd on ia.status = srd.id '
            . 'left join irhp_permit_application ipa on ipa.irhp_application_id = ia.id '
            . 'left join irhp_permit ip on ip.irhp_permit_application_id = ipa.id '
            . 'inner join irhp_permit_type ipt on ia.irhp_permit_type_id = ipt.id '
            . 'inner join ref_data trd on ipt.name = trd.id '
            . 'where l.`organisation_id` = :filterByColumnValue '
            . 'and ia.status in (:applicationStatus1) '
            . 'and ('
            . '    ia.irhp_permit_type_id in (:permitType1, :permitType2) '
            . '    or '
            . '    ip.status in (:permitStatus1, :permitStatus2, :permitStatus3, :permitStatus4, :permitStatus5)'
            . ') '
            . 'group by ia.id'
            . ' order by l.`lic_no`, trd.`description`, ia.`id`',
            [
                'permitType1' => IrhpPermitType::IRHP_PERMIT_TYPE_ID_CERT_ROADWORTHINESS_VEHICLE,
                'permitType2' => IrhpPermitType::IRHP_PERMIT_TYPE_ID_CERT_ROADWORTHINESS_TRAILER,
                'permitStatus1' => IrhpPermit::STATUS_PENDING,
                'permitStatus2' => IrhpPermit::STATUS_AWAITING_PRINTING,
                'permitStatus3' => IrhpPermit::STATUS_PRINTING,
                'permitStatus4' => IrhpPermit::STATUS_PRINTED,
                'permitStatus5' => IrhpPermit::STATUS_ERROR,
                'filterByColumnValue' => 7,
                'applicationStatus1' => IrhpInterface::STATUS_VALID,
            ],
        ];
        yield 'selfserve applications' => [
            'fetchSelfserveApplicationsSummary',
            [7],
            sprintf(
                $applicationsSummary,
                'l.`organisation_id`',
                ':applicationStatus1, :applicationStatus2, :applicationStatus3,'
                . ' :applicationStatus4, :applicationStatus5',
                'l.`lic_no`, trd.`description`, ia.`id`',
            ),
            ['filterByColumnValue' => 7] + self::numberedStatuses($selfserveStatuses),
        ];
        // Internal lists every status, newest first, and filters by licence rather than operator.
        yield 'internal applications' => [
            'fetchInternalApplicationsSummary',
            [7],
            sprintf(
                $applicationsSummary,
                'l.`id`',
                implode(
                    ', ',
                    array_map(
                        static fn(int $index): string => ':applicationStatus' . $index,
                        range(1, count(IrhpInterface::ALL_STATUSES)),
                    ),
                ),
                'ia.`id` DESC',
            ),
            ['filterByColumnValue' => 7] + self::numberedStatuses(IrhpInterface::ALL_STATUSES),
        ];
        yield 'internal applications in one status' => [
            'fetchInternalApplicationsSummary',
            [7, IrhpInterface::STATUS_UNDER_CONSIDERATION],
            sprintf($applicationsSummary, 'l.`id`', ':applicationStatus1', 'ia.`id` DESC'),
            [
                'filterByColumnValue' => 7,
                'applicationStatus1' => IrhpInterface::STATUS_UNDER_CONSIDERATION,
            ],
        ];
    }

    /**
     * The filter and sort columns reach the SQL by concatenation, so the escaping is the only
     * thing between a column name and an injection. They are hard-coded call sites today; this
     * covers the guard directly so it stays that way.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('columnNameProvider')]
    public function testEscapeColumnName(string $columnName, ?string $expected, ?string $expectedMessage): void
    {
        $escape = \Closure::bind(
            fn(string $name): string => $this->escapeColumnName($name),
            $this->sut,
            Repo::class,
        );

        if ($expectedMessage !== null) {
            $this->expectException(\RuntimeException::class);
            $this->expectExceptionMessage($expectedMessage);
        }

        $this->assertSame($expected, $escape($columnName));
    }

    public static function columnNameProvider(): \Iterator
    {
        yield 'an identifier' => ['lic_no', '`lic_no`', null];
        yield 'a prefixed identifier' => ['l.lic_no', 'l.`lic_no`', null];
        yield 'too many elements' => [
            'a.b.c',
            null,
            'Unexpected number of elements in column name a.b.c',
        ];
        // An underscore is allowed in the identifier but not in the prefix.
        yield 'a backtick in the identifier' => [
            'l.`; drop table licence; --',
            null,
            'Unpermitted characters in identifier `; drop table licence; --',
        ];
        yield 'an underscore in the prefix' => [
            'my_alias.lic_no',
            null,
            'Unpermitted characters in prefix my_alias',
        ];
    }

    /**
     * Several methods build straight off the EntityManager rather than through the repository.
     */
    private function expectEntityManagerQb(): TestQueryBuilder
    {
        $qb = $this->newRealQb();

        $this->em->expects('createQueryBuilder')->withNoArgs()->andReturn($qb);

        return $qb;
    }

    private function expectConnectionQuery(
        string $method,
        string $expectedSql,
        array $expectedParameters,
        mixed $result,
    ): void {
        if (is_array($result)) {
            $dbalResult = m::mock(DbalResult::class);
            $dbalResult->expects('fetchAllAssociative')->withNoArgs()->andReturn($result);
            $result = $dbalResult;
        }

        $connection = m::mock(Connection::class);
        $connection->expects($method)->with($expectedSql, $expectedParameters)->andReturn($result);

        $this->em->expects('getConnection')->withNoArgs()->andReturn($connection);
    }

    /**
     * @param list<string> $statuses
     *
     * @return array<string, string> applicationStatus1..n, as the summaries bind them
     */
    private static function numberedStatuses(array $statuses): array
    {
        $parameters = [];

        foreach (array_values($statuses) as $index => $status) {
            $parameters['applicationStatus' . ($index + 1)] = $status;
        }

        return $parameters;
    }
}
