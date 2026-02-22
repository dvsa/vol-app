<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result as DbalResult;
use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Api\Domain\Repository\IrhpApplication;
use Dvsa\Olcs\Api\Domain\Repository\Query\Permits\ExpireIrhpApplications as ExpireIrhpApplicationsQuery;
use Dvsa\Olcs\Api\Entity\IrhpInterface;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication as Entity;
use Dvsa\Olcs\Api\Entity\Permits\IrhpCandidatePermit as IrhpCandidatePermitEntity;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitType;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermit;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Mockery as m;

class IrhpApplicationTest extends RepositoryTestCase
{
    public function setUp(): void
    {
        $this->setUpSut(IrhpApplication::class, true);
    }

    public function testFetchByWindowId(): void
    {
        $qb = $this->createMockQb('BLAH');

        $this->mockCreateQueryBuilder($qb);

        $qb->shouldReceive('getQuery')->andReturn(
            m::mock()->shouldReceive('execute')
                ->shouldReceive('getResult')
                ->andReturn(['RESULTS'])
                ->getMock()
        );
        $this->assertEquals(['RESULTS'], $this->sut->fetchByWindowId('ID', ['S1', 'S2']));

        $expectedQuery = 'BLAH '
            . 'INNER JOIN ia.irhpPermitApplications ipa '
            . 'INNER JOIN ipa.irhpPermitWindow ipw '
            . 'AND ipw.id = [[ID]] '
            . 'AND ia.status IN [[["S1","S2"]]]';

        $this->assertEquals($expectedQuery, $this->query);
    }

    public function testFetchAllAwaitingFee(): void
    {
        $queryBuilder = m::mock(QueryBuilder::class);
        $this->em->shouldReceive('createQueryBuilder')->once()->andReturn($queryBuilder);

        $irhpApplications = [
            m::mock(Entity::class),
            m::mock(Entity::class),
        ];

        $queryBuilder->shouldReceive('select')
            ->with('ia')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('from')
            ->with(Entity::class, 'ia')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('where')
            ->with('ia.status = :status')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('setParameter')
            ->with('status', IrhpInterface::STATUS_AWAITING_FEE)
            ->once()
            ->andReturnSelf()
            ->shouldReceive('getQuery->getResult')
            ->once()
            ->andReturn($irhpApplications);

        $this->assertEquals(
            $irhpApplications,
            $this->sut->fetchAllAwaitingFee()
        );
    }

    public function testFetchForRoadworthinessReport(): void
    {
        $startDate = '2020-12-25';
        $endDate = '2020-12-31';

        $queryBuilder = $this->createMockQb('query');

        $this->mockCreateQueryBuilder($queryBuilder);

        $queryBuilder->shouldReceive('getQuery')->andReturn(
            m::mock()->shouldReceive('execute')
                ->shouldReceive('getResult')
                ->andReturn(['RESULTS'])
                ->getMock()
        );

        self::assertEquals(['RESULTS'], $this->sut->fetchForRoadworthinessReport($startDate, $endDate));

        $expectedQuery = 'query'
            . ' AND ia.irhpPermitType IN [[[' . implode(',', IrhpPermitType::CERTIFICATE_TYPES) . ']]]'
            . ' AND ia.status NOT IN [[["' . IrhpInterface::STATUS_NOT_YET_SUBMITTED . '","' . IrhpInterface::STATUS_CANCELLED . '","' . IrhpInterface::STATUS_WITHDRAWN . '"]]]'
            . ' AND ia.dateReceived BETWEEN [[' . $startDate . ']] AND [[' . $endDate . ']]';

        self::assertEquals($expectedQuery, $this->query);
    }

    public function testFetchAllValidRoadworthiness(): void
    {
        $queryBuilder = $this->createMockQb('BLAH');

        $this->mockCreateQueryBuilder($queryBuilder);

        $queryBuilder->shouldReceive('getQuery')->andReturn(
            m::mock()->shouldReceive('execute')
                ->shouldReceive('getResult')
                ->andReturn(['RESULTS'])
                ->getMock()
        );

        self::assertEquals(['RESULTS'], $this->sut->fetchAllValidRoadworthiness());

        $expectedQuery = 'BLAH '
            . 'AND ia.status = [[' . IrhpInterface::STATUS_VALID . ']] '
            . 'AND ia.irhpPermitType IN [[[6,7]]]';

        self::assertEquals($expectedQuery, $this->query);
    }

    public function testMarkAsExpired(): void
    {
        $this->expectQueryWithData(ExpireIrhpApplicationsQuery::class, []);
        $this->sut->markAsExpired();
    }

