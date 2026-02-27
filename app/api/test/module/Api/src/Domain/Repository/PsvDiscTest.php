<?php

declare(strict_types=1);

/**
 * Psv Disc test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\ORM\NoResultException;
use Dvsa\Olcs\Api\Entity\System\DiscSequence;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Repository\PsvDisc as PsvDiscRepo;
use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea as TrafficAreaEntity;
use Doctrine\DBAL\Connection;

/**
 * Psv Disc test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class PsvDiscTest extends RepositoryTestCase
{
    public $activeStatuses;
    public function setUp(): void
    {
        $this->activeStatuses = [
            LicenceEntity::LICENCE_STATUS_UNDER_CONSIDERATION,
            LicenceEntity::LICENCE_STATUS_GRANTED,
            LicenceEntity::LICENCE_STATUS_VALID,
            LicenceEntity::LICENCE_STATUS_CURTAILED,
            LicenceEntity::LICENCE_STATUS_SUSPENDED
        ];
        $this->setUpSut(PsvDiscRepo::class);
    }

    public function testFetchDiscsToPrint(): void
    {
        $licenceType = 'ltyp_r';
        $maxPages = 1;

        $mockQb = m::mock(QueryBuilder::class);
        $mockQb->shouldReceive('expr->eq')->with('lta.isNi', 0)->once()->andReturn('condition1');
        $mockQb->shouldReceive('expr->eq')->with('llt.id', ':licenceType')->once()->andReturn('condition2');
        $mockQb->shouldReceive('expr->neq')
            ->with('lta.id', ':licenceTrafficAreaId')->once()->andReturn('condition3');
        $mockQb->shouldReceive('expr->eq')
            ->with('lgp.id', ':goodsOrPsv')->once()->andReturn('condition4');
        $mockQb->shouldReceive('expr->andX')
            ->with('condition1', 'condition2', 'condition3', 'condition4')->once()->andReturn('conditionAndX');

        $mockQb->shouldReceive('andWhere')->with('conditionAndX')->once()->andReturnSelf();
        $mockQb->shouldReceive('setMaxResults')->with(6)->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')
            ->with('licenceType', $licenceType)
            ->once()
            ->andReturnSelf();
        $mockQb->shouldReceive('setParameter')
            ->with('licenceTrafficAreaId', TrafficAreaEntity::NORTHERN_IRELAND_TRAFFIC_AREA_CODE)
            ->once()
            ->andReturnSelf();
        $mockQb->shouldReceive('setParameter')
            ->with('goodsOrPsv', LicenceEntity::LICENCE_CATEGORY_PSV)
            ->once()
            ->andReturnSelf();

        $mockQb->shouldReceive('expr->isNull')->with('psv.ceasedDate')->once()->andReturn('noCeasedDateCond');
        $mockQb->shouldReceive('expr->isNull')->with('psv.issuedDate')->once()->andReturn('noIssuedDateCond');
        $mockQb->shouldReceive('andWhere')->with('noCeasedDateCond')->once()->andReturnSelf();
        $mockQb->shouldReceive('andWhere')->with('noIssuedDateCond')->once()->andReturnSelf();
        $mockQb->shouldReceive('expr->in')->with('l.status', ':activeStatuses')->once()->andReturn('activeStatuses');
        $mockQb->shouldReceive('andWhere')->with('activeStatuses')->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')
            ->with('activeStatuses', $this->activeStatuses)
            ->once()
            ->andReturnSelf();

        $this->queryBuilder->shouldReceive('modifyQuery')->with($mockQb)->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('withRefdata')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('licence', 'l')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('l.trafficArea', 'lta')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('l.licenceType', 'llt')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('l.goodsOrPsv', 'lgp')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('order')->with('l.licNo', 'ASC')->once()->andReturnSelf();

        $this->em->shouldReceive('getRepository->createQueryBuilder')->with('psv')->once()->andReturn($mockQb);
        $mockQb->shouldReceive('getQuery->getResult')->once()->andReturn(['result']);

        $this->sut->fetchDiscsToPrint($licenceType, $maxPages * DiscSequence::DISCS_ON_PAGE);
    }

    public function testSetPrintingOn(): void
    {
        $discs = [1, 2];
        $sut = m::mock(PsvDiscRepo::class)->makePartial()->shouldAllowMockingProtectedMethods();
        $sut->shouldReceive('setIsPrinting')
            ->with(1, $discs)
            ->once()
            ->getMock();

        $this->assertNull($sut->setIsPrintingOn($discs));
    }

    public function testSetPrintingOff(): void
    {
        $discs = [1, 2];
        $sut = m::mock(PsvDiscRepo::class)->makePartial()->shouldAllowMockingProtectedMethods();
        $sut->shouldReceive('setIsPrinting')
            ->with(0, $discs)
            ->once()
            ->getMock();

        $this->assertNull($sut->setIsPrintingOff($discs));
    }

    public function testSetPrinting(): void
    {
        $this->expectQueryWithData(
            'Discs\PsvDiscsSetIsPrinting',
            ['isPrinting' => 1, 'ids' => [1, 2]],
            ['isPrinting' => \PDO::PARAM_INT, 'ids' => Connection::PARAM_INT_ARRAY]
        );

        $this->sut->setIsPrintingOn([1, 2]);
    }

    public function testSetIsPrintingOffAndAssignNumbers(): void
    {
        $query = m::mock();
        $query->shouldReceive('execute')->once()->with(['id' => 121, 'discNo' => 99]);
        $query->shouldReceive('execute')->once()->with(['id' => 12, 'discNo' => 100]);
        $query->shouldReceive('execute')->once()->with(['id' => 54, 'discNo' => 101]);

        $this->dbQueryService->shouldReceive('get')
            ->with('Discs\PsvDiscsSetIsPrintingOffAndDiscNo')
            ->andReturn($query);

        $this->sut->setIsPrintingOffAndAssignNumbers([121, 12, 54], 99);
    }

    public function testApplyListFiltersIncludeCeasedFalse(): void
    {
        $sut = m::mock(PsvDiscRepo::class)->makePartial()->shouldAllowMockingProtectedMethods();
        $qb = m::mock(\Doctrine\ORM\QueryBuilder::class);
        $query = \Dvsa\Olcs\Transfer\Query\Licence\PsvDiscs::create(['includeCeased' => false, 'id' => 12]);

        $sut->shouldReceive('createQueryBuilder')->with()->once()->andReturn($qb);
        $sut->shouldReceive('buildDefaultListQuery')->with($qb, $query)->once();
        $sut->shouldReceive('applyListJoins')->with($qb)->once();
        $sut->shouldReceive('fetchPaginatedList')->with($qb, \Doctrine\ORM\Query::HYDRATE_ARRAY)->once();

        $qb->shouldReceive('expr->isNull')->with('psv.ceasedDate')->once()->andReturn('QUERY1');
        $qb->shouldReceive('andWhere')->with('QUERY1')->once();

        $qb->shouldReceive('expr->eq')->with('psv.licence', ':licence')->once()->andReturn('QUERY2');
        $qb->shouldReceive('andWhere')->with('QUERY2')->once()->andReturnSelf();
        $qb->shouldReceive('setParameter')->with('licence', 12)->once()->andReturnSelf();
        $qb->shouldReceive('addSelect')->with('psv.discNo+0 as HIDDEN intDiscNo')->once()->andReturnSelf();
        $qb->shouldReceive('orderBy')->with('intDiscNo', 'ASC')->once()->andReturnSelf();

        $sut->fetchList($query);
    }

    public function testApplyListFiltersIncludeCeasedTrue(): void
    {
        $sut = m::mock(PsvDiscRepo::class)->makePartial()->shouldAllowMockingProtectedMethods();
        $qb = m::mock(\Doctrine\ORM\QueryBuilder::class);
        $query = \Dvsa\Olcs\Transfer\Query\Licence\PsvDiscs::create(['includeCeased' => true, 'id' => 12]);

        $sut->shouldReceive('createQueryBuilder')->with()->once()->andReturn($qb);
        $sut->shouldReceive('buildDefaultListQuery')->with($qb, $query)->once();
        $sut->shouldReceive('applyListJoins')->with($qb)->once();
        $sut->shouldReceive('fetchPaginatedList')->with($qb, \Doctrine\ORM\Query::HYDRATE_ARRAY)->once();

        $qb->shouldReceive('expr->eq')->with('psv.licence', ':licence')->once()->andReturn('QUERY2');
        $qb->shouldReceive('andWhere')->with('QUERY2')->once()->andReturnSelf();
        $qb->shouldReceive('setParameter')->with('licence', 12)->once()->andReturnSelf();
        $qb->shouldReceive('addSelect')->with('psv.discNo+0 as HIDDEN intDiscNo')->once()->andReturnSelf();
        $qb->shouldReceive('orderBy')->with('intDiscNo', 'ASC')->once()->andReturnSelf();

        $sut->fetchList($query);
    }

    public function testCeaseDiscsForLicence(): void
    {
        $licenceId = 123;
        $stmt = m::mock();
        $stmt->shouldReceive('rowCount')->with()->once()->andReturn(652);

        $this->expectQueryWithData('Discs\CeaseDiscsForLicence', ['licence' => 123], [], $stmt);

        $this->assertSame(652, $this->sut->ceaseDiscsForLicence($licenceId));
    }

    public function testFetchDiscsToPrintMin(): void
    {
        $licenceType = 'ltyp_r';

        $mockQb = m::mock(QueryBuilder::class);
        $mockQb->shouldReceive('expr->eq')->with('lta.isNi', 0)->once()->andReturn('condition1');
        $mockQb->shouldReceive('expr->eq')->with('llt.id', ':licenceType')->once()->andReturn('condition2');
        $mockQb->shouldReceive('expr->neq')
            ->with('lta.id', ':licenceTrafficAreaId')->once()->andReturn('condition3');
        $mockQb->shouldReceive('expr->eq')
            ->with('lgp.id', ':goodsOrPsv')->once()->andReturn('condition4');
        $mockQb->shouldReceive('expr->andX')
            ->with('condition1', 'condition2', 'condition3', 'condition4')->once()->andReturn('conditionAndX');

        $mockQb->shouldReceive('andWhere')->with('conditionAndX')->once()->andReturnSelf();

        $mockQb->shouldReceive('setParameter')
            ->with('licenceType', $licenceType)
            ->once()
            ->andReturnSelf();
        $mockQb->shouldReceive('setParameter')
            ->with('licenceTrafficAreaId', TrafficAreaEntity::NORTHERN_IRELAND_TRAFFIC_AREA_CODE)
            ->once()
            ->andReturnSelf();
        $mockQb->shouldReceive('setParameter')
            ->with('goodsOrPsv', LicenceEntity::LICENCE_CATEGORY_PSV)
            ->once()
            ->andReturnSelf();

        $mockQb->shouldReceive('expr->isNull')->with('psv.ceasedDate')->once()->andReturn('noCeasedDateCond');
        $mockQb->shouldReceive('expr->isNull')->with('psv.issuedDate')->once()->andReturn('noIssuedDateCond');
        $mockQb->shouldReceive('andWhere')->with('noCeasedDateCond')->once()->andReturnSelf();
        $mockQb->shouldReceive('andWhere')->with('noIssuedDateCond')->once()->andReturnSelf();
        $mockQb->shouldReceive('expr->in')->with('l.status', ':activeStatuses')->once()->andReturn('activeStatuses');
        $mockQb->shouldReceive('andWhere')->with('activeStatuses')->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')
            ->with('activeStatuses', $this->activeStatuses)
            ->once()
            ->andReturnSelf();

        $mockQb->shouldReceive('leftJoin')->with('psv.licence', 'l')->once()->andReturnSelf();
        $mockQb->shouldReceive('leftJoin')->with('l.trafficArea', 'lta')->once()->andReturnSelf();
        $mockQb->shouldReceive('leftJoin')->with('l.licenceType', 'llt')->once()->andReturnSelf();
        $mockQb->shouldReceive('leftJoin')->with('l.goodsOrPsv', 'lgp')->once()->andReturnSelf();

        $this->em->shouldReceive('getRepository->createQueryBuilder')->with('psv')->once()->andReturn($mockQb);
        $mockQb->shouldReceive('getQuery->getResult')->once()->andReturn(['result']);

        $this->sut->fetchDiscsToPrintMin($licenceType);
    }

    public function testCreatePsvDiscs(): void
    {
        $query = m::mock();
        $query->shouldReceive('executeInsert')->once()->with(321, 99, true);

        $this->dbQueryService->shouldReceive('get')->with('Discs\CreatePsvDiscs')->andReturn($query);

        $this->sut->createPsvDiscs(321, 99, true);
    }

    public function testCountForLicence(): void
    {
        $licenceId = 1;

        $qb = $this->createMockQb('{QUERY}');

        $qb->shouldReceive('getQuery->getSingleScalarResult')
            ->once()
            ->andReturn(1);

        $this->mockCreateQueryBuilder($qb);

        $this->sut->countForLicence($licenceId);

        $expectedQuery = '{QUERY} SELECT count(psv) AND psv.licence = [[' . $licenceId . ']] AND psv.ceasedDate IS NULL GROUP BY psv.licence LIMIT 1';

        self::assertEquals($expectedQuery, $this->query);
    }

    public function testCountForLicenceNoResult(): void
    {
        $licenceId = 1;

        $qb = $this->createMockQb();
        $exception = new NoResultException();

        $qb->shouldReceive('getQuery')
            ->once()
            ->andReturn($qb)
            ->getMock();
        $qb->shouldReceive('getSingleScalarResult')
            ->once()
            ->andThrow($exception);

        $this->mockCreateQueryBuilder($qb);

        $this->assertSame(['discCount' => 0], $this->sut->countForLicence($licenceId));
    }

    public function testCountForLicenceException(): void
    {
        $licenceId = 1;

        $qb = $this->createMockQb();

        $ex = new \Exception('testException');
        $qb->shouldReceive('getQuery')
            ->once()
            ->andReturn($qb)
            ->getMock();
        $qb->shouldReceive('getSingleScalarResult')
            ->once()
            ->andThrow($ex);

        $this->mockCreateQueryBuilder($qb);

        $this->expectExceptionMessage('testException');

        $this->sut->countForLicence($licenceId);
    }
}
