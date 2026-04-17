<?php

declare(strict_types=1);

/**
 * Goods Disc test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\ORM\NoResultException;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Repository\GoodsDisc as GoodsDiscRepo;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Doctrine\DBAL\Connection;

/**
 * Goods Disc test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class GoodsDiscTest extends RepositoryTestCase
{
    /**
     * @var m\MockInterface|GoodsDiscRepo
     */
    protected $sut;

    protected $activeStatuses;

    public function setUp(): void
    {
        $this->activeStatuses = [
            LicenceEntity::LICENCE_STATUS_UNDER_CONSIDERATION,
            LicenceEntity::LICENCE_STATUS_GRANTED,
            LicenceEntity::LICENCE_STATUS_VALID,
            LicenceEntity::LICENCE_STATUS_CURTAILED,
            LicenceEntity::LICENCE_STATUS_SUSPENDED
        ];
        $this->setUpSut(GoodsDiscRepo::class);
    }

    public function testFetchDiscsToPrintNi(): void
    {
        $licenceType = 'ltyp_r';

        $mockQb = m::mock(QueryBuilder::class);
        $mockQb->shouldReceive('expr->eq')->with('lvlta.isNi', 1)->once()->andReturn('condition1');
        $mockQb->shouldReceive('expr->eq')->with('gd.isInterim', 1)->once()->andReturn('condition2');
        $mockQb->shouldReceive('expr->eq')
            ->with('lvalt.id', ':applicationLicenceType')->once()->andReturn('condition3');
        $mockQb->shouldReceive('expr->andX')
            ->with('condition1', 'condition2', 'condition3')->once()->andReturn('conditionAndX1');
        $mockQb->shouldReceive('setMaxResults')->with(1)->once()->andReturn('condition4');
        $mockQb->shouldReceive('expr->eq')->with('lvlta.isNi', 1)->once()->andReturn('condition5');
        $mockQb->shouldReceive('expr->eq')->with('gd.isInterim', 0)->once()->andReturn('condition6');
        $mockQb->shouldReceive('expr->eq')->with('lvllt.id', ':licenceLicenceType')->once()->andReturn('condition7');
        $mockQb->shouldReceive('expr->andX')
            ->with('condition5', 'condition6', 'condition7')->once()->andReturn('conditionAndX2');

        $mockQb->shouldReceive('expr->orX')->with('conditionAndX1', 'conditionAndX2')->once()->andReturn('and');
        $mockQb->shouldReceive('andWhere')->with('and')->once()->andReturnSelf();

        $mockQb->shouldReceive('expr->isNull')->with('gd.ceasedDate')->once()->andReturn('noCeasedDateCond');
        $mockQb->shouldReceive('expr->isNull')->with('gd.issuedDate')->once()->andReturn('noIssuedDateCond');
        $mockQb->shouldReceive('expr->isNull')->with('lv.removalDate')->once()->andReturn('noRemovalDateCond');
        $mockQb->shouldReceive('andWhere')->with('noCeasedDateCond')->once()->andReturnSelf();
        $mockQb->shouldReceive('andWhere')->with('noIssuedDateCond')->once()->andReturnSelf();
        $mockQb->shouldReceive('andWhere')->with('noRemovalDateCond')->once()->andReturnSelf();

        $mockQb->shouldReceive('setParameter')
            ->with('applicationLicenceType', $licenceType)
            ->once()
            ->andReturnSelf();

        $mockQb->shouldReceive('setParameter')
            ->with('licenceLicenceType', $licenceType)
            ->once()
            ->andReturnSelf();

        $mockQb->shouldReceive('expr->in')->with('lvl.status', ':activeStatuses')->once()->andReturn('activeStatuses');
        $mockQb->shouldReceive('andWhere')->with('activeStatuses')->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')
            ->with('activeStatuses', $this->activeStatuses)
            ->once()
            ->andReturnSelf();

        $this->queryBuilder->shouldReceive('modifyQuery')->with($mockQb)->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('gd.licenceVehicle', 'lv')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('lv.licence', 'lvl')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('lvl.goodsOrPsv', 'lvlgp')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('lvl.licenceType', 'lvllt')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('lvl.trafficArea', 'lvlta')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('lv.vehicle', 'lvv')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('lv.application', 'lva')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('lva.licenceType', 'lvalt')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('lva.goodsOrPsv', 'lvagp')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('order')->with('lvl.licNo', 'ASC')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('order')->with('gd.id', 'ASC')->once()->andReturnSelf();

        $this->em->shouldReceive('getRepository->createQueryBuilder')->with('gd')->once()->andReturn($mockQb);
        $mockQb->shouldReceive('getQuery->getResult')->once()->andReturn(['result']);

        $maxResults = 1;
        $this->sut->fetchDiscsToPrint('Y', $licenceType, $maxResults);
    }

    public function testFetchDiscsToPrint(): void
    {
        $licenceType = 'ltyp_r';

        $mockQb = m::mock(QueryBuilder::class);
        $mockQb->shouldReceive('expr->eq')->with('lvlta.isNi', 0)->once()->andReturn('condition1');
        $mockQb->shouldReceive('expr->eq')->with('gd.isInterim', 1)->once()->andReturn('condition2');
        $mockQb->shouldReceive('expr->eq')
            ->with('lvagp.id', ':operatorType')->once()->andReturn('condition3');
        $mockQb->shouldReceive('expr->eq')
            ->with('lvalt.id', ':applicationLicenceType')->once()->andReturn('condition4');
        $mockQb->shouldReceive('setMaxResults')->with(1)->once()->andReturn('conditionAnd4');
        $mockQb->shouldReceive('expr->andX')
            ->with('condition1', 'condition2', 'condition3', 'condition4')
            ->once()
            ->andReturn('conditionAndX1');

        $mockQb->shouldReceive('expr->eq')->with('lvlta.isNi', 0)->once()->andReturn('condition6');
        $mockQb->shouldReceive('expr->eq')->with('gd.isInterim', 0)->once()->andReturn('condition7');
        $mockQb->shouldReceive('expr->eq')
            ->with('lvlgp.id', ':operatorType1')->once()->andReturn('condition8');
        $mockQb->shouldReceive('expr->eq')->with('lvllt.id', ':licenceLicenceType')->once()->andReturn('condition9');
        $mockQb->shouldReceive('expr->andX')
            ->with('condition6', 'condition7', 'condition8', 'condition9')
            ->once()
            ->andReturn('conditionAndX2');

        $mockQb->shouldReceive('expr->orX')->with('conditionAndX1', 'conditionAndX2')->once()->andReturn('and');
        $mockQb->shouldReceive('andWhere')->with('and')->once()->andReturnSelf();

        $mockQb->shouldReceive('expr->isNull')->with('gd.ceasedDate')->once()->andReturn('noCeasedDateCond');
        $mockQb->shouldReceive('expr->isNull')->with('gd.issuedDate')->once()->andReturn('noIssuedDateCond');
        $mockQb->shouldReceive('expr->isNull')->with('lv.removalDate')->once()->andReturn('noRemovalDateCond');
        $mockQb->shouldReceive('andWhere')->with('noCeasedDateCond')->once()->andReturnSelf();
        $mockQb->shouldReceive('andWhere')->with('noIssuedDateCond')->once()->andReturnSelf();
        $mockQb->shouldReceive('andWhere')->with('noRemovalDateCond')->once()->andReturnSelf();

        $mockQb->shouldReceive('setParameter')
            ->with('operatorType', LicenceEntity::LICENCE_CATEGORY_GOODS_VEHICLE)
            ->once()
            ->andReturnSelf();
        $mockQb->shouldReceive('setParameter')
            ->with('applicationLicenceType', $licenceType)
            ->once()
            ->andReturnSelf();
        $mockQb->shouldReceive('setParameter')
            ->with('operatorType1', LicenceEntity::LICENCE_CATEGORY_GOODS_VEHICLE)
            ->once()
            ->andReturnSelf();
        $mockQb->shouldReceive('setParameter')
            ->with('licenceLicenceType', $licenceType)
            ->once()
            ->andReturnSelf();

        $mockQb->shouldReceive('expr->in')->with('lvl.status', ':activeStatuses')->once()->andReturn('activeStatuses');
        $mockQb->shouldReceive('andWhere')->with('activeStatuses')->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')
            ->with('activeStatuses', $this->activeStatuses)
            ->once()
            ->andReturnSelf();

        $this->queryBuilder->shouldReceive('modifyQuery')->with($mockQb)->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('gd.licenceVehicle', 'lv')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('lv.licence', 'lvl')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('lvl.goodsOrPsv', 'lvlgp')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('lvl.licenceType', 'lvllt')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('lvl.trafficArea', 'lvlta')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('lv.vehicle', 'lvv')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('lv.application', 'lva')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('lva.licenceType', 'lvalt')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('lva.goodsOrPsv', 'lvagp')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('order')->with('lvl.licNo', 'ASC')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('order')->with('gd.id', 'ASC')->once()->andReturnSelf();

        $this->em->shouldReceive('getRepository->createQueryBuilder')->with('gd')->once()->andReturn($mockQb);
        $mockQb->shouldReceive('getQuery->getResult')->once()->andReturn(['result']);

        $maxResults = 1;
        $this->sut->fetchDiscsToPrint('N', $licenceType, $maxResults);
    }

    public function testSetPrintingOn(): void
    {
        $discs = [1, 2];
        $sut = m::mock(GoodsDiscRepo::class)->makePartial()->shouldAllowMockingProtectedMethods();
        $sut->shouldReceive('setIsPrinting')
            ->with(1, $discs)
            ->once()
            ->getMock();

        $this->assertNull($sut->setIsPrintingOn($discs));
    }

    public function testSetPrintingOff(): void
    {
        $discs = [1, 2];
        $sut = m::mock(GoodsDiscRepo::class)->makePartial()->shouldAllowMockingProtectedMethods();
        $sut->shouldReceive('setIsPrinting')
            ->with(0, $discs)
            ->once()
            ->getMock();

        $this->assertNull($sut->setIsPrintingOff($discs));
    }

    public function testSetPrinting(): void
    {
        $this->expectQueryWithData(
            'Discs\GoodsDiscsSetIsPrinting',
            ['isPrinting' => 1, 'ids' => [1, 2]],
            ['isPrinting' => \PDO::PARAM_INT, 'ids' => Connection::PARAM_INT_ARRAY]
        );

        $this->sut->setIsPrintingOn([1, 2]);
    }

    public function testSetIsPrintingOffAndAssignNumbers(): void
    {
        $query = m::mock();
        $query->shouldReceive('execute')->once()->with(['id' => 1, 'discNo' => 634]);
        $query->shouldReceive('execute')->once()->with(['id' => 32, 'discNo' => 635]);
        $query->shouldReceive('execute')->once()->with(['id' => 4, 'discNo' => 636]);

        $this->dbQueryService->shouldReceive('get')
            ->with('Discs\GoodsDiscsSetIsPrintingOffAndDiscNo')
            ->andReturn($query);

        $this->sut->setIsPrintingOffAndAssignNumbers([1, 32, 4], 634);
    }

    public function testCeaseDiscsForLicence(): void
    {
        $licenceId = 123;

        $stmt = m::mock();
        $stmt->shouldReceive('rowCount')->with()->once()->andReturn(564);

        $this->expectQueryWithData('LicenceVehicle\CeaseDiscsForLicence', ['licence' => 123], [], $stmt);

        $this->assertSame(564, $this->sut->ceaseDiscsForLicence($licenceId));
    }

    public function testCeaseDiscsForLicenceVehicle(): void
    {
        $lvId = 123;
        $rowCount = 564;

        $stmt = m::mock();
        $stmt->shouldReceive('rowCount')->with()->once()->andReturn($rowCount);

        $this->expectQueryWithData(
            'LicenceVehicle\CeaseDiscsForLicenceVehicle',
            ['licenceVehicle' => $lvId],
            [],
            $stmt
        );

        $this->assertSame($rowCount, $this->sut->ceaseDiscsForLicenceVehicle($lvId));
    }

    public function testCeaseDiscsForApplication(): void
    {
        $stmt = m::mock();
        $stmt->shouldReceive('rowCount')->with()->once()->andReturn(123);

        $this->expectQueryWithData('LicenceVehicle\CeaseDiscsForApplication', ['application' => 45], [], $stmt);

        $this->assertSame(123, $this->sut->ceaseDiscsForApplication(45));
    }

    public function testFetchDiscsToPrintMin(): void
    {
        $licenceType = 'ltyp_r';

        $mockQb = m::mock(QueryBuilder::class);
        $mockQb->shouldReceive('expr->eq')->with('lvlta.isNi', 0)->once()->andReturn('condition1');
        $mockQb->shouldReceive('expr->eq')->with('gd.isInterim', 1)->once()->andReturn('condition2');
        $mockQb->shouldReceive('expr->eq')
            ->with('lvagp.id', ':operatorType')->once()->andReturn('condition3');
        $mockQb->shouldReceive('expr->eq')
            ->with('lvalt.id', ':applicationLicenceType')->once()->andReturn('condition4');
        $mockQb->shouldReceive('expr->andX')
            ->with('condition1', 'condition2', 'condition3', 'condition4')
            ->once()
            ->andReturn('conditionAndX1');

        $mockQb->shouldReceive('expr->eq')->with('lvlta.isNi', 0)->once()->andReturn('condition6');
        $mockQb->shouldReceive('expr->eq')->with('gd.isInterim', 0)->once()->andReturn('condition7');
        $mockQb->shouldReceive('expr->eq')
            ->with('lvlgp.id', ':operatorType1')->once()->andReturn('condition8');
        $mockQb->shouldReceive('expr->eq')->with('lvllt.id', ':licenceLicenceType')->once()->andReturn('condition9');
        $mockQb->shouldReceive('expr->andX')
            ->with('condition6', 'condition7', 'condition8', 'condition9')
            ->once()
            ->andReturn('conditionAndX2');

        $mockQb->shouldReceive('expr->orX')->with('conditionAndX1', 'conditionAndX2')->once()->andReturn('and');
        $mockQb->shouldReceive('andWhere')->with('and')->once()->andReturnSelf();

        $mockQb->shouldReceive('expr->isNull')->with('gd.ceasedDate')->once()->andReturn('noCeasedDateCond');
        $mockQb->shouldReceive('expr->isNull')->with('gd.issuedDate')->once()->andReturn('noIssuedDateCond');
        $mockQb->shouldReceive('expr->isNull')->with('lv.removalDate')->once()->andReturn('noRemovalDateCond');
        $mockQb->shouldReceive('andWhere')->with('noCeasedDateCond')->once()->andReturnSelf();
        $mockQb->shouldReceive('andWhere')->with('noIssuedDateCond')->once()->andReturnSelf();
        $mockQb->shouldReceive('andWhere')->with('noRemovalDateCond')->once()->andReturnSelf();

        $mockQb->shouldReceive('setParameter')
            ->with('operatorType', LicenceEntity::LICENCE_CATEGORY_GOODS_VEHICLE)
            ->once()
            ->andReturnSelf();
        $mockQb->shouldReceive('setParameter')
            ->with('applicationLicenceType', $licenceType)
            ->once()
            ->andReturnSelf();
        $mockQb->shouldReceive('setParameter')
            ->with('operatorType1', LicenceEntity::LICENCE_CATEGORY_GOODS_VEHICLE)
            ->once()
            ->andReturnSelf();
        $mockQb->shouldReceive('setParameter')
            ->with('licenceLicenceType', $licenceType)
            ->once()
            ->andReturnSelf();

        $mockQb->shouldReceive('expr->in')->with('lvl.status', ':activeStatuses')->once()->andReturn('activeStatuses');
        $mockQb->shouldReceive('andWhere')->with('activeStatuses')->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')
            ->with('activeStatuses', $this->activeStatuses)
            ->once()
            ->andReturnSelf();

        $mockQb->shouldReceive('leftJoin')->with('gd.licenceVehicle', 'lv')->once()->andReturnSelf();
        $mockQb->shouldReceive('leftJoin')->with('lv.licence', 'lvl')->once()->andReturnSelf();
        $mockQb->shouldReceive('leftJoin')->with('lvl.goodsOrPsv', 'lvlgp')->once()->andReturnSelf();
        $mockQb->shouldReceive('leftJoin')->with('lvl.licenceType', 'lvllt')->once()->andReturnSelf();
        $mockQb->shouldReceive('leftJoin')->with('lvl.trafficArea', 'lvlta')->once()->andReturnSelf();
        $mockQb->shouldReceive('leftJoin')->with('lv.vehicle', 'lvv')->once()->andReturnSelf();
        $mockQb->shouldReceive('leftJoin')->with('lv.application', 'lva')->once()->andReturnSelf();
        $mockQb->shouldReceive('leftJoin')->with('lva.licenceType', 'lvalt')->once()->andReturnSelf();
        $mockQb->shouldReceive('leftJoin')->with('lva.goodsOrPsv', 'lvagp')->once()->andReturnSelf();

        $this->em->shouldReceive('getRepository->createQueryBuilder')->with('gd')->once()->andReturn($mockQb);
        $mockQb->shouldReceive('getQuery->getResult')->once()->andReturn(['result']);

        $this->sut->fetchDiscsToPrintMin('N', $licenceType);
    }

    public function testUpdateExistingGoodsDiscs(): void
    {
        $application = m::mock(\Dvsa\Olcs\Api\Entity\Application\Application::class);
        $application->shouldReceive('getId')->andReturn(1102);
        $application->shouldReceive('getLicence->getId')->andReturn(321);

        $this->expectQueryWithData('Discs\CeaseGoodsDiscsForApplication', ['application' => 1102, 'licence' => 321]);
        $this->expectQueryWithData('Discs\CreateGoodsDiscs', ['application' => 1102, 'licence' => 321, 'isCopy' => 0]);

        $this->sut->updateExistingGoodsDiscs($application);
    }

    public function testCreateDiscsForLicence(): void
    {
        $stmt = m::mock();
        $stmt->shouldReceive('rowCount')->with()->once()->andReturn(83);

        $this->expectQueryWithData('LicenceVehicle\CreateDiscsForLicence', ['licence' => 1502], [], $stmt);

        $this->assertSame(83, $this->sut->createDiscsForLicence(1502));
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

        $expectedQuery = '{QUERY} SELECT count(gd) INNER JOIN gd.licenceVehicle lv AND lv.licence = [[' . $licenceId . ']] AND gd.ceasedDate IS NULL LIMIT 1';

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
