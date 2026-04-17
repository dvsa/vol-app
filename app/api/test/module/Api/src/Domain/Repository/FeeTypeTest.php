<?php

declare(strict_types=1);

/**
 * Fee Type repository test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Api\Domain\Repository\FeeType as Repo;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea as TrafficAreaEntity;
use Dvsa\Olcs\Transfer\Query\Fee\FeeTypeList as FeeTypeListQry;
use Dvsa\Olcs\Transfer\Query\FeeType\GetList as AdminFeeTypeListQry;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Entity\Irfo\IrfoGvPermit;
use Dvsa\Olcs\Api\Entity\Irfo\IrfoGvPermitType;
use Dvsa\Olcs\Api\Entity\Irfo\IrfoPsvAuth;
use Dvsa\Olcs\Api\Entity\Irfo\IrfoPsvAuthType;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Entity\Fee\FeeType;

/**
 * Fee Type repository test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class FeeTypeTest extends RepositoryTestCase
{
    public function setUp(): void
    {
        $this->setUpSut(Repo::class, true);
    }

    public function testFetchLatestForOverpayment(): void
    {
        $qb = $this->createMockQb('QUERY');

        $this->mockCreateQueryBuilder($qb);

        $qb->shouldReceive('getQuery')
            ->andReturn(
                m::mock()
                    ->shouldReceive('execute')
                    ->andReturn(['RESULTS'])
                    ->getMock()
            );

        $this->assertEquals('RESULTS', $this->sut->fetchLatestForOverpayment());

        $expectedQuery = 'QUERY AND ft.feeType = [[ADJUSTMENT]] ORDER BY ft.effectiveFrom DESC LIMIT 1';

        $this->assertEquals($expectedQuery, $this->query);
    }

    public function testFetchListForApplication(): void
    {
        $applicationId = 99;

        $mockApplication = m::mock(ApplicationEntity::class)
            ->shouldReceive('getId')
            ->andReturn($applicationId)
            ->shouldReceive('getLicenceType')
            ->andReturn(new RefData('LICENCE_TYPE'))
            ->shouldReceive('getGoodsOrPsv')
            ->andReturn('GOODS_OR_PSV')
            ->getMock();

        $qb = $this->createMockQb('QUERY');

        $this->mockCreateQueryBuilder($qb);

        $this->queryBuilder
            ->shouldReceive('modifyQuery')
            ->with($qb)
            ->andReturnSelf()
            ->shouldReceive('withRefdata')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('with')
            ->with('feeType', 'ftft')
            ->once()
            ->andReturnSelf();

        $this->em
            ->shouldReceive('getReference')->with(ApplicationEntity::class, $applicationId)
            ->andReturn($mockApplication);

        $this->sut->shouldReceive('fetchPaginatedList')
            ->andReturn(['RESULTS']);

        $queryDto = FeeTypeListQry::create(
            [
                'application' => $applicationId,
                'effectiveDate' => '2014-10-26',
            ]
        );
        $this->assertEquals(['RESULTS'], $this->sut->fetchList($queryDto, Query::HYDRATE_OBJECT));

        $expectedQuery = 'QUERY'
            . ' AND ft.isMiscellaneous = [[0]]'
            . ' AND ft.feeType IN ["APP","VAR","GRANT","GRANTINT"]'
            . ' AND ft.goodsOrPsv = [[GOODS_OR_PSV]]'
            . ' AND ft.licenceType = [[LICENCE_TYPE]]'
            . ' AND ft.effectiveFrom <= [[2014-10-26T00:00:00+00:00]]'
            . ' ORDER BY ftft.id ASC'
            . ' ORDER BY ft.effectiveFrom DESC';

        $this->assertEquals($expectedQuery, $this->query);
    }

    public function testFetchListForLicence(): void
    {
        $licenceId = 99;

        $mockLicence = m::mock(LicenceEntity::class)
            ->shouldReceive('getId')
            ->andReturn($licenceId)
            ->shouldReceive('getLicenceType')
            ->andReturn(new RefData('LICENCE_TYPE'))
            ->shouldReceive('getGoodsOrPsv')
            ->andReturn('GOODS_OR_PSV')
            ->getMock();

        $qb = $this->createMockQb('QUERY');

        $this->mockCreateQueryBuilder($qb);

        $this->queryBuilder
            ->shouldReceive('modifyQuery')
            ->with($qb)
            ->andReturnSelf()
            ->shouldReceive('withRefdata')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('with')
            ->with('feeType', 'ftft')
            ->once()
            ->andReturnSelf();

        $this->em
            ->shouldReceive('getReference')->with(LicenceEntity::class, $licenceId)
            ->andReturn($mockLicence);

        $this->sut->shouldReceive('fetchPaginatedList')
            ->andReturn(['RESULTS']);

        $queryDto = FeeTypeListQry::create(
            [
                'licence' => $licenceId,
                'effectiveDate' => '2014-10-26',
            ]
        );
        $this->assertEquals(['RESULTS'], $this->sut->fetchList($queryDto, Query::HYDRATE_OBJECT));

        $expectedQuery = 'QUERY'
            . ' AND ft.feeType IN ["CONT"]'
            . ' AND ft.licenceType = [[LICENCE_TYPE]]'
            . ' AND ft.effectiveFrom <= [[2014-10-26T00:00:00+00:00]]'
            . ' ORDER BY ftft.id ASC'
            . ' ORDER BY ft.effectiveFrom DESC';

        $this->assertEquals($expectedQuery, $this->query);
    }

    public function testFetchListForOrganisation(): void
    {
        $now = new DateTime();
        $expectedDate = $now->format(DateTime::W3C);

        $organisationId = 99;

        $qb = $this->createMockQb('QUERY');

        $this->mockCreateQueryBuilder($qb);

        $this->queryBuilder
            ->shouldReceive('modifyQuery')
            ->with($qb)
            ->andReturnSelf()
            ->shouldReceive('withRefdata')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('with')
            ->with('feeType', 'ftft')
            ->once()
            ->andReturnSelf();

        $this->sut->shouldReceive('fetchPaginatedList')
            ->andReturn(['RESULTS']);

        $queryDto = FeeTypeListQry::create(
            [
                'organisation' => $organisationId,
            ]
        );
        $this->assertEquals(['RESULTS'], $this->sut->fetchList($queryDto, Query::HYDRATE_OBJECT));

        $expectedQuery = 'QUERY'
            . ' AND ft.isMiscellaneous = [[0]]'
            . ' AND ft.feeType IN ["IRFOGVPERMIT","IRFOPSVANN","IRFOPSVAPP","IRFOPSVCOPY"]'
            . ' AND ft.effectiveFrom <= [[' . $expectedDate . ']]'
            . ' ORDER BY ftft.id ASC'
            . ' ORDER BY ft.effectiveFrom DESC';

        $this->assertEquals($expectedQuery, $this->query);
    }

    public function testFetchListForBusReg(): void
    {
        $now = new DateTime();
        $expectedDate = $now->format(DateTime::W3C);

        $mockLicence = m::mock(LicenceEntity::class);

        $qb = $this->createMockQb('QUERY');

        $this->mockCreateQueryBuilder($qb);

        $this->queryBuilder
            ->shouldReceive('modifyQuery')
            ->with($qb)
            ->andReturnSelf()
            ->shouldReceive('withRefdata')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('with')
            ->with('feeType', 'ftft')
            ->once()
            ->andReturnSelf();

        $this->em
            ->shouldReceive('getReference')->with(LicenceEntity::class, 99)
            ->andReturn($mockLicence);

        $this->sut->shouldReceive('fetchPaginatedList')
            ->andReturn(['RESULTS']);

        $queryDto = FeeTypeListQry::create(
            [
                'busReg' => 1412,
                'licence' => 99,
            ]
        );
        $this->assertEquals(['RESULTS'], $this->sut->fetchList($queryDto, Query::HYDRATE_OBJECT));

        $expectedQuery = 'QUERY'
            . ' AND ft.isMiscellaneous = [[0]]'
            . ' AND ft.feeType IN ["BUSAPP","BUSVAR"]'
            . ' AND ft.effectiveFrom <= [[' . $expectedDate . ']]'
            . ' ORDER BY ftft.id ASC'
            . ' ORDER BY ft.effectiveFrom DESC';

        $this->assertEquals($expectedQuery, $this->query);
    }

    public function testFetchListMiscellaneous(): void
    {
        $now = new DateTime();
        $expectedDate = $now->format(DateTime::W3C);

        $qb = $this->createMockQb('QUERY');

        $this->mockCreateQueryBuilder($qb);

        $this->queryBuilder
            ->shouldReceive('modifyQuery')
            ->with($qb)
            ->andReturnSelf()
            ->shouldReceive('withRefdata')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('with')
            ->with('feeType', 'ftft')
            ->once()
            ->andReturnSelf();

        $this->sut->shouldReceive('fetchPaginatedList')
            ->andReturn(['RESULTS']);

        $queryDto = FeeTypeListQry::create(
            [
                'isMiscellaneous' => 'Y',
            ]
        );
        $this->assertEquals(['RESULTS'], $this->sut->fetchList($queryDto, Query::HYDRATE_OBJECT));

        $expectedQuery = 'QUERY'
            . ' AND ft.isMiscellaneous = [[1]]'
            . ' AND ft.effectiveFrom <= [[' . $expectedDate . ']]'
            . ' ORDER BY ftft.id ASC'
            . ' ORDER BY ft.effectiveFrom DESC';

        $this->assertEquals($expectedQuery, $this->query);
    }

    public function testFetchListForGoodsOrPsv(): void
    {
        $qb = $this->createMockQb('QUERY');

        $this->mockCreateQueryBuilder($qb);

        $this->queryBuilder
            ->shouldReceive('modifyQuery')
            ->with($qb)
            ->andReturnSelf()
            ->shouldReceive('withRefdata')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('with')
            ->with('feeType', 'ftft')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('paginate')
            ->once()
            ->andReturnSelf();

        $this->sut->shouldReceive('fetchPaginatedList')
            ->andReturn(['RESULTS']);

        $queryDto = AdminFeeTypeListQry::create(
            [
                'goodsOrPsv' => 'lcat_gv',
            ]
        );
        $this->assertEquals(['RESULTS'], $this->sut->fetchList($queryDto, Query::HYDRATE_OBJECT));

        $expectedQuery = 'QUERY'
            . ' AND ft.goodsOrPsv = [[lcat_gv]]'
            . ' AND ft.goodsOrPsv IS NOT NULL'
            . ' AND ft.isVisibleInInternal = [[1]]'
            . ' ORDER BY ft.id ASC';

        $this->assertEquals($expectedQuery, $this->query);
    }

    public function testFetchListForFeeType(): void
    {
        $qb = $this->createMockQb('QUERY');

        $this->mockCreateQueryBuilder($qb);

        $this->queryBuilder
            ->shouldReceive('modifyQuery')
            ->with($qb)
            ->andReturnSelf()
            ->shouldReceive('withRefdata')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('with')
            ->with('feeType', 'ftft')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('paginate')
            ->once()
            ->andReturnSelf();

        $this->sut->shouldReceive('fetchPaginatedList')
            ->andReturn(['RESULTS']);

        $queryDto = AdminFeeTypeListQry::create(
            [
                'feeType' => 'ANN',
            ]
        );
        $this->assertEquals(['RESULTS'], $this->sut->fetchList($queryDto, Query::HYDRATE_OBJECT));

        $expectedQuery = 'QUERY'
            . ' AND ft.feeType = [[ANN]]'
            . ' AND ft.goodsOrPsv IS NOT NULL'
            . ' AND ft.isVisibleInInternal = [[1]]'
            . ' ORDER BY ft.id ASC';

        $this->assertEquals($expectedQuery, $this->query);
    }

    public function testGetLatestIrfoFeeTypeForGvPermits(): void
    {
        $organisation = new Organisation();
        $irfoGvPermitType = new IrfoGvPermitType();
        $feeTypeFeeType = new RefData('feeTypefeeType');
        $irfoGvPermitType->setIrfoFeeType($feeTypeFeeType);

        $status = new RefData('status');

        $irfoEntity = new IrfoGvPermit($organisation, $irfoGvPermitType, $status);

        $this->sut->shouldReceive('fetchLatestForIrfo')->andReturn(['foo']);

        $this->assertEquals($this->sut->getLatestIrfoFeeType($irfoEntity, $feeTypeFeeType), ['foo']);
    }

    public function testGetLatestIrfoFeeTypeForPsvAuth(): void
    {
        $organisation = new Organisation();
        $irfoPsvAuthType = new IrfoPsvAuthType();
        $feeTypeFeeType = new RefData('feeTypefeeType');
        $irfoPsvAuthType->setIrfoFeeType($feeTypeFeeType);

        $status = new RefData('status');

        $irfoEntity = new IrfoPsvAuth($organisation, $irfoPsvAuthType, $status);

        $this->sut->shouldReceive('fetchLatestForIrfo')->andReturn(['foo']);

        $this->assertEquals($this->sut->getLatestIrfoFeeType($irfoEntity, $feeTypeFeeType), ['foo']);
    }

    public function testGetLatestIrfoFeeTypeForUnknownEntity(): void
    {
        $this->expectException(NotFoundException::class);

        $feeTypeFeeType = new RefData('feeTypefeeType');

        // force exception by passing something other than IrfoGvPermit or IrfoPsvAuth Entities
        $irfoEntity = new \StdClass();

        $this->sut->getLatestIrfoFeeType($irfoEntity, $feeTypeFeeType);
    }

    public function testFetchDistinctFeeTypesVisibleInInternal(): void
    {
        $qb = m::mock(QueryBuilder::class);
        $repo = m::mock(FeeType::class)->shouldAllowMockingProtectedMethods();

        $this->em->shouldReceive('getRepository')->with(FeeType::class)->andReturn($repo);

        $repo->shouldReceive('createQueryBuilder')->with('ft')->once()->andReturn($qb);

        $this->queryBuilder
            ->shouldReceive('modifyQuery')
            ->once()
            ->with($qb)
            ->andReturnSelf()
            ->shouldReceive('with')
            ->once()
            ->with('feeType', 'ftft')
            ->andReturnSelf();

        $qb->shouldReceive('distinct')->once()->andReturnSelf();
        $qb->shouldReceive('select')->once()->with(['ftft.id'])->andReturnSelf();
        $qb->shouldReceive('orderBy')->once()->with('ftft.id', 'ASC')->andReturnSelf();
        $qb->shouldReceive('where')
           ->once()
           ->with('ft.isVisibleInInternal = :isVisibleInInternal')
           ->andReturnSelf();
        $qb->shouldReceive('setParameter')->once()->with('isVisibleInInternal', 1)->andReturnSelf();
        $qb->shouldReceive('getQuery->getResult')->once()->andReturn(['RESULTS']);

        $this->assertSame(['RESULTS'], $this->sut->fetchDistinctFeeTypesVisibleInInternal());
    }
}