    public function testFetchApplicationIdsAwaitingScoring(): void
    {
        $stockId = 14;

        $dbalResult = m::mock(DbalResult::class);
        $dbalResult->expects('fetchAll')
            ->andReturn(
                [
                    [ 'id' => 14 ],
                    [ 'id' => 15 ],
                    [ 'id' => 16 ],
                ]
            );

        $connection = m::mock(Connection::class);
        $connection->shouldReceive('executeQuery')
            ->with(
                'select e.id from irhp_application e ' .
                'inner join licence as l on e.licence_id = l.id ' .
                'where e.id in (' .
                '    select irhp_application_id from irhp_permit_application where irhp_permit_window_id in (' .
                '        select id from irhp_permit_window where irhp_permit_stock_id = :stockId' .
                '    )' .
                ') ' .
                'and e.status = :status ' .
                'and l.licence_type in (:licenceType1, :licenceType2, :licenceType3) ' .
                'and l.status in (:licenceStatus1, :licenceStatus2, :licenceStatus3)',
                [
                    'stockId' => $stockId,
                    'status' => IrhpInterface::STATUS_UNDER_CONSIDERATION,
                    'licenceType1' => LicenceEntity::LICENCE_TYPE_RESTRICTED,
                    'licenceType2' => LicenceEntity::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                    'licenceType3' => LicenceEntity::LICENCE_TYPE_STANDARD_NATIONAL,
                    'licenceStatus1' => LicenceEntity::LICENCE_STATUS_VALID,
                    'licenceStatus2' => LicenceEntity::LICENCE_STATUS_SUSPENDED,
                    'licenceStatus3' => LicenceEntity::LICENCE_STATUS_CURTAILED
                ]
            )
            ->once()
            ->andReturn($dbalResult);

        $this->em->shouldReceive('getConnection')->once()->andReturn($connection);

        $this->assertEquals(
            [14, 15, 16],
            $this->sut->fetchApplicationIdsAwaitingScoring($stockId)
        );
    }

    public function testFetchInScopeUnderConsiderationApplicationIds(): void
    {
        $stockId = 14;

        $dbalResult = m::mock(DbalResult::class);
        $dbalResult->expects('fetchAll')
            ->andReturn(
                [
                    [ 'id' => 14 ],
                    [ 'id' => 15 ],
                    [ 'id' => 16 ],
                ]
            );

        $connection = m::mock(Connection::class);
        $connection->shouldReceive('executeQuery')
            ->with(
                'select e.id from irhp_application e ' .
                'where e.id in (' .
                '    select irhp_application_id from irhp_permit_application where irhp_permit_window_id in (' .
                '        select id from irhp_permit_window where irhp_permit_stock_id = :stockId' .
                '    )' .
                ') ' .
                'and e.in_scope = 1 ' .
                'and e.status = :status',
                [
                    'stockId' => $stockId,
                    'status' => IrhpInterface::STATUS_UNDER_CONSIDERATION
                ]
            )
            ->once()
            ->andReturn($dbalResult);

        $this->em->shouldReceive('getConnection')->once()->andReturn($connection);

        $this->assertEquals(
            [14, 15, 16],
            $this->sut->fetchInScopeUnderConsiderationApplicationIds($stockId)
        );
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpHasInScopeUnderConsiderationApplications')]
    public function testHasInScopeUnderConsiderationApplications(mixed $applicationIds, mixed $expected): void
    {
        $stockId = 47;

        $this->sut->shouldReceive('fetchInScopeUnderConsiderationApplicationIds')
            ->with($stockId)
            ->andReturn($applicationIds);

        $this->assertEquals(
            $expected,
            $this->sut->hasInScopeUnderConsiderationApplications($stockId)
        );
    }

    public static function dpHasInScopeUnderConsiderationApplications(): array
    {
        return [
            [
                [],
                false
            ],
            [
                [5],
                true
            ],
            [
                [5, 10],
                true
            ],
        ];
    }

    public function testClearScope(): void
    {
        $stockId = 7;

        $dbalResult = m::mock(DbalResult::class);
        $dbalResult->expects('execute');

        $connection = m::mock(Connection::class);
        $connection->shouldReceive('executeQuery')
            ->with(
                'update irhp_application e ' .
                'set e.in_scope = 0 ' .
                'where e.id in (' .
                '    select irhp_application_id from irhp_permit_application where irhp_permit_window_id in (' .
                '        select id from irhp_permit_window where irhp_permit_stock_id = :stockId' .
                '    )' .
                ')',
                ['stockId' => $stockId]
            )
            ->once()
            ->andReturn($dbalResult);

        $this->em->shouldReceive('getConnection')->once()->andReturn($connection);

        $this->sut->clearScope($stockId);
    }

    public function testApplyScope(): void
    {
        $stockId = 7;

        $dbalResult = m::mock(DbalResult::class);
        $dbalResult->expects('execute');

        $connection = m::mock(Connection::class);
        $connection->shouldReceive('executeQuery')
            ->with(
                'update irhp_application as e ' .
                'inner join licence as l on e.licence_id = l.id ' .
                'set e.in_scope = 1 ' .
                'where e.id in (' .
                '    select irhp_application_id from irhp_permit_application where irhp_permit_window_id in (' .
                '        select id from irhp_permit_window where irhp_permit_stock_id = :stockId' .
                '    )' .
                ') ' .
                'and e.status = :status ' .
                'and l.licence_type in (:licenceType1, :licenceType2, :licenceType3) ' .
                'and l.status in (:licenceStatus1, :licenceStatus2, :licenceStatus3)',
                [
                    'stockId' => $stockId,
                    'status' => IrhpInterface::STATUS_UNDER_CONSIDERATION,
                    'licenceType1' => LicenceEntity::LICENCE_TYPE_RESTRICTED,
                    'licenceType2' => LicenceEntity::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                    'licenceType3' => LicenceEntity::LICENCE_TYPE_STANDARD_NATIONAL,
                    'licenceStatus1' => LicenceEntity::LICENCE_STATUS_VALID,
                    'licenceStatus2' => LicenceEntity::LICENCE_STATUS_SUSPENDED,
                    'licenceStatus3' => LicenceEntity::LICENCE_STATUS_CURTAILED
                ]
            )
            ->once()
            ->andReturn($dbalResult);

        $this->em->shouldReceive('getConnection')->once()->andReturn($connection);

        $this->sut->applyScope($stockId);
    }

    public function testGetScoreOrderedBySectorInScope(): void
    {
        $stockId = 6;
        $sectorsId = 8;

        $result = [
            ['id' => 18, 'emissions_category' => RefData::EMISSIONS_CATEGORY_EURO5_REF],
            ['id' => 24, 'emissions_category' => RefData::EMISSIONS_CATEGORY_EURO6_REF],
            ['id' => 25, 'emissions_category' => RefData::EMISSIONS_CATEGORY_EURO5_REF],
            ['id' => 31, 'emissions_category' => RefData::EMISSIONS_CATEGORY_EURO6_REF],
            ['id' => 34, 'emissions_category' => RefData::EMISSIONS_CATEGORY_EURO5_REF],
        ];

        $queryBuilder = m::mock(QueryBuilder::class);
        $this->em->shouldReceive('createQueryBuilder')->once()->andReturn($queryBuilder);

        $queryBuilder->shouldReceive('select')
            ->with('icp.id, IDENTITY(icp.requestedEmissionsCategory) as emissions_category')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('from')
            ->with(IrhpCandidatePermitEntity::class, 'icp')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('innerJoin')
            ->with('icp.irhpPermitApplication', 'ipa')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('innerJoin')
            ->with('ipa.irhpPermitWindow', 'ipw')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('innerJoin')
            ->with('ipa.irhpApplication', 'epa')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('where')
            ->with('IDENTITY(ipw.irhpPermitStock) = ?1')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('andWhere')
            ->with('IDENTITY(epa.sectors) = ?2')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('andWhere')
            ->with('epa.inScope = 1')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('orderBy')
            ->with('icp.randomizedScore', 'DESC')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('setParameter')
            ->with(1, $stockId)
            ->once()
            ->andReturnSelf()
            ->shouldReceive('setParameter')
            ->with(2, $sectorsId)
            ->once()
            ->andReturnSelf()
            ->shouldReceive('getQuery->getScalarResult')
            ->once()
            ->andReturn($result);

        $this->assertEquals(
            $result,
            $this->sut->getScoreOrderedBySectorInScope($stockId, $sectorsId)
        );
    }

    public function testGetSuccessfulDaCountInScope(): void
    {
        $successfulDaCount = 35;
        $stockId = 8;
        $jurisdictionId = 12;

        $queryBuilder = m::mock(QueryBuilder::class);
        $this->em->shouldReceive('createQueryBuilder')->once()->andReturn($queryBuilder);

        $queryBuilder->shouldReceive('select')
            ->with('count(icp.id)')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('from')
            ->with(IrhpCandidatePermitEntity::class, 'icp')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('innerJoin')
            ->with('icp.irhpPermitApplication', 'ipa')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('innerJoin')
            ->with('ipa.irhpApplication', 'epa')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('innerJoin')
            ->with('ipa.irhpPermitWindow', 'ipw')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('innerJoin')
            ->with('epa.licence', 'l')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('where')
            ->with('IDENTITY(ipw.irhpPermitStock) = ?1')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('andWhere')
            ->with('icp.successful = 1')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('andWhere')
            ->with('IDENTITY(l.trafficArea) = ?2')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('andWhere')
            ->with('epa.inScope = 1')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('setParameter')
            ->with(1, $stockId)
            ->once()
            ->andReturnSelf()
            ->shouldReceive('setParameter')
            ->with(2, $jurisdictionId)
            ->once()
            ->andReturnSelf()
            ->shouldReceive('getQuery->getSingleScalarResult')
            ->once()
            ->andReturn($successfulDaCount);

        $this->assertEquals(
            $successfulDaCount,
            $this->sut->getSuccessfulDaCountInScope($stockId, $jurisdictionId)
        );
    }

    public function testGetUnsuccessfulScoreOrderedInScopeWithoutTrafficAreaId(): void
    {
        $result = [
            ['id' => 35, 'emissions_category' => RefData::EMISSIONS_CATEGORY_EURO5_REF],
            ['id' => 37, 'emissions_category' => RefData::EMISSIONS_CATEGORY_EURO6_REF],
            ['id' => 41, 'emissions_category' => RefData::EMISSIONS_CATEGORY_EURO5_REF]
        ];

        $stockId = 3;

        $queryBuilder = m::mock(QueryBuilder::class);
        $this->em->shouldReceive('createQueryBuilder')->once()->andReturn($queryBuilder);

        $queryBuilder->shouldReceive('select')
            ->with('icp.id, IDENTITY(icp.requestedEmissionsCategory) as emissions_category')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('from')
            ->with(IrhpCandidatePermitEntity::class, 'icp')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('innerJoin')
            ->with('icp.irhpPermitApplication', 'ipa')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('innerJoin')
            ->with('ipa.irhpPermitWindow', 'ipw')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('innerJoin')
            ->with('ipa.irhpApplication', 'epa')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('where')
            ->with('IDENTITY(ipw.irhpPermitStock) = ?1')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('andWhere')
            ->with('icp.successful = 0')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('andWhere')
            ->with('epa.inScope = 1')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('orderBy')
            ->with('icp.randomizedScore', 'DESC')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('setParameter')
            ->with(1, $stockId)
            ->once()
            ->andReturnSelf()
            ->shouldReceive('getQuery->getScalarResult')
            ->once()
            ->andReturn($result);

        $this->assertEquals(
            $result,
            $this->sut->getUnsuccessfulScoreOrderedInScope($stockId)
        );
    }

    public function testGetUnsuccessfulScoreOrderedInScopeWithTrafficAreaId(): void
    {
        $result = [
            ['id' => 35, 'emissions_category' => RefData::EMISSIONS_CATEGORY_EURO5_REF],
            ['id' => 37, 'emissions_category' => RefData::EMISSIONS_CATEGORY_EURO6_REF],
            ['id' => 41, 'emissions_category' => RefData::EMISSIONS_CATEGORY_EURO5_REF]
        ];

        $stockId = 3;
        $trafficAreaId = 12;

        $queryBuilder = m::mock(QueryBuilder::class);
        $this->em->shouldReceive('createQueryBuilder')->once()->andReturn($queryBuilder);

        $queryBuilder->shouldReceive('select')
            ->with('icp.id, IDENTITY(icp.requestedEmissionsCategory) as emissions_category')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('from')
            ->with(IrhpCandidatePermitEntity::class, 'icp')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('innerJoin')
            ->with('icp.irhpPermitApplication', 'ipa')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('innerJoin')
            ->with('ipa.irhpPermitWindow', 'ipw')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('innerJoin')
            ->with('ipa.irhpApplication', 'epa')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('where')
            ->with('IDENTITY(ipw.irhpPermitStock) = ?1')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('andWhere')
            ->with('icp.successful = 0')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('andWhere')
            ->with('epa.inScope = 1')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('orderBy')
            ->with('icp.randomizedScore', 'DESC')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('setParameter')
            ->with(1, $stockId)
            ->once()
            ->andReturnSelf()
            ->shouldReceive('innerJoin')
            ->with('epa.licence', 'l')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('andWhere')
            ->with('IDENTITY(l.trafficArea) = ?2')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('setParameter')
            ->with(2, $trafficAreaId)
            ->once()
            ->andReturnSelf()
            ->shouldReceive('getQuery->getScalarResult')
            ->once()
            ->andReturn($result);

        $this->assertEquals(
            $result,
            $this->sut->getUnsuccessfulScoreOrderedInScope($stockId, $trafficAreaId)
        );
    }

    public function testGetSuccessfulCountInScopeWithoutEmissionsCategoryId(): void
    {
        $stockId = 7;
        $successfulCount = 15;

        $queryBuilder = m::mock(QueryBuilder::class);
        $this->em->shouldReceive('createQueryBuilder')->once()->andReturn($queryBuilder);

        $queryBuilder->shouldReceive('select')
            ->with('count(icp)')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('from')
            ->with(IrhpCandidatePermitEntity::class, 'icp')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('innerJoin')
            ->with('icp.irhpPermitApplication', 'ipa')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('innerJoin')
            ->with('ipa.irhpPermitWindow', 'ipw')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('innerJoin')
            ->with('ipa.irhpApplication', 'epa')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('where')
            ->with('IDENTITY(ipw.irhpPermitStock) = ?1')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('andWhere')
            ->with('icp.successful = 1')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('andWhere')
            ->with('epa.inScope = 1')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('setParameter')
            ->with(1, $stockId)
            ->andReturnSelf()
            ->shouldReceive('getQuery->getSingleScalarResult')
            ->once()
            ->andReturn($successfulCount);

        $this->assertEquals(
            $successfulCount,
            $this->sut->getSuccessfulCountInScope($stockId)
        );
    }

    public function testGetSuccessfulCountInScopeWithEmissionsCategoryId(): void
    {
        $stockId = 7;
        $assignedEmissionsCategoryId = RefData::EMISSIONS_CATEGORY_EURO5_REF;
        $successfulCount = 15;

        $queryBuilder = m::mock(QueryBuilder::class);
        $this->em->shouldReceive('createQueryBuilder')->once()->andReturn($queryBuilder);

        $queryBuilder->shouldReceive('select')
            ->with('count(icp)')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('from')
            ->with(IrhpCandidatePermitEntity::class, 'icp')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('innerJoin')
            ->with('icp.irhpPermitApplication', 'ipa')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('innerJoin')
            ->with('ipa.irhpPermitWindow', 'ipw')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('innerJoin')
            ->with('ipa.irhpApplication', 'epa')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('where')
            ->with('IDENTITY(ipw.irhpPermitStock) = ?1')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('andWhere')
            ->with('icp.successful = 1')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('andWhere')
            ->with('epa.inScope = 1')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('setParameter')
            ->with(1, $stockId)
            ->once()
            ->andReturnSelf()
            ->shouldReceive('andWhere')
            ->with('IDENTITY(icp.assignedEmissionsCategory) = ?2')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('setParameter')
            ->with(2, $assignedEmissionsCategoryId)
            ->once()
            ->andReturnSelf()
            ->shouldReceive('getQuery->getSingleScalarResult')
            ->once()
            ->andReturn($successfulCount);

        $this->assertEquals(
            $successfulCount,
            $this->sut->getSuccessfulCountInScope($stockId, $assignedEmissionsCategoryId)
        );
    }

    public function testGetSuccessfulScoreOrderedInScope(): void
    {
        $stockId = 7;

        $expectedResult = [
            m::mock(IrhpCandidatePermitEntity::class),
            m::mock(IrhpCandidatePermitEntity::class),
            m::mock(IrhpCandidatePermitEntity::class),
        ];

        $queryBuilder = m::mock(QueryBuilder::class);
        $this->em->shouldReceive('createQueryBuilder')->once()->andReturn($queryBuilder);

        $queryBuilder->shouldReceive('select')
            ->with('icp')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('from')
            ->with(IrhpCandidatePermitEntity::class, 'icp')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('innerJoin')
            ->with('icp.irhpPermitApplication', 'ipa')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('innerJoin')
            ->with('ipa.irhpPermitWindow', 'ipw')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('innerJoin')
            ->with('ipa.irhpApplication', 'epa')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('where')
            ->with('IDENTITY(ipw.irhpPermitStock) = ?1')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('andWhere')
            ->with('icp.successful = 1')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('andWhere')
            ->with('epa.inScope = 1')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('orderBy')
            ->with('icp.randomizedScore', 'DESC')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('setParameter')
            ->with(1, $stockId)
            ->once()
            ->andReturnSelf()
            ->shouldReceive('getQuery->getResult')
            ->once()
            ->andReturn($expectedResult);

        $this->assertEquals(
            $expectedResult,
            $this->sut->getSuccessfulScoreOrderedInScope($stockId)
        );
    }

    public function testFetchDeviationSourceValues(): void
    {
        $deviationSourceValues = [
            [
                'candidatePermitId' => 102,
                'licNo' => 'PD2737280',
                'applicationId' => 202,
                'permitsRequired' => 12
            ],
            [
                'candidatePermitId' => 104,
                'licNo' => 'OG4569803',
                'applicationId' => 205,
                'permitsRequired' => 6
            ]
        ];

        $stockId = 3;

        $queryBuilder = m::mock(QueryBuilder::class);
        $this->em->shouldReceive('createQueryBuilder')->once()->andReturn($queryBuilder);

        $queryBuilder->shouldReceive('select')
            ->with(
                'icp.id as candidatePermitId, l.licNo, epa.id as applicationId,' .
                '(ipa.requiredEuro5 + ipa.requiredEuro6) as permitsRequired'
            )
            ->once()
            ->andReturnSelf()
            ->shouldReceive('from')
            ->with(IrhpCandidatePermitEntity::class, 'icp')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('innerJoin')
            ->with('icp.irhpPermitApplication', 'ipa')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('innerJoin')
            ->with('ipa.irhpPermitWindow', 'ipw')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('innerJoin')
            ->with('ipa.irhpApplication', 'epa')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('innerJoin')
            ->with('epa.licence', 'l')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('where')
            ->with('IDENTITY(ipw.irhpPermitStock) = ?1')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('andWhere')
            ->with('epa.inScope = 1')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('setParameter')
            ->with(1, $stockId)
            ->once()
            ->andReturnSelf()
            ->shouldReceive('getQuery->getScalarResult')
            ->once()
            ->andReturn($deviationSourceValues);

        $this->assertEquals(
            $deviationSourceValues,
            $this->sut->fetchDeviationSourceValues($stockId)
        );
    }

    public function testFetchApplicationIdToCountryIdAssociations(): void
    {
        $stockId = 14;

        $associations = [
            102 => 'AT',
            102 => 'RU',
            103 => 'GR'
        ];

        $dbalResult = m::mock(DbalResult::class);
        $dbalResult->expects('fetchAll')
            ->andReturn($associations);

        $connection = m::mock(Connection::class);
        $connection->shouldReceive('executeQuery')
            ->with(
                'select e.id as applicationId, eacl.country_id as countryId ' .
                'from irhp_application_country_link eacl ' .
                'inner join irhp_application as e on e.id = eacl.irhp_application_id ' .
                'where e.id in (' .
                '    select irhp_application_id from irhp_permit_application where irhp_permit_window_id in (' .
                '        select id from irhp_permit_window where irhp_permit_stock_id = :stockId' .
                '    )' .
                ') ' .
                'and e.in_scope = 1 ',
                ['stockId' => $stockId]
            )
            ->once()
            ->andReturn($dbalResult);

        $this->em->shouldReceive('getConnection')->once()->andReturn($connection);

        $this->assertEquals(
            $associations,
            $this->sut->fetchApplicationIdToCountryIdAssociations($stockId)
        );
    }

    public function testFetchScoringReport(): void
    {
        $scoringReport = [
            'row1' => 'rowContent1',
            'row2' => 'rowContent2'
        ];

        $stockId = 3;

        $queryBuilder = m::mock(QueryBuilder::class);
        $this->em->shouldReceive('createQueryBuilder')->once()->andReturn($queryBuilder);

        $queryBuilder->shouldReceive('select')
            ->with(
                'icp.id as candidatePermitId, ' .
                'epa.id as applicationId, ' .
                'o.name as organisationName, ' .
                'icp.applicationScore as candidatePermitApplicationScore, ' .
                'icp.intensityOfUse as candidatePermitIntensityOfUse, ' .
                'icp.randomFactor as candidatePermitRandomFactor, ' .
                'icp.randomizedScore as candidatePermitRandomizedScore, ' .
                'IDENTITY(icp.requestedEmissionsCategory) as candidatePermitRequestedEmissionsCategory, ' .
                'IDENTITY(icp.assignedEmissionsCategory) as candidatePermitAssignedEmissionsCategory, ' .
                'IDENTITY(epa.internationalJourneys) as applicationInternationalJourneys, ' .
                'COALESCE(s.name, \'N/A\') as applicationSectorName, ' .
                'l.licNo as licenceNo, ' .
                'ta.id as trafficAreaId, ' .
                'ta.name as trafficAreaName, ' .
                'icp.successful as candidatePermitSuccessful, ' .
                'IDENTITY(icp.irhpPermitRange) as candidatePermitRangeId'
            )
            ->once()
            ->andReturnSelf()
            ->shouldReceive('from')
            ->with(IrhpCandidatePermitEntity::class, 'icp')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('innerJoin')
            ->with('icp.irhpPermitApplication', 'ipa')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('innerJoin')
            ->with('ipa.irhpPermitWindow', 'ipw')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('innerJoin')
            ->with('ipa.irhpApplication', 'epa')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('innerJoin')
            ->with('epa.licence', 'l')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('leftJoin')
            ->with('epa.sectors', 's')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('innerJoin')
            ->with('l.trafficArea', 'ta')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('innerJoin')
            ->with('l.organisation', 'o')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('where')
            ->with('IDENTITY(ipw.irhpPermitStock) = ?1')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('andWhere')
            ->with('epa.status = ?2')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('andWhere')
            ->with('epa.inScope = 1')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('setParameter')
            ->with(1, $stockId)
            ->once()
            ->andReturnSelf()
            ->shouldReceive('setParameter')
            ->with(2, IrhpInterface::STATUS_UNDER_CONSIDERATION)
            ->once()
            ->andReturnSelf()
            ->shouldReceive('getQuery->getScalarResult')
            ->once()
            ->andReturn($scoringReport);

        $this->assertEquals(
            $scoringReport,
            $this->sut->fetchScoringReport($stockId)
        );
    }

    public function testFetchSelfserveIssuedPermitsSummary(): void
    {
        $rows = [
            ['data1'],
            ['data2'],
            ['data3'],
        ];

        $organisationId = 14;

        $dbalResult = m::mock(DbalResult::class);
        $dbalResult->expects('fetchAll')
            ->andReturn($rows);

        $connection = m::mock(Connection::class);
        $connection->shouldReceive('executeQuery')
            ->with(
                'select ' .
                'concat (l.lic_no, \' / \', ia.id) as applicationRef, ' .
                'ia.id as id, ' .
                'ia.irhp_permit_type_id as typeId, ' .
                'ia.status as statusId, ' .
                'l.id as licenceId, ' .
                'l.lic_no as licNo, ' .
                'srd.description as statusDescription, ' .
                'trd.description as typeDescription, ' .
                'count(ip.id) as validPermitCount ' .
                'from ' .
                'irhp_application ia ' .
                'inner join licence l on ia.licence_id = l.id ' .
                'inner join ref_data srd on ia.status = srd.id ' .
                'left join irhp_permit_application ipa on ipa.irhp_application_id = ia.id ' .
                'left join irhp_permit ip on ip.irhp_permit_application_id = ipa.id ' .
                'inner join irhp_permit_type ipt on ia.irhp_permit_type_id = ipt.id ' .
                'inner join ref_data trd on ipt.name = trd.id ' .
                'where l.`organisation_id` = :filterByColumnValue ' .
                'and ia.status in (:applicationStatus1) ' .
                'and (' .
                '    ia.irhp_permit_type_id in (:permitType1, :permitType2) ' .
                '    or ' .
                '    ip.status in (:permitStatus1, :permitStatus2, :permitStatus3, :permitStatus4, :permitStatus5)' .
                ') ' .
                'group by ia.id ' .
                'order by l.`lic_no`, trd.`description`, ia.`id`',
                [
                    'filterByColumnValue' => $organisationId,
                    'applicationStatus1' => IrhpInterface::STATUS_VALID,
                    'permitType1' => IrhpPermitType::IRHP_PERMIT_TYPE_ID_CERT_ROADWORTHINESS_VEHICLE,
                    'permitType2' => IrhpPermitType::IRHP_PERMIT_TYPE_ID_CERT_ROADWORTHINESS_TRAILER,
                    'permitStatus1' => IrhpPermit::STATUS_PENDING,
                    'permitStatus2' => IrhpPermit::STATUS_AWAITING_PRINTING,
                    'permitStatus3' => IrhpPermit::STATUS_PRINTING,
                    'permitStatus4' => IrhpPermit::STATUS_PRINTED,
                    'permitStatus5' => IrhpPermit::STATUS_ERROR,
                ]
            )
            ->once()
            ->andReturn($dbalResult);

        $this->em->shouldReceive('getConnection')->once()->andReturn($connection);

        $this->assertEquals(
            $rows,
            $this->sut->fetchSelfserveIssuedPermitsSummary($organisationId)
        );
    }

    public function testFetchSelfserveApplicationsSummary(): void
    {
        $rows = [
            ['data1'],
            ['data2'],
            ['data3'],
        ];

        $organisationId = 14;

        $dbalResult = m::mock(DbalResult::class);
        $dbalResult->expects('fetchAll')
            ->andReturn($rows);

        $connection = m::mock(Connection::class);
        $connection->shouldReceive('executeQuery')
            ->with(
                'select ' .
                'concat (l.lic_no, \' / \', ia.id) as applicationRef, ' .
                'sum(ifnull(ipa.permits_required, 0) + ifnull(ipa.required_euro5, 0) + ifnull(ipa.required_euro6, 0) + ifnull(ipa.required_standard, 0) + ifnull(ipa.required_cabotage, 0)) as permitsRequired, ' .
                'ia.id as id, ' .
                'ia.irhp_permit_type_id as typeId, ' .
                'ia.status as statusId, ' .
                'ia.date_received as dateReceived, ' .
                'srd.description as statusDescription, ' .
                'trd.description as typeDescription, ' .
                'ips.period_name_key as periodNameKey, ' .
                'ips.valid_to as stockValidTo, ' .
                'l.id as licenceId ' .
                'from ' .
                'irhp_application ia ' .
                'inner join licence l on ia.licence_id = l.id ' .
                'inner join ref_data srd on ia.status = srd.id ' .
                'left join irhp_permit_application ipa on ipa.irhp_application_id = ia.id ' .
                'inner join irhp_permit_type ipt on ia.irhp_permit_type_id = ipt.id ' .
                'inner join ref_data trd on ipt.name = trd.id ' .
                'left join irhp_permit_window ipw on ipa.irhp_permit_window_id = ipw.id ' .
                'left join irhp_permit_stock ips on ipw.irhp_permit_stock_id = ips.id ' .
                'where l.`organisation_id` = :filterByColumnValue ' .
                'and ia.status in (:applicationStatus1, :applicationStatus2, :applicationStatus3, :applicationStatus4, :applicationStatus5) ' .
                'group by ia.id ' .
                'order by l.`lic_no`, trd.`description`, ia.`id`',
                [
                    'filterByColumnValue' => $organisationId,
                    'applicationStatus1' => IrhpInterface::STATUS_NOT_YET_SUBMITTED,
                    'applicationStatus2' => IrhpInterface::STATUS_UNDER_CONSIDERATION,
                    'applicationStatus3' => IrhpInterface::STATUS_AWAITING_FEE,
                    'applicationStatus4' => IrhpInterface::STATUS_FEE_PAID,
                    'applicationStatus5' => IrhpInterface::STATUS_ISSUING,
                ]
            )
            ->once()
            ->andReturn($dbalResult);

        $this->em->shouldReceive('getConnection')->once()->andReturn($connection);

        $this->assertEquals(
            $rows,
            $this->sut->fetchSelfserveApplicationsSummary($organisationId)
        );
    }

    public function testFetchInternalApplicationsSummary(): void
    {
        $rows = [
            ['data1'],
            ['data2'],
            ['data3'],
        ];

        $licenceId = 14;
        $status = null;

        $dbalResult = m::mock(DbalResult::class);
        $dbalResult->expects('fetchAll')
            ->andReturn($rows);

        $connection = m::mock(Connection::class);
        $connection->shouldReceive('executeQuery')
            ->with(
                'select ' .
                'concat (l.lic_no, \' / \', ia.id) as applicationRef, ' .
                'sum(ifnull(ipa.permits_required, 0) + ifnull(ipa.required_euro5, 0) + ifnull(ipa.required_euro6, 0) + ifnull(ipa.required_standard, 0) + ifnull(ipa.required_cabotage, 0)) as permitsRequired, ' .
                'ia.id as id, ' .
                'ia.irhp_permit_type_id as typeId, ' .
                'ia.status as statusId, ' .
                'ia.date_received as dateReceived, ' .
                'srd.description as statusDescription, ' .
                'trd.description as typeDescription, ' .
                'ips.period_name_key as periodNameKey, ' .
                'ips.valid_to as stockValidTo, ' .
                'l.id as licenceId ' .
                'from ' .
                'irhp_application ia ' .
                'inner join licence l on ia.licence_id = l.id ' .
                'inner join ref_data srd on ia.status = srd.id ' .
                'left join irhp_permit_application ipa on ipa.irhp_application_id = ia.id ' .
                'inner join irhp_permit_type ipt on ia.irhp_permit_type_id = ipt.id ' .
                'inner join ref_data trd on ipt.name = trd.id ' .
                'left join irhp_permit_window ipw on ipa.irhp_permit_window_id = ipw.id ' .
                'left join irhp_permit_stock ips on ipw.irhp_permit_stock_id = ips.id ' .
                'where l.`id` = :filterByColumnValue ' .
                'and ia.status in (:applicationStatus1, :applicationStatus2, :applicationStatus3, :applicationStatus4, :applicationStatus5, :applicationStatus6, :applicationStatus7, :applicationStatus8, :applicationStatus9, :applicationStatus10, :applicationStatus11, :applicationStatus12) ' .
                'group by ia.id ' .
                'order by ia.`id` DESC',
                [
                    'filterByColumnValue' => $licenceId,
                    'applicationStatus1' => IrhpInterface::STATUS_CANCELLED,
                    'applicationStatus2' => IrhpInterface::STATUS_NOT_YET_SUBMITTED,
                    'applicationStatus3' => IrhpInterface::STATUS_UNDER_CONSIDERATION,
                    'applicationStatus4' => IrhpInterface::STATUS_WITHDRAWN,
                    'applicationStatus5' => IrhpInterface::STATUS_AWAITING_FEE,
                    'applicationStatus6' => IrhpInterface::STATUS_FEE_PAID,
                    'applicationStatus7' => IrhpInterface::STATUS_UNSUCCESSFUL,
                    'applicationStatus8' => IrhpInterface::STATUS_ISSUING,
                    'applicationStatus9' => IrhpInterface::STATUS_VALID,
                    'applicationStatus10' => IrhpInterface::STATUS_EXPIRED,
                    'applicationStatus11' => IrhpInterface::STATUS_TERMINATED,
                    'applicationStatus12' => IrhpInterface::STATUS_DECLINED,
                ]
            )
            ->once()
            ->andReturn($dbalResult);

        $this->em->shouldReceive('getConnection')->once()->andReturn($connection);

        $this->assertEquals(
            $rows,
            $this->sut->fetchInternalApplicationsSummary($licenceId, $status)
        );
    }

    public function testFetchInternalApplicationsSummaryWithStatus(): void
    {
        $rows = [
            ['data1'],
            ['data2'],
            ['data3'],
        ];

        $licenceId = 14;
        $status = IrhpInterface::STATUS_NOT_YET_SUBMITTED;

        $dbalResult = m::mock(DbalResult::class);
        $dbalResult->expects('fetchAll')
            ->andReturn($rows);

        $connection = m::mock(Connection::class);
        $connection->shouldReceive('executeQuery')
            ->with(
                'select ' .
                'concat (l.lic_no, \' / \', ia.id) as applicationRef, ' .
                'sum(ifnull(ipa.permits_required, 0) + ifnull(ipa.required_euro5, 0) + ifnull(ipa.required_euro6, 0) + ifnull(ipa.required_standard, 0) + ifnull(ipa.required_cabotage, 0)) as permitsRequired, ' .
                'ia.id as id, ' .
                'ia.irhp_permit_type_id as typeId, ' .
                'ia.status as statusId, ' .
                'ia.date_received as dateReceived, ' .
                'srd.description as statusDescription, ' .
                'trd.description as typeDescription, ' .
                'ips.period_name_key as periodNameKey, ' .
                'ips.valid_to as stockValidTo, ' .
                'l.id as licenceId ' .
                'from ' .
                'irhp_application ia ' .
                'inner join licence l on ia.licence_id = l.id ' .
                'inner join ref_data srd on ia.status = srd.id ' .
                'left join irhp_permit_application ipa on ipa.irhp_application_id = ia.id ' .
                'inner join irhp_permit_type ipt on ia.irhp_permit_type_id = ipt.id ' .
                'inner join ref_data trd on ipt.name = trd.id ' .
                'left join irhp_permit_window ipw on ipa.irhp_permit_window_id = ipw.id ' .
                'left join irhp_permit_stock ips on ipw.irhp_permit_stock_id = ips.id ' .
                'where l.`id` = :filterByColumnValue ' .
                'and ia.status in (:applicationStatus1) ' .
                'group by ia.id ' .
                'order by ia.`id` DESC',
                [
                    'filterByColumnValue' => $licenceId,
                    'applicationStatus1' => $status,
                ]
            )
            ->once()
            ->andReturn($dbalResult);

        $this->em->shouldReceive('getConnection')->once()->andReturn($connection);

        $this->assertEquals(
            $rows,
            $this->sut->fetchInternalApplicationsSummary($licenceId, $status)
        );
    }

    public function testFetchNotYetSubmittedBilateralApplications(): void
    {
        $qb = $this->createMockQb('BLAH');

        $this->mockCreateQueryBuilder($qb);

        $qb->shouldReceive('getQuery')->andReturn(
            m::mock()->shouldReceive('execute')
                ->shouldReceive('getResult')
                ->andReturn(['RESULTS'])
                ->getMock()
        );

        $this->assertEquals(
            ['RESULTS'],
            $this->sut->fetchNotYetSubmittedBilateralApplications()
        );

        $expectedQuery = 'BLAH '
            . 'AND ia.status = [[permit_app_nys]] AND '
            . 'ia.irhpPermitType = [[4]]';

        $this->assertEquals($expectedQuery, $this->query);
    }
}
