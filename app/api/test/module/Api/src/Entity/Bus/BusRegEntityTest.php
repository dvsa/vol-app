<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Bus;

use Dvsa\Olcs\Api\Entity\Ebsr\EbsrSubmission;
use Dvsa\Olcs\Api\Entity\Publication\Publication as PublicationEntity;
use Dvsa\Olcs\Api\Entity\Publication\PublicationLink;
use Dvsa\Olcs\Api\Entity\Publication\PublicationSection;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Entity\Task\Task as TaskEntity;
use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Dvsa\Olcs\Api\Entity\Bus\BusReg as Entity;
use Dvsa\Olcs\Api\Entity\Bus\BusRegOtherService as BusRegOtherServiceEntity;
use Dvsa\Olcs\Api\Entity\Bus\BusNoticePeriod as BusNoticePeriodEntity;
use Dvsa\Olcs\Api\Entity\Bus\BusShortNotice as BusShortNoticeEntity;
use Dvsa\Olcs\Api\Entity\Fee\Fee as FeeEntity;
use Dvsa\Olcs\Api\Entity\Fee\FeeType as FeeTypeEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\System\RefData as RefDataEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Domain\Exception\ForbiddenException;
use Mockery as m;

/**
 * BusReg Entity Unit Tests
 *
 * Initially auto-generated but won't be overridden
 */
class BusRegEntityTest extends EntityTester
{
    /** @var Entity */
    protected $entity;

    public function setUp(): void
    {
        $this->entity = $this->instantiate($this->entityClass);
    }

    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    /**
     *
     * @param $receivedDate
     * @param $status
     * @param $fees
     * @param $expectedResult
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('shouldCreateFeeProvider')]
    public function testShouldCreateFee(mixed $receivedDate, mixed $status, mixed $fees, mixed $expectedResult): void
    {
        $statusRefData = new RefData($status);
        $entity = new Entity();
        $entity->setReceivedDate($receivedDate);
        $entity->setStatus($statusRefData);
        $entity->setFees(new ArrayCollection($fees));

        $this->assertEquals($expectedResult, $entity->shouldCreateFee());
    }

    /**
     * Data provider for shouldCreateFee
     *
     * @return array
     */
    public static function shouldCreateFeeProvider(): array
    {
        $outstandingFee = m::mock(FeeEntity::class);
        $outstandingFee->shouldReceive('isPaid')->andReturn(false);
        $outstandingFee->shouldReceive('isOutstanding')->andReturn(true);

        $paidFee = m::mock(FeeEntity::class);
        $paidFee->shouldReceive('isPaid')->andReturn(true);
        $paidFee->shouldReceive('isOutstanding')->never();

        $cancelledFee = m::mock(FeeEntity::class);
        $cancelledFee->shouldReceive('isPaid')->andReturn(false);
        $cancelledFee->shouldReceive('isOutstanding')->andReturn(false);

        return [
            [null, Entity::STATUS_NEW, [], false],
            [null, Entity::STATUS_VAR, [], false],
            ['2015-12-25', Entity::STATUS_CANCEL, [], false],
            ['2015-12-25', Entity::STATUS_NEW, [$cancelledFee], true],
            ['2015-12-25', Entity::STATUS_VAR, [$cancelledFee], true],
            ['2015-12-25', Entity::STATUS_NEW, [], true],
            ['2015-12-25', Entity::STATUS_VAR, [], true],
            ['2015-12-25', Entity::STATUS_NEW, [$paidFee], false],
            ['2015-12-25', Entity::STATUS_VAR, [$paidFee], false],
            ['2015-12-25', Entity::STATUS_NEW, [$outstandingFee], false],
            ['2015-12-25', Entity::STATUS_VAR, [$outstandingFee], false],
            ['2015-12-25', Entity::STATUS_NEW, [$cancelledFee, $paidFee], false],
            ['2015-12-25', Entity::STATUS_VAR, [$cancelledFee, $paidFee], false],
            ['2015-12-25', Entity::STATUS_NEW, [$cancelledFee, $outstandingFee], false],
            ['2015-12-25', Entity::STATUS_VAR, [$cancelledFee, $outstandingFee], false],
        ];
    }

    private function getAssertionsForCanEditIsTrue(): void
    {
        $id = 15;
        $regNo = 12345;

        //the bus reg entity which exists on the licence
        $licenceBusReg = new Entity();
        $licenceBusReg->setId($id);

        $licenceEntityMock = m::mock(LicenceEntity::class);
        $licenceEntityMock->shouldReceive('getLatestBusVariation')->once()->with($regNo)->andReturn($licenceBusReg);

        $this->entity->setRegNo($regNo);
        $this->entity->setId($id);
        $this->entity->setLicence($licenceEntityMock);
    }

    private function getAssertionsForCanEditIsFalseDueToVariation(): void
    {
        $id = 15;
        $otherBusId = 16;
        $regNo = 12345;

        //the bus reg entity which exists on the licence
        $licenceBusReg = new Entity();
        $licenceBusReg->setId($otherBusId);

        $licenceEntityMock = m::mock(LicenceEntity::class);
        $licenceEntityMock->shouldReceive('getLatestBusVariation')->once()->with($regNo)->andReturn($licenceBusReg);

        $this->entity->setRegNo($regNo);
        $this->entity->setId($id);
        $this->entity->setLicence($licenceEntityMock);
    }

    /**
     * Test isReadOnly
     *
     * @param bool   $isLatestVariation
     * @param string $status
     * @param bool   $expected
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('isReadOnlyProvider')]
    public function testIsReadOnly(mixed $isLatestVariation, mixed $status, mixed $expected): void
    {
        $busRegStatus = new RefDataEntity($status);

        /** @var Entity|m\MockInterface $busReg */
        $busReg = m::mock(Entity::class)->makePartial();
        $busReg->shouldReceive('isLatestVariation')->once()->andReturn($isLatestVariation);
        $busReg->setStatus($busRegStatus);

        $this->assertEquals($expected, $busReg->isReadOnly());
    }

    /**
     * Data provider for isFromEbsr
     *
     * @return array
     */
    public static function isReadOnlyProvider(): array
    {
        return [
            [false, Entity::STATUS_NEW, true],
            [false, Entity::STATUS_VAR, true],
            [false, Entity::STATUS_CANCEL, true],
            [false, Entity::STATUS_ADMIN, true],
            [false, Entity::STATUS_REGISTERED, true],
            [false, Entity::STATUS_REFUSED, true],
            [false, Entity::STATUS_WITHDRAWN, true],
            [false, Entity::STATUS_CNS, true],
            [false, Entity::STATUS_CANCELLED, true],
            [true, Entity::STATUS_NEW, false],
            [true, Entity::STATUS_VAR, false],
            [true, Entity::STATUS_CANCEL, false],
            [true, Entity::STATUS_ADMIN, false],
            [true, Entity::STATUS_REGISTERED, false],
            [true, Entity::STATUS_REFUSED, false],
            [true, Entity::STATUS_WITHDRAWN, false],
            [true, Entity::STATUS_CNS, false],
            [true, Entity::STATUS_CANCELLED, true]
        ];
    }

    /**
     * Test isCancellation
     *
     * @param string $status
     * @param bool   $expected
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('isCancellationProvider')]
    public function testIsCancellation(mixed $status, mixed $expected): void
    {
        $busRegStatus = new RefDataEntity($status);

        /** @var Entity|m\MockInterface $busReg */
        $busReg = m::mock(Entity::class)->makePartial();
        $busReg->setStatus($busRegStatus);

        $this->assertEquals($expected, $busReg->isCancellation());
    }

    /**
     * @return array
     */
    public static function isCancellationProvider(): array
    {
        return [
            [Entity::STATUS_NEW, false],
            [Entity::STATUS_VAR, false],
            [Entity::STATUS_CANCEL, true],
            [Entity::STATUS_ADMIN, false],
            [Entity::STATUS_REGISTERED, false],
            [Entity::STATUS_REFUSED, false],
            [Entity::STATUS_WITHDRAWN, false],
            [Entity::STATUS_CNS, false],
            [Entity::STATUS_CANCELLED, false]
        ];
    }

    /**
     * Test isCancelled
     *
     * @param string $status
     * @param bool   $expected
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('dpIsCancelled')]
    public function testIsCancelled(mixed $status, mixed $expected): void
    {
        $busRegStatus = new RefDataEntity($status);

        /** @var Entity|m\MockInterface $busReg */
        $busReg = m::mock(Entity::class)->makePartial();
        $busReg->setStatus($busRegStatus);

        $this->assertEquals($expected, $busReg->isCancelled());
    }

    /**
     * @return array
     */
    public static function dpIsCancelled(): array
    {
        return [
            [Entity::STATUS_NEW, false],
            [Entity::STATUS_VAR, false],
            [Entity::STATUS_CANCEL, false],
            [Entity::STATUS_ADMIN, false],
            [Entity::STATUS_REGISTERED, false],
            [Entity::STATUS_REFUSED, false],
            [Entity::STATUS_WITHDRAWN, false],
            [Entity::STATUS_CNS, false],
            [Entity::STATUS_CANCELLED, true]
        ];
    }

    /**
     * Test isFromEbsr
     *
     * @param string $isTxcApp
     * @param bool   $expected
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('isFromEbsrProvider')]
    public function testIsFromEbsr(mixed $isTxcApp, mixed $expected): void
    {
        $busReg = new Entity();
        $busReg->setIsTxcApp($isTxcApp);

        $this->assertEquals($expected, $busReg->isFromEbsr());
    }

    /**
     * Data provider for isFromEbsr
     *
     * @return array
     */
    public static function isFromEbsrProvider(): array
    {
        return [
            ['Y', true],
            ['N', false]
        ];
    }

    /**
     * Tests whether bus reg is from a data refresh
     *
     * @param $isDataRefresh
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('isEbsrRefreshProvider')]
    public function testIsEbsrRefresh(mixed $isDataRefresh): void
    {
        $ebsrSubmission = m::mock(EbsrSubmission::class);
        $ebsrSubmission->shouldReceive('isDataRefresh')->once()->andReturn($isDataRefresh);

        $submissions = new ArrayCollection([$ebsrSubmission]);

        $busReg = new Entity();
        $busReg->setEbsrSubmissions($submissions);

        $this->assertEquals($isDataRefresh, $busReg->isEbsrRefresh());
    }

    /**
     * @return array
     */
    public static function isEbsrRefreshProvider(): array
    {
        return [
            [true],
            [false]
        ];
    }

    /**
     * Tests isScottishRules
     *
     * @param int  $noticePeriodId
     * @param bool $expected
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('isScottishRulesProvider')]
    public function testIsScottishRules(mixed $noticePeriodId, mixed $expected): void
    {
        $noticePeriod = new BusNoticePeriodEntity();
        $noticePeriod->setId($noticePeriodId);

        $busReg = new Entity();
        $busReg->setBusNoticePeriod($noticePeriod);
        $this->assertEquals($expected, $busReg->isScottishRules());
    }

    /**
     * Data provider for isScottishRules
     *
     * @return array
     */
    public static function isScottishRulesProvider(): array
    {
        return [
            [BusNoticePeriodEntity::NOTICE_PERIOD_SCOTLAND, true],
            [BusNoticePeriodEntity::NOTICE_PERIOD_OTHER, false]
        ];
    }

    public function testIsVariation(): void
    {
        $busReg = new Entity();

        //  not variation
        $busReg->setVariationNo(null);
        static::assertFalse($busReg->isVariation());

        //  not variation
        $busReg->setVariationNo(0);
        static::assertFalse($busReg->isVariation());

        //  is variation
        $busReg->setVariationNo(1);
        static::assertTrue($busReg->isVariation());
    }

    /**
     * Tests calculated values
     */
    public function testGetCalculatedValues(): void
    {
        $id = 15;
        $regNo = 12345;

        //the bus reg entity which exists on the licence
        $licenceBusReg = new Entity();
        $licenceBusReg->setId($id);

        $licenceEntityMock = m::mock(LicenceEntity::class);
        $licenceEntityMock->shouldReceive('getLatestBusVariation')->once()->with($regNo)->andReturn($licenceBusReg);

        $noticePeriod = new BusNoticePeriodEntity();
        $noticePeriod->setId(BusNoticePeriodEntity::NOTICE_PERIOD_SCOTLAND);

        /** @var Entity|m\MockInterface $sut */
        $sut = m::mock(Entity::class)->makePartial();
        $sut->shouldReceive('getRegNo')->once()->andReturn($regNo);
        $sut->shouldReceive('getId')->once()->andReturn($id);
        $sut->shouldReceive('getLicence')->once()->andReturn($licenceEntityMock);
        $sut->shouldReceive('isScottishRules')->once()->andReturn(true);
        $sut->shouldReceive('isReadOnly')->once()->andReturn(true);
        $sut->shouldReceive('isFromEbsr')->once()->andReturn(true);

        $result = $sut->getCalculatedValues();

        $this->assertEquals($result['licence'], null);
        $this->assertEquals($result['isLatestVariation'], true);
        $this->assertEquals($result['isScottishRules'], true);
        $this->assertEquals($result['isFromEbsr'], true);
        $this->assertEquals($result['isReadOnly'], true);
    }

    /**
     * Tests calculated bundle values
     */
    public function testGetCalculatedBundleValues(): void
    {
        $bundledGetters = [
            'isLatestVariation',
            'isReadOnly',
            'isScottishRules',
            'isFromEbsr',
            'isCancelled',
            'isCancellation',
            'canWithdraw',
            'canRefuse',
            'canRefuseByShortNotice',
            'canCreateCancellation',
            'canPrint',
            'canRequestNewRouteMap',
            'canRepublish',
            'canCancelByAdmin',
            'canResetRegistration',
            'canCreateVariation',
        ];

        $expectedBundleValues = [];

        /** @var Entity|m\MockInterface $sut */
        $sut = m::mock(Entity::class)->makePartial();

        foreach ($bundledGetters as $getter) {
            $testValue = new \stdClass();
            $expectedBundleValues[$getter] = $testValue;
            $sut->shouldReceive($getter)->once()->andReturn($testValue);
        }

        $this->assertSame($expectedBundleValues, $sut->getCalculatedBundleValues());
    }

    /**
     * Tests canDelete throws exception correctly
     */
    public function testCanDeleteThrowsException(): void
    {
        $this->expectException(ForbiddenException::class);

        $this->getAssertionsForCanEditIsFalseDueToVariation();
        $this->entity->canDelete();
    }

    /**
     * Tests can delete doesn't throw exception when isVariation is true
     */
    public function testCanDeleteTrue(): void
    {
        $this->getAssertionsForCanEditIsTrue();
        $this->assertEquals(true, $this->entity->canDelete());
    }

    /**
     * Tests updateStops throws exception correctly
     */
    public function testUpdateStopsThrowsCanEditExceptionForLatestVariation(): void
    {
        $this->expectException(ForbiddenException::class);

        $this->getAssertionsForCanEditIsFalseDueToVariation();
        $this->entity->updateStops(null, null, null, null, null, null, null, null, null);
    }

    /**
     * Tests updateQualitySchemes throws exception correctly
     */
    public function testUpdateQualitySchemesThrowsCanEditExceptionForLatestVariation(): void
    {
        $this->expectException(ForbiddenException::class);

        $this->getAssertionsForCanEditIsFalseDueToVariation();
        $this->entity->updateQualitySchemes(null, null, null, null, null);
    }

    /**
     * Tests updateServiceDetails throws exception correctly
     */
    public function testUpdateServiceDetailsThrowsCanEditExceptionForLatestVariation(): void
    {
        $this->expectException(ForbiddenException::class);
        $busNoticePeriod = m::mock(BusNoticePeriodEntity::class);

        $this->getAssertionsForCanEditIsFalseDueToVariation();
        $this->entity->updateServiceDetails(null, null, null, null, null, null, null, null, null, $busNoticePeriod);
    }

    /**
     * Tests updateTaAuthority throws exception correctly
     */
    public function testUpdateTaAuthorityThrowsCanEditExceptionForLatestVariation(): void
    {
        $this->expectException(ForbiddenException::class);

        $this->getAssertionsForCanEditIsFalseDueToVariation();
        $this->entity->updateTaAuthority(null);
    }

    /**
     * Tests updateStops
     */
    public function testUpdateStops(): void
    {
        $useAllStops = 'Y';
        $hasManoeuvre = 'N';
        $manoeuvreDetail = 'string';
        $needNewStop = 'Y';
        $newStopDetail = 'string 2';
        $hasNotFixedStop = 'N';
        $notFixedStopDetail = 'string 3';
        $subsidised = 'Y';
        $subsidyDetail = 'string 4';

        $this->getAssertionsForCanEditIsTrue();

        $this->entity->updateStops(
            $useAllStops,
            $hasManoeuvre,
            $manoeuvreDetail,
            $needNewStop,
            $newStopDetail,
            $hasNotFixedStop,
            $notFixedStopDetail,
            $subsidised,
            $subsidyDetail
        );

        $this->assertEquals($useAllStops, $this->entity->getUseAllStops());
        $this->assertEquals($hasManoeuvre, $this->entity->getHasManoeuvre());
        $this->assertEquals($manoeuvreDetail, $this->entity->getManoeuvreDetail());
        $this->assertEquals($needNewStop, $this->entity->getNeedNewStop());
        $this->assertEquals($newStopDetail, $this->entity->getNewStopDetail());
        $this->assertEquals($hasNotFixedStop, $this->entity->getHasNotFixedStop());
        $this->assertEquals($notFixedStopDetail, $this->entity->getNotFixedStopDetail());
        $this->assertEquals($subsidised, $this->entity->getSubsidised());
        $this->assertEquals($subsidyDetail, $this->entity->getSubsidyDetail());
    }

    /**
     * tests updateQualitySchemes
     */
    public function testUpdateQualitySchemes(): void
    {
        $isQualityPartnership = 'Y';
        $qualityPartnershipDetails = 'string';
        $qualityPartnershipFacilitiesUsed = 'N';
        $isQualityContract = 'Y';
        $qualityContractDetails = 'string 2';

        $this->getAssertionsForCanEditIsTrue();

        $this->entity->updateQualitySchemes(
            $isQualityPartnership,
            $qualityPartnershipDetails,
            $qualityPartnershipFacilitiesUsed,
            $isQualityContract,
            $qualityContractDetails
        );

        $this->assertEquals($isQualityPartnership, $this->entity->getIsQualityPartnership());
        $this->assertEquals($qualityPartnershipDetails, $this->entity->getQualityPartnershipDetails());
        $this->assertEquals($qualityPartnershipFacilitiesUsed, $this->entity->getQualityPartnershipFacilitiesUsed());
        $this->assertEquals($isQualityContract, $this->entity->getIsQualityContract());
        $this->assertEquals($qualityContractDetails, $this->entity->getQualityContractDetails());
    }

    /**
     * tests updateTaAuthority
     */
    public function testUpdateTaAuthority(): void
    {
        $stoppingArrangements = 'Stopping arrangements';

        $this->getAssertionsForCanEditIsTrue();

        $this->entity->updateTaAuthority(
            $stoppingArrangements
        );

        $this->assertEquals($stoppingArrangements, $this->entity->getStoppingArrangements());
    }

    public function testUpdateServiceDetails(): void
    {
        $serviceNo = '12345';
        $startPoint = 'start point';
        $finishPoint = 'finish point';
        $via = 'via';
        $otherDetails = 'other details';
        $receivedDate = '2016-12-25';
        $effectiveDate = '2016-12-26';
        $applicationCompleteDate = '2016-12-27';
        $endDate = '2017-01-01';

        $busNoticePeriod = new BusNoticePeriodEntity();
        $busNoticePeriod->setStandardPeriod(56);

        $this->getAssertionsForCanEditIsTrue();

        $this->entity->updateServiceDetails(
            $serviceNo,
            $startPoint,
            $finishPoint,
            $via,
            $otherDetails,
            $receivedDate,
            $effectiveDate,
            $endDate,
            $applicationCompleteDate,
            $busNoticePeriod
        );

        $this->assertEquals($serviceNo, $this->entity->getServiceNo());
        $this->assertEquals($startPoint, $this->entity->getStartPoint());
        $this->assertEquals($finishPoint, $this->entity->getFinishPoint());
        $this->assertEquals($via, $this->entity->getVia());
        $this->assertEquals($otherDetails, $this->entity->getOtherDetails());
        $this->assertEquals($receivedDate, $this->entity->getReceivedDate());
        $this->assertEquals($effectiveDate, $this->entity->getEffectiveDate());
        $this->assertEquals($endDate, $this->entity->getEndDate());
        $this->assertEquals($applicationCompleteDate, $this->entity->getApplicationCompleteDate());
        $this->assertEquals($busNoticePeriod, $this->entity->getBusNoticePeriod());
    }

    public function testCreateNew(): void
    {
        $latestBusRouteNo = 3;
        $licNo = '123';

        /** @var m\Mock|LicenceEntity $licenceEntityMock */
        $licenceEntityMock = m::mock(LicenceEntity::class);
        $licenceEntityMock->shouldReceive('getLatestBusRouteNo')->once()->andReturn($latestBusRouteNo);
        $licenceEntityMock->shouldReceive('getLicNo')->once()->andReturn($licNo);

        $status = new RefDataEntity();
        $status->setId(Entity::STATUS_VAR);

        $revertStatus = new RefDataEntity();
        $revertStatus->setId(Entity::STATUS_VAR);

        $subsidised = new RefDataEntity();
        $subsidised->setId(Entity::SUBSIDY_NO);

        $busNoticePeriod = new BusNoticePeriodEntity();
        $busNoticePeriod->setId(BusNoticePeriodEntity::NOTICE_PERIOD_OTHER);

        $busReg = Entity::createNew($licenceEntityMock, $status, $revertStatus, $subsidised, $busNoticePeriod);

        // test some values from $defaultAll
        $this->assertEquals('N', $busReg->getIsShortNotice());
        $this->assertNull($busReg->getEndDate());

        // test some database metadata
        $this->assertNull($busReg->getId());
        $this->assertEquals(1, $busReg->getVersion());

        // test new specific values
        $this->assertEquals($licenceEntityMock, $busReg->getLicence());
        $this->assertEquals($status, $busReg->getStatus());
        $this->assertEquals($revertStatus, $busReg->getRevertStatus());
        $this->assertEquals($subsidised, $busReg->getSubsidised());
        $this->assertEquals($busNoticePeriod, $busReg->getBusNoticePeriod());
        $this->assertEquals($latestBusRouteNo + 1, $busReg->getRouteNo());
        $this->assertEquals('123/4', $busReg->getRegNo());

        // test some short notice values
        $busRegSN = $busReg->getShortNotice();
        $this->assertInstanceOf(BusShortNoticeEntity::class, $busRegSN);
        $this->assertNull($busRegSN->getId());
        $this->assertEquals(1, $busRegSN->getVersion());
        $this->assertEquals(0, $busRegSN->getBankHolidayChange());
        $this->assertNull($busRegSN->getHolidayDetail());
        $this->assertEquals($busReg, $busRegSN->getBusReg());
    }

    /**
     *
     * @param $statusId
     * @return bool
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('createVariationProvider')]
    public function testCreateVariation(mixed $statusId): void
    {
        $id = 15;
        $regNo = 12345;
        $variationNo = 5;
        $newVariationNo = 6;

        $status = new RefDataEntity();
        $status->setId($statusId);

        $revertStatus = new RefDataEntity();
        $revertStatus->setId($statusId);

        // the bus reg entity which exists on the licence
        $licenceBusReg = new Entity();
        $licenceBusReg->setId($id);
        $licenceBusReg->setVariationNo($variationNo);

        $licenceEntityMock = m::mock(LicenceEntity::class);
        $licenceEntityMock->shouldReceive('getLatestBusVariation')
            ->once()
            ->with($regNo, [])
            ->andReturn($licenceBusReg);
        $licenceEntityMock->shouldReceive('getLatestBusVariation')
            ->once()
            ->with($regNo)
            ->andReturn($licenceBusReg);

        $shortNotice = new BusShortNoticeEntity();
        $shortNotice->setId(100);

        $otherService1 = new BusRegOtherServiceEntity($licenceBusReg, 'otherService1');
        $otherService1->setId(201);
        $otherService1->setOlbsKey('olbs-key');

        $otherService2 = new BusRegOtherServiceEntity($licenceBusReg, 'otherService2');
        $otherService2->setId(202);

        // set up the bus reg entity based on which a variation is to be created
        $this->entity->setId($id);
        $this->entity->setRegNo($regNo);
        $this->entity->setLicence($licenceEntityMock);
        $this->entity->setVersion(10);
        $this->entity->setIsShortNotice('Y');
        $this->entity->setShortNotice($shortNotice);
        $this->entity->setEndDate(new \DateTime());
        $this->entity->addVariationReasons(new RefDataEntity());
        $this->entity->addOtherServices($otherService1);
        $this->entity->addOtherServices($otherService2);
        $this->entity->setStatus(new RefDataEntity(Entity::STATUS_REGISTERED));
        $this->entity->setOlbsKey(123);
        $this->entity->setVariationNo($variationNo);

        /** @var Entity $busReg */
        $busReg = $this->entity->createVariation($status, $revertStatus);

        // test some values from $defaultAll
        $this->assertEquals('N', $busReg->getIsShortNotice());
        $this->assertNull($busReg->getEndDate());

        // test some database metadata
        $this->assertNull($busReg->getId());
        $this->assertNull($busReg->getVersion());
        $this->assertNull($busReg->getVariationReasons());
        $this->assertNull($busReg->getOlbsKey());

        // test variation specific values
        $this->assertEquals($this->entity, $busReg->getParent());
        $this->assertEquals($status, $busReg->getStatus());
        $this->assertInstanceOf(\DateTime::class, $busReg->getStatusChangeDate());
        $this->assertEquals($revertStatus, $busReg->getRevertStatus());
        $this->assertEquals($newVariationNo, $busReg->getVariationNo());

        // test some short notice values
        $busRegSN = $busReg->getShortNotice();
        $this->assertInstanceOf(BusShortNoticeEntity::class, $busRegSN);
        $this->assertNull($busRegSN->getId());
        $this->assertEquals(1, $busRegSN->getVersion());
        $this->assertEquals(0, $busRegSN->getBankHolidayChange());
        $this->assertNull($busRegSN->getHolidayDetail());
        $this->assertEquals($busReg, $busRegSN->getBusReg());

        // test other services
        $this->assertEquals(2, $busReg->getOtherServices()->count());
        $this->assertNull($busReg->getOtherServices()->first()->getId());
        $this->assertNull($busReg->getOtherServices()->first()->getVersion());
        $this->assertNull($busReg->getOtherServices()->first()->getOlbsKey());
        $this->assertEquals($busReg, $busReg->getOtherServices()->first()->getBusReg());
        $this->assertEquals('otherService1', $busReg->getOtherServices()->first()->getServiceNo());
        $this->assertEquals('otherService2', $busReg->getOtherServices()->last()->getServiceNo());
    }

    /**
     * Data provider for testCreateVariation
     *
     * @return array
     */
    public static function createVariationProvider(): array
    {
        return [
            [Entity::STATUS_VAR],
            [Entity::STATUS_CANCEL],
        ];
    }

    /**
     * Tests updateServiceRegister
     */
    public function testUpdateServiceRegister(): void
    {
        $timetableAcceptable = 'Y';
        $mapSupplied = 'Y';
        $routeDescription = 'string';
        $trcConditionChecked = 'Y';
        $trcNotes = 'string 2';
        $copiedToLaPte = 'Y';
        $laShortNote = 'Y';
        $opNotifiedLaPte = 'Y';
        $applicationSigned = 'Y';

        $this->getAssertionsForCanEditIsTrue();

        $this->entity->updateServiceRegister(
            $trcConditionChecked,
            $trcNotes,
            $copiedToLaPte,
            $laShortNote,
            $opNotifiedLaPte,
            $applicationSigned,
            $timetableAcceptable,
            $mapSupplied,
            $routeDescription
        );

        $this->assertEquals($trcConditionChecked, $this->entity->getTrcConditionChecked());
        $this->assertEquals($trcNotes, $this->entity->getTrcNotes());
        $this->assertEquals($copiedToLaPte, $this->entity->getCopiedToLaPte());
        $this->assertEquals($laShortNote, $this->entity->getLaShortNote());
        $this->assertEquals($opNotifiedLaPte, $this->entity->getOpNotifiedLaPte());
        $this->assertEquals($applicationSigned, $this->entity->getApplicationSigned());
        $this->assertEquals($timetableAcceptable, $this->entity->getTimetableAcceptable());
        $this->assertEquals($mapSupplied, $this->entity->getMapSupplied());
        $this->assertEquals($routeDescription, $this->entity->getRouteDescription());
    }

    /**
     * Tests updateServiceRegister throws exception correctly
     */
    public function testUpdateServiceRegisterThrowsExceptionForLatestVariation(): void
    {
        $this->expectException(ForbiddenException::class);

        $this->getAssertionsForCanEditIsFalseDueToVariation();
        $this->entity->updateServiceRegister(null, null, null, null, null, null, null, null, null);
    }

    private function getAssertionsForCanMakeDecisionIsTrue(): void
    {
        $id = 15;
        $regNo = 12345;

        //the bus reg entity which exists on the licence
        $licenceBusReg = new Entity();
        $licenceBusReg->setId($id);

        $licenceEntityMock = m::mock(LicenceEntity::class);
        $licenceEntityMock->shouldReceive('getLatestBusVariation')->once()->with($regNo)->andReturn($licenceBusReg);

        $this->entity->setRegNo($regNo);
        $this->entity->setId($id);
        $this->entity->setLicence($licenceEntityMock);
    }

    private function getAssertionsForCanMakeDecisionIsFalse(): void
    {
        $id = 15;
        $otherBusId = 16;
        $regNo = 12345;

        //the bus reg entity which exists on the licence
        $licenceBusReg = new Entity();
        $licenceBusReg->setId($otherBusId);

        $licenceEntityMock = m::mock(LicenceEntity::class);
        $licenceEntityMock->shouldReceive('getLatestBusVariation')->once()->with($regNo)->andReturn($licenceBusReg);

        $this->entity->setRegNo($regNo);
        $this->entity->setId($id);
        $this->entity->setLicence($licenceEntityMock);
    }

    public function testResetStatus(): void
    {
        $this->getAssertionsForCanMakeDecisionIsTrue();

        $status = new RefDataEntity();
        $status->setId(Entity::STATUS_REGISTERED);

        $revertStatus = new RefDataEntity();
        $revertStatus->setId(Entity::STATUS_VAR);

        $this->entity->setStatus($status);
        $this->entity->setRevertStatus($revertStatus);

        $this->entity->resetStatus();

        $this->assertEquals($revertStatus, $this->entity->getStatus());
        $this->assertEquals($status, $this->entity->getRevertStatus());
        $this->assertInstanceOf(\DateTime::class, $this->entity->getStatusChangeDate());
    }

    /**
     * Tests resetStatus throws exception correctly
     */
    public function testResetStatusThrowsCanMakeDecisionException(): void
    {
        $this->expectException(ForbiddenException::class);

        $this->getAssertionsForCanMakeDecisionIsFalse();
        $this->entity->resetStatus();
    }

    public function testCancelByAdmin(): void
    {
        $this->getAssertionsForCanMakeDecisionIsTrue();

        $status = new RefDataEntity();
        $status->setId(Entity::STATUS_REGISTERED);
        $this->entity->setStatus($status);

        $newStatus = new RefDataEntity();
        $newStatus->setId(Entity::STATUS_ADMIN);

        $reason = 'testing';

        $this->entity->cancelByAdmin($newStatus, $reason);

        $this->assertEquals($newStatus, $this->entity->getStatus());
        $this->assertEquals($status, $this->entity->getRevertStatus());
        $this->assertInstanceOf(\DateTime::class, $this->entity->getStatusChangeDate());
        $this->assertEquals($reason, $this->entity->getReasonCancelled());
    }

    /**
     * Tests cancelByAdmin throws exception correctly
     */
    public function testCancelByAdminThrowsCanMakeDecisionException(): void
    {
        $this->expectException(ForbiddenException::class);

        $this->getAssertionsForCanMakeDecisionIsFalse();

        $status = new RefDataEntity();
        $status->setId(Entity::STATUS_ADMIN);

        $this->entity->cancelByAdmin($status, null);
    }

    /**
     * Tests cancelByAdmin throws exception correctly
     */
    public function testCancelByAdminThrowsIncorrectStatusException(): void
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\BadRequestException::class);

        $this->getAssertionsForCanMakeDecisionIsTrue();

        $status = new RefDataEntity();
        $status->setId(Entity::STATUS_REGISTERED);

        $this->entity->cancelByAdmin($status, null);
    }

    public function testWithdraw(): void
    {
        $this->getAssertionsForCanMakeDecisionIsTrue();

        $status = new RefDataEntity();
        $status->setId(Entity::STATUS_REGISTERED);
        $this->entity->setStatus($status);

        $newStatus = new RefDataEntity();
        $newStatus->setId(Entity::STATUS_WITHDRAWN);

        $reason = new RefDataEntity();

        $this->entity->withdraw($newStatus, $reason);

        $this->assertEquals($newStatus, $this->entity->getStatus());
        $this->assertEquals($status, $this->entity->getRevertStatus());
        $this->assertInstanceOf(\DateTime::class, $this->entity->getStatusChangeDate());
        $this->assertEquals($reason, $this->entity->getWithdrawnReason());
    }

    /**
     * Tests withdraw throws exception correctly
     */
    public function testWithdrawThrowsCanMakeDecisionException(): void
    {
        $this->expectException(ForbiddenException::class);

        $this->getAssertionsForCanMakeDecisionIsFalse();

        $status = new RefDataEntity();
        $status->setId(Entity::STATUS_WITHDRAWN);

        $reason = new RefDataEntity();

        $this->entity->withdraw($status, $reason);
    }

    /**
     * Tests withdraw throws exception correctly
     */
    public function testWithdrawThrowsIncorrectStatusException(): void
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\BadRequestException::class);

        $this->getAssertionsForCanMakeDecisionIsTrue();

        $status = new RefDataEntity();
        $status->setId(Entity::STATUS_REGISTERED);

        $reason = new RefDataEntity();

        $this->entity->withdraw($status, $reason);
    }

    public function testRefuse(): void
    {
        $this->getAssertionsForCanMakeDecisionIsTrue();

        $status = new RefDataEntity();
        $status->setId(Entity::STATUS_REGISTERED);
        $this->entity->setStatus($status);

        $newStatus = new RefDataEntity();
        $newStatus->setId(Entity::STATUS_REFUSED);

        $reason = 'testing';

        $this->entity->refuse($newStatus, $reason);

        $this->assertEquals($newStatus, $this->entity->getStatus());
        $this->assertEquals($status, $this->entity->getRevertStatus());
        $this->assertInstanceOf(\DateTime::class, $this->entity->getStatusChangeDate());
        $this->assertEquals($reason, $this->entity->getReasonRefused());
    }

    /**
     * Tests refuse throws exception correctly
     */
    public function testRefuseThrowsCanMakeDecisionException(): void
    {
        $this->expectException(ForbiddenException::class);

        $this->getAssertionsForCanMakeDecisionIsFalse();

        $status = new RefDataEntity();
        $status->setId(Entity::STATUS_REFUSED);

        $this->entity->refuse($status, null);
    }

    /**
     * Tests refuse throws exception correctly
     */
    public function testRefuseThrowsIncorrectStatusException(): void
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\BadRequestException::class);

        $this->getAssertionsForCanMakeDecisionIsTrue();

        $status = new RefDataEntity();
        $status->setId(Entity::STATUS_REGISTERED);

        $this->entity->refuse($status, null);
    }

    /**
     *
     * @param array  $busNoticePeriodData
     * @param array  $busRegData
     * @param string $expectedEffectiveDate
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideCalculateNoticeDate')]
    public function testRefuseByShortNotice(mixed $busNoticePeriodData, mixed $busRegData, mixed $expectedEffectiveDate): void
    {
        $this->getAssertionsForCanMakeDecisionIsTrue();

        $shortNotice = m::mock(BusShortNoticeEntity::class)->makePartial();
        $shortNotice->shouldReceive('reset')->once()->andReturnSelf();
        $this->entity->setShortNotice($shortNotice);

        foreach ($busRegData as $key => $value) {
            if ($key === 'parent') {
                $parent = new Entity();
                $parent->setEffectiveDate($value['effectiveDate']);
                $value = $parent;
            }
            $this->entity->{'set' . ucwords((string) $key)}($value);
        }

        $busNoticePeriod = new BusNoticePeriodEntity();
        foreach ($busNoticePeriodData as $key => $value) {
            $busNoticePeriod->{'set' . ucwords((string) $key)}($value);
        }
        $this->entity->setBusNoticePeriod($busNoticePeriod);

        $reason = 'testing';

        $this->entity->refuseByShortNotice($reason);

        $this->assertEquals('Y', $this->entity->getShortNoticeRefused());
        $this->assertEquals($reason, $this->entity->getReasonSnRefused());
        $this->assertEquals('N', $this->entity->getIsShortNotice());

        $this->assertEquals($expectedEffectiveDate, $this->entity->getEffectiveDate());
    }

    public static function provideCalculateNoticeDate(): array
    {
        $scotRules = [
            'standardPeriod' => 42,
            'cancellationPeriod' => 90,
            'id' => 1
        ];

        $otherRules = [
            'standardPeriod' => 56,
            'cancellationPeriod' => 0,
            'id' => 2
        ];

        return [
            [
                $scotRules,
                [
                    'variationNo' => 0,
                    'receivedDate' => null
                ],
                null //no received date
            ],
            [
                $scotRules,
                [
                    'variationNo' => 1,
                    'receivedDate' => null
                ],
                null //no received date
            ],
            [
                $otherRules,
                [
                    'variationNo' => 0,
                    'receivedDate' => null
                ],
                null //no received date
            ],
            [
                $otherRules,
                [
                    'variationNo' => 1,
                    'receivedDate' => null
                ],
                null //no received date
            ],
            [
                $otherRules,
                [
                    'variationNo' => 0,
                    'receivedDate' => '2015-02-09'
                ],
                new \DateTime('2015-04-06') //received + 56 days
            ],
            [
                $otherRules,
                [
                    'variationNo' => 1,
                    'receivedDate' => '2015-02-09'
                ],
                new \DateTime('2015-04-06') //received + 56 days
            ],
            [
                $otherRules,
                [
                    'variationNo' => 0,
                    'receivedDate' => '2017-02-14'
                ],
                new \DateTime('2017-04-11') //received + 56 days (example from OLCS-15276)
            ],
            [
                $otherRules,
                [
                    'variationNo' => 1,
                    'receivedDate' => '2017-02-14'
                ],
                new \DateTime('2017-04-11') //received + 56 days (example from OLCS-15276)
            ],
            [
                $scotRules,
                [
                    'variationNo' => 0,
                    'receivedDate' => '2015-02-09'
                ],
                new \DateTime('2015-03-23') //received + 42 days
            ],
            [
                $scotRules,
                [
                    'variationNo' => 1,
                    'receivedDate' => '2015-02-09'
                ],
                new \DateTime('2015-03-23') //received + 42 days (no parent)
            ],
            [
                $scotRules,
                [
                    'variationNo' => 1,
                    'receivedDate' => '2015-02-09',
                    'parent' => ['effectiveDate' => null]
                ],
                new \DateTime('2015-03-23') //received + 42 days (no parent effective date)
            ],
            [
                $scotRules,
                [
                    'variationNo' => 1,
                    'receivedDate' => '2014-07-15',
                    'parent' => ['effectiveDate' => '2014-06-11']
                ],
                new \DateTime('2014-09-10') //parent + 91 days
            ],
            [
                $scotRules,
                [
                    'variationNo' => 1,
                    'receivedDate' => '2014-07-27',
                    'parent' => ['effectiveDate' => '2014-06-11']
                ],
                new \DateTime('2014-09-10') //parent + 91 days
            ],
            [
                $scotRules,
                [
                    'variationNo' => 1,
                    'receivedDate' => '2014-07-30',
                    'parent' => ['effectiveDate' => '2014-06-11']
                ],
                new \DateTime('2014-09-10') //received + 42 days
            ],
            [
                $scotRules,
                [
                    'variationNo' => 1,
                    'receivedDate' => '2014-07-31',
                    'parent' => ['effectiveDate' => '2014-06-11']
                ],
                new \DateTime('2014-09-11') //received + 42 days
            ],
            [
                $scotRules,
                [
                    'variationNo' => 1,
                    'receivedDate' => '2014-10-10',
                    'parent' => ['effectiveDate' => '2014-06-11']
                ],
                new \DateTime('2014-11-21') //received + 42 days
            ],
        ];
    }

    /**
     * Tests refuseByShortNotice throws exception correctly
     */
    public function testRefuseByShortNoticeThrowsCanMakeDecisionException(): void
    {
        $this->expectException(ForbiddenException::class);

        $this->getAssertionsForCanMakeDecisionIsFalse();

        $this->entity->refuseByShortNotice(null);
    }

    public function testGrant(): void
    {
        /** @var Entity|m\MockInterface $sut */
        $sut = m::mock(Entity::class)->makePartial();
        $sut->shouldReceive('canMakeDecision')->once()->andReturn(true);
        $sut->shouldReceive('isGrantable')->once()->andReturn(true);
        $sut->shouldReceive('getStatusForGrant')->once()->andReturn(Entity::STATUS_REGISTERED);

        $status = new RefDataEntity();
        $status->setId(Entity::STATUS_VAR);
        $sut->setStatus($status);

        $newStatus = new RefDataEntity();
        $newStatus->setId(Entity::STATUS_REGISTERED);

        $reasons = ['testing'];

        $sut->grant($newStatus, $reasons);

        $this->assertEquals($newStatus, $sut->getStatus());
        $this->assertEquals($status, $sut->getRevertStatus());
        $this->assertInstanceOf(\DateTime::class, $sut->getStatusChangeDate());
        $this->assertEquals($reasons, $sut->getVariationReasons());
    }

    /**
     * Tests grant throws exception correctly
     */
    public function testGrantThrowsCanMakeDecisionException(): void
    {
        $this->expectException(ForbiddenException::class);

        $this->getAssertionsForCanMakeDecisionIsFalse();

        $status = new RefDataEntity();
        $status->setId(Entity::STATUS_REGISTERED);

        $this->entity->grant($status, null);
    }

    /**
     * Tests grant throws exception correctly
     */
    public function testGrantThrowsNotGrantableException(): void
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\BadRequestException::class);

        /** @var Entity|m\MockInterface $sut */
        $sut = m::mock(Entity::class)->makePartial();
        $sut->shouldReceive('canMakeDecision')->once()->andReturn(true);
        $sut->shouldReceive('isGrantable')->once()->andReturn(false);

        $status = new RefDataEntity();
        $status->setId(Entity::STATUS_REGISTERED);

        $sut->grant($status, null);
    }

    /**
     * Tests grant throws exception correctly
     */
    public function testGrantThrowsIncorrectStatusException(): void
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\BadRequestException::class);

        /** @var Entity|m\MockInterface $sut */
        $sut = m::mock(Entity::class)->makePartial();
        $sut->shouldReceive('canMakeDecision')->once()->andReturn(true);
        $sut->shouldReceive('isGrantable')->once()->andReturn(true);
        $sut->shouldReceive('getStatusForGrant')->once()->andReturn(Entity::STATUS_CANCELLED);

        $status = new RefDataEntity();
        $status->setId(Entity::STATUS_REGISTERED);

        $sut->grant($status, null);
    }

    /**
     *
     * @param string $statusId
     * @param array  $expected
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('getStatusForGrantDataProvider')]
    public function testGetStatusForGrant(mixed $statusId, mixed $expected): void
    {
        $status = new RefDataEntity();
        $status->setId($statusId);
        $this->entity->setStatus($status);

        $this->assertEquals($expected, $this->entity->getStatusForGrant());
    }

    public static function getStatusForGrantDataProvider(): array
    {
        return [
            [Entity::STATUS_NEW, Entity::STATUS_REGISTERED],
            [Entity::STATUS_VAR, Entity::STATUS_REGISTERED],
            [Entity::STATUS_CANCEL, Entity::STATUS_CANCELLED],
        ];
    }

    /**
     *
     * @param string $shortNoticeRefused
     * @param array  $expected
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('isShortNoticeRefusedDataProvider')]
    public function testIsShortNoticeRefused(mixed $shortNoticeRefused, mixed $expected): void
    {
        $this->entity->setShortNoticeRefused($shortNoticeRefused);

        $this->assertEquals($expected, $this->entity->isShortNoticeRefused());
    }

    public static function isShortNoticeRefusedDataProvider(): array
    {
        return [
            [null, false],
            ['N', false],
            ['Y', true],
        ];
    }

    /**
     *
     * @param string $statusId
     * @param string $shortNoticeRefused
     * @param bool   $withWithdrawnReason
     * @param array  $expected
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('getDecisionDataProvider')]
    public function testGetDecision(mixed $statusId, mixed $shortNoticeRefused, mixed $withWithdrawnReason, mixed $expected): void
    {
        $status = new RefDataEntity();
        $status->setId($statusId);
        $status->setDescription('Decision');

        $this->entity->setStatus($status);
        $this->entity->setShortNoticeRefused($shortNoticeRefused);

        if ($withWithdrawnReason) {
            $withdrawnReason = new RefDataEntity();
            $withdrawnReason->setDescription('Withdrawn Reason');
            $this->entity->setWithdrawnReason($withdrawnReason);
        }

        $this->entity->setReasonSnRefused('Reason SN Refused');
        $this->entity->setReasonRefused('Reason Refused');
        $this->entity->setReasonCancelled('Reason Cancelled');

        $this->assertEquals($expected, $this->entity->getDecision());
    }

    public static function getDecisionDataProvider(): array
    {
        return [
            // registered
            [
                Entity::STATUS_REGISTERED,
                'N',
                false,
                null
            ],
            // refused - nonShortNoticeRefused
            [
                Entity::STATUS_REFUSED,
                'N',
                false,
                ['decision' => 'Decision', 'reason' => 'Reason Refused']
            ],
            // refused - ShortNoticeRefused
            [
                Entity::STATUS_REFUSED,
                'Y',
                false,
                ['decision' => 'Decision', 'reason' => 'Reason SN Refused']
            ],
            // cancelled
            [
                Entity::STATUS_CANCELLED,
                'N',
                false,
                ['decision' => 'Decision', 'reason' => 'Reason Cancelled']
            ],
            // admin cancelled
            [
                Entity::STATUS_ADMIN,
                'N',
                false,
                ['decision' => 'Decision', 'reason' => 'Reason Cancelled']
            ],
            // admin withdrawn with a reason
            [
                Entity::STATUS_WITHDRAWN,
                'N',
                true,
                ['decision' => 'Decision', 'reason' => 'Withdrawn Reason']
            ],
            // admin withdrawn without a reason
            [
                Entity::STATUS_WITHDRAWN,
                'N',
                false,
                ['decision' => 'Decision', 'reason' => null]
            ],
        ];
    }

    private function getAssertionsForIsGrantable(): void
    {
        $this->entity->setTimetableAcceptable('Y');
        $this->entity->setMapSupplied('Y');
        $this->entity->setTrcConditionChecked('Y');
        $this->entity->setCopiedToLaPte('Y');
        $this->entity->setLaShortNote('Y');
        $this->entity->setApplicationSigned('Y');
        $this->entity->setEffectiveDate('any value');
        $this->entity->setReceivedDate('any value');
        $this->entity->setApplicationCompleteDate(new \DateTime('2024-12-25'));
        $this->entity->setServiceNo('any value');
        $this->entity->setStartPoint('any value');
        $this->entity->setFinishPoint('any value');
        $this->entity->setIsShortNotice('N');

        $this->entity->addBusServiceTypes('any value');
        $this->entity->addTrafficAreas('any value');
        $this->entity->addLocalAuthoritys('any value');

        $busNoticePeriod = new BusNoticePeriodEntity();
        $busNoticePeriod->setId(BusNoticePeriodEntity::NOTICE_PERIOD_OTHER);
        $this->entity->setBusNoticePeriod($busNoticePeriod);

        $this->entity->setStatus(new RefDataEntity(Entity::STATUS_NEW));
    }

    private function getAssertionsForIsGrantableCancellation(): void
    {
        $this->getAssertionsForIsGrantable();
        $this->entity->setTimetableAcceptable('N');
        $this->entity->setMapSupplied('N');
    }

    /**
     * Tests that timetable and supplied map don't need to be set to yes for granting a cancellation.
     * New and variation should return false
     *
     *
     * @param $status
     * @param $expected
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('dpIsGrantableForCancellation')]
    public function testIsGrantableForCancellation(mixed $status, mixed $expected): void
    {
        $this->getAssertionsForIsGrantableCancellation();

        $this->entity->setStatus(new RefDataEntity($status));

        // Grantable - Rule: Other - isShortNotice: N - Fee: none
        $this->assertEquals($expected, $this->entity->isGrantable());
    }

    /**
     * Tests that grant is only possible when in certain statuses
     *
     *
     * @param $status
     * @param $expected
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideIsGrantableForStatusCases')]
    public function testIsGrantableForStatus(mixed $status, mixed $expected): void
    {
        $this->getAssertionsForIsGrantable();
        $this->entity->setStatus(new RefDataEntity($status));
        $this->assertEquals($expected, $this->entity->isGrantable());
    }

    public static function provideIsGrantableForStatusCases(): \Generator
    {
        foreach (self::getAllStatuses() as $status) {
            yield [$status, in_array($status, [Entity::STATUS_NEW, Entity::STATUS_VAR, Entity::STATUS_CANCEL], true)];
        }
    }

    /**
     * data provider for testIsGrantableForCancellation
     *
     * @return array
     */
    public static function dpIsGrantableForCancellation(): array
    {
        return [
            [Entity::STATUS_NEW, false],
            [Entity::STATUS_VAR, false],
            [Entity::STATUS_CANCEL, true]
        ];
    }

    public function testIsGrantable(): void
    {
        $this->getAssertionsForIsGrantable();

        // Grantable - Rule: Other - isShortNotice: N - Fee: none
        $this->assertEquals(true, $this->entity->isGrantable());
    }

    public function testIsGrantableWithoutTimetableAcceptable(): void
    {
        $this->getAssertionsForIsGrantable();

        // nonGrantable - Rule: Other - isShortNotice: N - Fee: none
        // timetableAcceptable: N
        $this->entity->setTimetableAcceptable('N');
        $this->assertEquals(false, $this->entity->isGrantable());
    }

    public function testIsGrantableWithoutMapSupplied(): void
    {
        $this->getAssertionsForIsGrantable();

        // nonGrantable - Rule: Other - isShortNotice: N - Fee: none
        // mapSupplied: N
        $this->entity->setMapSupplied('N');
        $this->assertEquals(false, $this->entity->isGrantable());
    }

    public function testIsGrantableWithoutTrcConditionChecked(): void
    {
        $this->getAssertionsForIsGrantable();

        // nonGrantable - Rule: Other - isShortNotice: N - Fee: none
        // trcConditionChecked: N
        $this->entity->setTrcConditionChecked('N');
        $this->assertEquals(false, $this->entity->isGrantable());
    }

    public function testIsGrantableWithoutCopiedToLaPte(): void
    {
        $this->getAssertionsForIsGrantable();

        // nonGrantable - Rule: Other - isShortNotice: N - Fee: none
        // copiedToLaPte: N
        $this->entity->setCopiedToLaPte('N');
        $this->assertEquals(false, $this->entity->isGrantable());
    }

    public function testIsGrantableWithoutLaShortNote(): void
    {
        $this->getAssertionsForIsGrantable();

        $this->entity->setIsShortNotice('Y');
        $this->entity->setShortNotice('Y');

        $busNoticePeriod = new BusNoticePeriodEntity();
        $busNoticePeriod->setId(BusNoticePeriodEntity::NOTICE_PERIOD_OTHER);
        $this->entity->setBusNoticePeriod($busNoticePeriod);

        // nonGrantable - Rule: Other - isShortNotice: N - Fee: none
        // laShortNote: N
        $this->entity->setLaShortNote('N');
        $this->assertEquals(false, $this->entity->isGrantable());
    }

    public function testIsGrantableWithoutApplicationSigned(): void
    {
        $this->getAssertionsForIsGrantable();

        // nonGrantable - Rule: Other - isShortNotice: N - Fee: none
        // applicationSigned: N
        $this->entity->setApplicationSigned('N');
        $this->assertEquals(false, $this->entity->isGrantable());
    }

    public function testIsGrantableWithoutEffectiveDate(): void
    {
        $this->getAssertionsForIsGrantable();

        // nonGrantable - Rule: Other - isShortNotice: N - Fee: none
        // effectiveDate empty
        $this->entity->setEffectiveDate(null);
        $this->assertEquals(false, $this->entity->isGrantable());
    }

    public function testIsGrantableWithoutReceivedDate(): void
    {
        $this->getAssertionsForIsGrantable();

        // nonGrantable - Rule: Other - isShortNotice: N - Fee: none
        // receivedDate empty
        $this->entity->setReceivedDate(null);
        $this->assertFalse($this->entity->isGrantable());
    }

    public function testIsGrantableWithoutApplicationCompleteDate(): void
    {
        $this->getAssertionsForIsGrantable();

        // nonGrantable due to missing application complete date
        $this->entity->setApplicationCompleteDate(null);
        $this->assertFalse($this->entity->isGrantable());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestIsGrantableServiceNo')]
    public function testIsGrantableServiceNo(?string $serviceNo, bool $expectedResult): void
    {
        $this->getAssertionsForIsGrantable();

        $this->entity->setServiceNo($serviceNo);
        $this->assertEquals($expectedResult, $this->entity->isGrantable());
    }

    public static function dpTestIsGrantableServiceNo(): array
    {
        return [
            ['serviceNo' => null, 'expectedResult' => false],
            ['serviceNo' => '', 'expectedResult' => false],
            ['serviceNo' => '0', 'expectedResult' => true],
            ['serviceNo' => '1239a', 'expectedResult' => true],
        ];
    }

    public function testIsGrantableWithoutStartPoint(): void
    {
        $this->getAssertionsForIsGrantable();

        // nonGrantable - Rule: Other - isShortNotice: N - Fee: none
        // startPoint empty
        $this->entity->setStartPoint(null);
        $this->assertEquals(false, $this->entity->isGrantable());
    }

    public function testIsGrantableWithoutFinishPoint(): void
    {
        $this->getAssertionsForIsGrantable();

        // nonGrantable - Rule: Other - isShortNotice: N - Fee: none
        // finishPoint empty
        $this->entity->setFinishPoint(null);
        $this->assertEquals(false, $this->entity->isGrantable());
    }

    public function testIsGrantableWithoutBusServiceTypes(): void
    {
        $this->getAssertionsForIsGrantable();

        // nonGrantable - Rule: Other - isShortNotice: N - Fee: none
        // busServiceTypes empty
        $this->entity->setBusServiceTypes(null);
        $this->assertEquals(false, $this->entity->isGrantable());
    }

    public function testIsGrantableWithoutTrafficAreas(): void
    {
        $this->getAssertionsForIsGrantable();

        // nonGrantable - Rule: Other - isShortNotice: N - Fee: none
        // trafficAreas empty
        $this->entity->setTrafficAreas(null);
        $this->assertEquals(false, $this->entity->isGrantable());
    }

    public function testIsGrantableWithoutLocalAuthoritys(): void
    {
        $this->getAssertionsForIsGrantable();

        // nonGrantable - Rule: Other - isShortNotice: N - Fee: none
        // localAuthoritys empty
        $this->entity->setLocalAuthoritys(null);
        $this->assertEquals(false, $this->entity->isGrantable());
    }

    public function testIsGrantableWithNoticePeriodScotland(): void
    {
        $this->getAssertionsForIsGrantable();

        $busNoticePeriod = new BusNoticePeriodEntity();
        $busNoticePeriod->setId(BusNoticePeriodEntity::NOTICE_PERIOD_SCOTLAND);
        $this->entity->setBusNoticePeriod($busNoticePeriod);

        $shortNotice = new BusShortNoticeEntity();
        $shortNotice->setBankHolidayChange('Y');
        $this->entity->setShortNotice($shortNotice);

        // nonGrantable - Rule: Scotland - isShortNotice: N - Fee: none
        // extra data required from Scotland missing
        $this->entity->setOpNotifiedLaPte('N');
        $this->assertEquals(false, $this->entity->isGrantable());

        // nonGrantable - Rule: Scotland - isShortNotice: N - Fee: none
        // missing short notice info
        $this->entity->setOpNotifiedLaPte('Y');
        $this->assertEquals(true, $this->entity->isGrantable());
    }

    public function testIsGrantableWithFeePaid(): void
    {
        $this->getAssertionsForIsGrantable();

        $feeType = new FeeTypeEntity();

        $status = new RefDataEntity();
        $status->setId(FeeEntity::STATUS_PAID);

        $fee = new FeeEntity($feeType, 10, $status);

        $this->entity->setFees(new ArrayCollection([$fee]));

        // Grantable - Rule: Other - isShortNotice: N - Fee: paid
        $this->assertEquals(true, $this->entity->isGrantable());
    }

    public function testIsGrantableWithFeeOutstanding(): void
    {
        $this->getAssertionsForIsGrantable();

        $feeType = new FeeTypeEntity();

        $status = new RefDataEntity();
        $status->setId(FeeEntity::STATUS_OUTSTANDING);

        $fee = new FeeEntity($feeType, 10, $status);

        $this->entity->setFees(new ArrayCollection([$fee]));

        // nonGrantable - Rule: Other - isShortNotice: N - Fee: outstanding
        $this->assertEquals(false, $this->entity->isGrantable());
    }

    public function testIsGrantableWithMixedFeesGrantable(): void
    {
        $this->getAssertionsForIsGrantable();

        $feeType = new FeeTypeEntity();

        $status1 = new RefDataEntity();
        $status1->setId(FeeEntity::STATUS_CANCELLED);
        $status2 = new RefDataEntity();
        $status2->setId(FeeEntity::STATUS_CANCELLED);
        $status3 = new RefDataEntity();
        $status3->setId(FeeEntity::STATUS_PAID);

        $fee1 = new FeeEntity($feeType, 10, $status1);
        $fee2 = new FeeEntity($feeType, 10, $status2);
        $fee3 = new FeeEntity($feeType, 10, $status3);

        $this->entity->setFees(new ArrayCollection([$fee1, $fee2, $fee3]));

        // Grantable - Rule: Other - isShortNotice: N - Fee: paid
        $this->assertEquals(true, $this->entity->isGrantable());
    }

    public function testIsGrantableWithMixedFeesNonGrantable(): void
    {
        $this->getAssertionsForIsGrantable();

        $feeType = new FeeTypeEntity();

        $status1 = new RefDataEntity();
        $status1->setId(FeeEntity::STATUS_CANCELLED);
        $status2 = new RefDataEntity();
        $status2->setId(FeeEntity::STATUS_CANCELLED);
        $status3 = new RefDataEntity();
        $status3->setId(FeeEntity::STATUS_PAID);
        $status4 = new RefDataEntity();
        $status4->setId(FeeEntity::STATUS_OUTSTANDING);

        $fee1 = new FeeEntity($feeType, 10, $status1);
        $fee2 = new FeeEntity($feeType, 10, $status2);
        $fee3 = new FeeEntity($feeType, 10, $status3);
        $fee4 = new FeeEntity($feeType, 10, $status4);

        $this->entity->setFees(new ArrayCollection([$fee1, $fee2, $fee3, $fee4]));

        // nonGrantable - Rule: Other - isShortNotice: N - Fee: outstanding
        $this->assertEquals(false, $this->entity->isGrantable());
    }

    public function testIsGrantableWithoutShortNotice(): void
    {
        $this->getAssertionsForIsGrantable();

        // nonGrantable - Rule: Other - isShortNotice: Y - Fee: none
        // missing short notice details
        $this->entity->setIsShortNotice('Y');

        $busNoticePeriod = new BusNoticePeriodEntity();
        $busNoticePeriod->setId(BusNoticePeriodEntity::NOTICE_PERIOD_OTHER);
        $this->entity->setBusNoticePeriod($busNoticePeriod);

        $this->assertEquals(false, $this->entity->isGrantable());
    }

    public function testIsGrantableWithShortNoticeBankHoliday(): void
    {
        $this->getAssertionsForIsGrantable();

        $this->entity->setIsShortNotice('Y');

        $shortNotice = new BusShortNoticeEntity();
        $shortNotice->setBankHolidayChange('Y');
        $this->entity->setShortNotice($shortNotice);

        $busNoticePeriod = new BusNoticePeriodEntity();
        $busNoticePeriod->setId(BusNoticePeriodEntity::NOTICE_PERIOD_OTHER);
        $this->entity->setBusNoticePeriod($busNoticePeriod);

        // Grantable - Rule: Other - isShortNotice: Y - Fee: none
        // bankHolidayChange: Y
        $this->assertEquals(true, $this->entity->isGrantable());
    }

    public function testIsGrantableWithShortNoticeConnection(): void
    {
        $this->getAssertionsForIsGrantable();

        $this->entity->setIsShortNotice('Y');

        $shortNotice = new BusShortNoticeEntity();
        $shortNotice->setConnectionChange('Y');
        $this->entity->setShortNotice($shortNotice);

        $busNoticePeriod = new BusNoticePeriodEntity();
        $busNoticePeriod->setId(BusNoticePeriodEntity::NOTICE_PERIOD_OTHER);
        $this->entity->setBusNoticePeriod($busNoticePeriod);

        // nonGrantable - Rule: Other - isShortNotice: Y - Fee: none
        // connectionChange: Y, connectionDetail: empty
        $this->assertEquals(false, $this->entity->isGrantable());

        // Grantable - Rule: Other - isShortNotice: Y - Fee: none
        // connectionChange: Y, connectionDetail: not empty
        $shortNotice->setConnectionDetail('any value');
        $this->assertEquals(true, $this->entity->isGrantable());
    }

    public function testIsGrantableWithShortNoticeHoliday(): void
    {
        $this->getAssertionsForIsGrantable();

        $this->entity->setIsShortNotice('Y');

        $shortNotice = new BusShortNoticeEntity();
        $shortNotice->setHolidayChange('Y');
        $this->entity->setShortNotice($shortNotice);

        $busNoticePeriod = new BusNoticePeriodEntity();
        $busNoticePeriod->setId(BusNoticePeriodEntity::NOTICE_PERIOD_OTHER);
        $this->entity->setBusNoticePeriod($busNoticePeriod);

        // nonGrantable - Rule: Other - isShortNotice: Y - Fee: none
        // holidayChange: Y, holidayDetail: empty
        $this->assertEquals(false, $this->entity->isGrantable());

        // Grantable - Rule: Other - isShortNotice: Y - Fee: none
        // holidayChange: Y, holidayDetail: not empty
        $shortNotice->setHolidayDetail('any value');
        $this->assertEquals(true, $this->entity->isGrantable());
    }

    public function testIsGrantableWithShortNoticeNotAvailable(): void
    {
        $this->getAssertionsForIsGrantable();

        $this->entity->setIsShortNotice('Y');

        $shortNotice = new BusShortNoticeEntity();
        $shortNotice->setNotAvailableChange('Y');
        $this->entity->setShortNotice($shortNotice);

        $busNoticePeriod = new BusNoticePeriodEntity();
        $busNoticePeriod->setId(BusNoticePeriodEntity::NOTICE_PERIOD_OTHER);
        $this->entity->setBusNoticePeriod($busNoticePeriod);

        // nonGrantable - Rule: Other - isShortNotice: Y - Fee: none
        // notAvailableChange: Y, notAvailableDetail: empty
        $this->assertEquals(false, $this->entity->isGrantable());

        // Grantable - Rule: Other - isShortNotice: Y - Fee: none
        // notAvailableChange: Y, notAvailableDetail: not empty
        $shortNotice->setNotAvailableDetail('any value');
        $this->assertEquals(true, $this->entity->isGrantable());
    }

    public function testIsGrantableWithShortNoticePolice(): void
    {
        $this->getAssertionsForIsGrantable();

        $this->entity->setIsShortNotice('Y');

        $shortNotice = new BusShortNoticeEntity();
        $shortNotice->setPoliceChange('Y');
        $this->entity->setShortNotice($shortNotice);

        $busNoticePeriod = new BusNoticePeriodEntity();
        $busNoticePeriod->setId(BusNoticePeriodEntity::NOTICE_PERIOD_OTHER);
        $this->entity->setBusNoticePeriod($busNoticePeriod);

        // nonGrantable - Rule: Other - isShortNotice: Y - Fee: none
        // policeChange: Y, policeDetail: empty
        $this->assertEquals(false, $this->entity->isGrantable());

        // Grantable - Rule: Other - isShortNotice: Y - Fee: none
        // policeChange: Y, policeDetail: not empty
        $shortNotice->setPoliceDetail('any value');
        $this->assertEquals(true, $this->entity->isGrantable());
    }

    public function testIsGrantableWithShortNoticeReplacement(): void
    {
        $this->getAssertionsForIsGrantable();

        $this->entity->setIsShortNotice('Y');

        $shortNotice = new BusShortNoticeEntity();
        $shortNotice->setReplacementChange('Y');
        $this->entity->setShortNotice($shortNotice);

        $busNoticePeriod = new BusNoticePeriodEntity();
        $busNoticePeriod->setId(BusNoticePeriodEntity::NOTICE_PERIOD_OTHER);
        $this->entity->setBusNoticePeriod($busNoticePeriod);

        // nonGrantable - Rule: Other - isShortNotice: Y - Fee: none
        // replacementChange: Y, replacementDetail: empty
        $this->assertEquals(false, $this->entity->isGrantable());

        // Grantable - Rule: Other - isShortNotice: Y - Fee: none
        // replacementChange: Y, replacementDetail: not empty
        $shortNotice->setReplacementDetail('any value');
        $this->assertEquals(true, $this->entity->isGrantable());
    }

    public function testIsGrantableWithShortNoticeSpecialOccasion(): void
    {
        $this->getAssertionsForIsGrantable();

        $this->entity->setIsShortNotice('Y');

        $shortNotice = new BusShortNoticeEntity();
        $shortNotice->setSpecialOccasionChange('Y');
        $this->entity->setShortNotice($shortNotice);

        $busNoticePeriod = new BusNoticePeriodEntity();
        $busNoticePeriod->setId(BusNoticePeriodEntity::NOTICE_PERIOD_OTHER);
        $this->entity->setBusNoticePeriod($busNoticePeriod);

        // nonGrantable - Rule: Other - isShortNotice: Y - Fee: none
        // specialOccasionChange: Y, specialOccasionDetail: empty
        $this->assertEquals(false, $this->entity->isGrantable());

        // Grantable - Rule: Other - isShortNotice: Y - Fee: none
        // specialOccasionChange: Y, specialOccasionDetail: not empty
        $shortNotice->setSpecialOccasionDetail('any value');
        $this->assertEquals(true, $this->entity->isGrantable());
    }

    public function testIsGrantableWithShortNoticeTimetable(): void
    {
        $this->getAssertionsForIsGrantable();

        $this->entity->setIsShortNotice('Y');

        $shortNotice = new BusShortNoticeEntity();
        $shortNotice->setTimetableChange('Y');
        $this->entity->setShortNotice($shortNotice);

        $busNoticePeriod = new BusNoticePeriodEntity();
        $busNoticePeriod->setId(BusNoticePeriodEntity::NOTICE_PERIOD_OTHER);
        $this->entity->setBusNoticePeriod($busNoticePeriod);

        // nonGrantable - Rule: Other - isShortNotice: Y - Fee: none
        // timetableChange: Y, timetableDetail: empty
        $this->assertEquals(false, $this->entity->isGrantable());

        // Grantable - Rule: Other - isShortNotice: Y - Fee: none
        // timetableChange: Y, timetableDetail: not empty
        $shortNotice->setTimetableDetail('any value');
        $this->assertEquals(true, $this->entity->isGrantable());
    }

    public function testIsGrantableWithShortNoticeTrc(): void
    {
        $this->getAssertionsForIsGrantable();

        $this->entity->setIsShortNotice('Y');

        $shortNotice = new BusShortNoticeEntity();
        $shortNotice->setTrcChange('Y');
        $this->entity->setShortNotice($shortNotice);

        $busNoticePeriod = new BusNoticePeriodEntity();
        $busNoticePeriod->setId(BusNoticePeriodEntity::NOTICE_PERIOD_OTHER);
        $this->entity->setBusNoticePeriod($busNoticePeriod);

        // nonGrantable - Rule: Other - isShortNotice: Y - Fee: none
        // trcChange: Y, trcDetail: empty
        $this->assertEquals(false, $this->entity->isGrantable());

        // Grantable - Rule: Other - isShortNotice: Y - Fee: none
        // trcChange: Y, trcDetail: not empty
        $shortNotice->setTrcDetail('any value');
        $this->assertEquals(true, $this->entity->isGrantable());
    }

    public function testIsGrantableWithShortNoticeUnforseen(): void
    {
        $this->getAssertionsForIsGrantable();

        $this->entity->setIsShortNotice('Y');

        $shortNotice = new BusShortNoticeEntity();
        $shortNotice->setUnforseenChange('Y');
        $this->entity->setShortNotice($shortNotice);

        $busNoticePeriod = new BusNoticePeriodEntity();
        $busNoticePeriod->setId(BusNoticePeriodEntity::NOTICE_PERIOD_OTHER);
        $this->entity->setBusNoticePeriod($busNoticePeriod);

        // nonGrantable - Rule: Other - isShortNotice: Y - Fee: none
        // unforseenChange: Y, unforseenDetail: empty
        $this->assertEquals(false, $this->entity->isGrantable());

        // Grantable - Rule: Other - isShortNotice: Y - Fee: none
        // unforseenChange: Y, unforseenDetail: not empty
        $shortNotice->setUnforseenDetail('any value');
        $this->assertEquals(true, $this->entity->isGrantable());
    }

    public function testGetContextValue(): void
    {
        /** @var LicenceEntity $licence */
        $licence = m::mock(LicenceEntity::class)->makePartial();
        $licence->setLicNo(111);

        $entity = new Entity();

        $entity->setLicence($licence);

        $this->assertEquals(111, $entity->getContextValue());
    }

    /**
     *
     * @param string $status
     * @param string $revertStatus
     * @param string $shortNotice
     * @param string $section
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('dpGetPublicationSectionForGrantEmailProvider')]
    public function testGetPublicationSectionForGrantEmail(mixed $status, mixed $revertStatus, mixed $shortNotice, mixed $section): void
    {
        $entity = new Entity();
        $status = new RefData($status);
        $entity->setStatus($status);
        $revertStatus = new RefData($revertStatus);
        $entity->setRevertStatus($revertStatus);
        $entity->setIsShortNotice($shortNotice);

        $this->assertEquals($section, $entity->getPublicationSectionForGrantEmail());
    }

    public static function dpGetPublicationSectionForGrantEmailProvider(): array
    {
        return [
            [Entity::STATUS_REGISTERED, Entity::STATUS_NEW, 'Y', PublicationSection::BUS_NEW_SHORT_SECTION],
            [Entity::STATUS_REGISTERED, Entity::STATUS_NEW, 'N', PublicationSection::BUS_NEW_SECTION],
            [Entity::STATUS_REGISTERED, Entity::STATUS_VAR, 'Y', PublicationSection::BUS_VAR_SHORT_SECTION],
            [Entity::STATUS_REGISTERED, Entity::STATUS_VAR, 'N', PublicationSection::BUS_VAR_SECTION],
            [Entity::STATUS_CANCELLED, Entity::STATUS_CANCEL, 'Y', PublicationSection::BUS_CANCEL_SHORT_SECTION],
            [Entity::STATUS_CANCELLED, Entity::STATUS_CANCEL, 'N', PublicationSection::BUS_CANCEL_SECTION],
        ];
    }

    /**
     * Tests the method throws exception if status is incorrect
     *
     *
     * @param string $status
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('publicationSectionForGrantEmailInvalidStatusProvider')]
    public function testPublicationSectionForGrantEmailStatusException(mixed $status): void
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\RuntimeException::class);

        $entity = new Entity();
        $status = new RefData($status);
        $entity->setStatus($status);

        $entity->getPublicationSectionForGrantEmail();
    }

    /**
     * Data provider for isFromEbsr
     *
     * @return array
     */
    public static function publicationSectionForGrantEmailInvalidStatusProvider(): array
    {
        return [
            [Entity::STATUS_NEW],
            [Entity::STATUS_VAR],
            [Entity::STATUS_CANCEL],
            [Entity::STATUS_ADMIN],
            [Entity::STATUS_REFUSED],
            [Entity::STATUS_WITHDRAWN],
            [Entity::STATUS_CNS],
        ];
    }

    /**
     * Tests the method throws exception if revertStatus is incorrect
     *
     *
     * @param        $status
     * @param string $revertStatus
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('publicationSectionForGrantEmailInvalidRevertStatusProvider')]
    public function testPublicationSectionForGrantEmailRevertStatusException(mixed $status, mixed $revertStatus): void
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\RuntimeException::class);

        $entity = new Entity();
        $status = new RefData($status);
        $entity->setStatus($status);
        $revertStatus = new RefData($revertStatus);
        $entity->setRevertStatus($revertStatus);

        $entity->getPublicationSectionForGrantEmail();
    }

    /**
     * Data provider for testPublicationSectionForGrantEmailRevertStatusException
     *
     * @return array
     */
    public static function publicationSectionForGrantEmailInvalidRevertStatusProvider(): array
    {
        return [
            [Entity::STATUS_REGISTERED, Entity::STATUS_CANCEL],
            [Entity::STATUS_REGISTERED, Entity::STATUS_CANCELLED],
            [Entity::STATUS_REGISTERED, Entity::STATUS_ADMIN],
            [Entity::STATUS_REGISTERED, Entity::STATUS_REFUSED],
            [Entity::STATUS_REGISTERED, Entity::STATUS_WITHDRAWN],
            [Entity::STATUS_REGISTERED, Entity::STATUS_CNS],
            [Entity::STATUS_CANCELLED, Entity::STATUS_NEW],
            [Entity::STATUS_CANCELLED, Entity::STATUS_VAR],
            [Entity::STATUS_CANCELLED, Entity::STATUS_CANCELLED],
            [Entity::STATUS_CANCELLED, Entity::STATUS_ADMIN],
            [Entity::STATUS_CANCELLED, Entity::STATUS_REFUSED],
            [Entity::STATUS_CANCELLED, Entity::STATUS_WITHDRAWN],
            [Entity::STATUS_CANCELLED, Entity::STATUS_CNS],
        ];
    }

    public function testPublicationLinksForGrantEmail(): void
    {
        $pub3No = 1234;
        $pub3TrafficArea = 'trafficArea3';
        $pub4No = 5678;
        $pub4TrafficArea = 'trafficArea4';

        $expectedResult = $pub3No . ' ' . $pub3TrafficArea . ', ' . $pub4No . ' ' . $pub4TrafficArea;

        $matchPubSection = new PublicationSection();
        $matchPubSection->setId(PublicationSection::BUS_NEW_SHORT_SECTION);

        $otherPubSection = new PublicationSection();
        $otherPubSection->setId(PublicationSection::BUS_VAR_SHORT_SECTION);

        /** @var m\Mock|PublicationEntity $publication2 */
        $publication2 = m::mock(PublicationEntity::class)->makePartial();
        $publication2->shouldReceive('isNew')->once()->andReturn(false);

        /** @var m\Mock|PublicationEntity $publication3 */
        $publication3 = m::mock(PublicationEntity::class)->makePartial();
        $publication3->shouldReceive('isNew')->once()->andReturn(true);
        $publication3->shouldReceive('getPublicationNo')->once()->andReturn($pub3No);
        $publication3->shouldReceive('getTrafficArea->getName')->once()->andReturn($pub3TrafficArea);

        /** @var m\Mock|PublicationEntity $publication4 */
        $publication4 = m::mock(PublicationEntity::class)->makePartial();
        $publication4->shouldReceive('isNew')->once()->andReturn(true);
        $publication4->shouldReceive('getPublicationNo')->once()->andReturn($pub4No);
        $publication4->shouldReceive('getTrafficArea->getName')->once()->andReturn($pub4TrafficArea);

        //won't match due to section
        $pubLink1 = new PublicationLink();
        $pubLink1->setPublicationSection($otherPubSection);

        //matches but publication not new
        $pubLink2 = new PublicationLink();
        $pubLink2->setPublicationSection($matchPubSection);
        $pubLink2->setPublication($publication2);

        //match
        $pubLink3 = new PublicationLink();
        $pubLink3->setPublicationSection($matchPubSection);
        $pubLink3->setPublication($publication3);

        //match
        $pubLink4 = new PublicationLink();
        $pubLink4->setPublicationSection($matchPubSection);
        $pubLink4->setPublication($publication4);

        $publicationLinks = new ArrayCollection([$pubLink1, $pubLink2, $pubLink3, $pubLink4]);

        $entity = new Entity();
        $entity->setPublicationLinks($publicationLinks);

        $this->assertEquals($expectedResult, $entity->getPublicationLinksForGrantEmail($matchPubSection));
    }

    /**
     *
     * @param string          $serviceNo
     * @param ArrayCollection $otherServiceNumbers
     * @param string          $expected
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('getFormattedServiceNumbersProvider')]
    public function testGetFormattedServiceNumbers(mixed $serviceNo, mixed $otherServiceNumbers, mixed $expected): void
    {
        $busReg = new Entity();
        $busReg->setOtherServices($otherServiceNumbers);
        $busReg->setServiceNo($serviceNo);

        $this->assertEquals($expected, $busReg->getFormattedServiceNumbers());
    }

    /**
     * data provider for testGetFormattedServiceNumbers
     *
     * @return array
     */
    public static function getFormattedServiceNumbersProvider(): array
    {
        $serviceNo1 = '4567';
        $serviceNo2 = '8910';
        $serviceNo3 = "0";
        $serviceNo4 = "";
        $otherServiceNo1 = new BusRegOtherServiceEntity(new Entity(), $serviceNo1);
        $otherServiceNo2 = new BusRegOtherServiceEntity(new Entity(), $serviceNo2);
        $otherServiceNo3 = new BusRegOtherServiceEntity(new Entity(), $serviceNo3);
        $otherServiceNo4 = new BusRegOtherServiceEntity(new Entity(), $serviceNo4);
        $serviceNo = '123';

        $otherServiceNumbers = new ArrayCollection([
            $otherServiceNo1,
            $otherServiceNo2,
            $otherServiceNo3,
            $otherServiceNo4
        ]);
        $expectedFormatted = $serviceNo . '(' . $serviceNo1 . ',' . $serviceNo2 . ',' . $serviceNo3 . ')';

        return [
            [$serviceNo, new ArrayCollection(), $serviceNo],
            [$serviceNo, $otherServiceNumbers, $expectedFormatted]
        ];
    }

    /**
     * Tests the isShortNotice calculation for standard rules, calls populateShortNotice and checks the result
     *
     * @param $variationNo
     * @param $effectiveDate
     * @param $receivedDate
     * @param $expected
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('isShortNoticeStandardProvider')]
    public function testIsShortNoticeStandardRules(mixed $variationNo, mixed $receivedDate, mixed $effectiveDate, mixed $expected): void
    {
        /** @var m\Mock|BusNoticePeriodEntity $standardRules */
        $standardRules = m::mock(BusNoticePeriodEntity::class);
        $standardRules->shouldReceive('isScottishRules')->once()->andReturn(false);
        $standardRules->shouldReceive('getStandardPeriod')->once()->andReturn(42);
        $standardRules->shouldReceive('getCancellationPeriod')->never();

        $busReg = new Entity();
        $busReg->setVariationNo($variationNo);
        $busReg->setEffectiveDate($effectiveDate);
        $busReg->setReceivedDate($receivedDate);
        $busReg->setBusNoticePeriod($standardRules);
        $busReg->populateShortNotice();

        $this->assertEquals($expected, $busReg->getIsShortNotice());
    }

    /**
     * @return array
     */
    public static function isShortNoticeStandardProvider(): array
    {
        return [
            [0, '2014-05-31', '2014-07-01', 'Y'], //31 days
            [0, '2014-05-31', '2014-07-11', 'Y'], //41 days
            [0, '2014-05-31', '2014-07-12', 'N'], //42 days
            [0, '2014-05-31', '2014-07-25', 'N'], //55 days
            [0, '2014-05-31', '2014-07-26', 'N'], //56 days
            [0, '2017-02-14', '2017-04-10', 'N'], //55 days (example from OLCS-15276)
            [0, '2017-02-14', '2017-04-11', 'N'], //56 days (example from OLCS-15276)
            [0, '2014-05-31', '2014-08-28', 'N'], //89 days
            [1, '2014-05-31', '2014-07-01', 'Y'], //31 days
            [1, '2014-05-31', '2014-07-25', 'N'], //55 days
            [1, '2014-05-31', '2014-07-26', 'N'], //56 days
            [1, '2014-05-31', '2015-08-27', 'N'], //57 days
            [1, '2017-02-14', '2017-04-10', 'N'], //55 days (example from OLCS-15276)
            [1, '2017-02-14', '2017-04-11', 'N'], //56 days (example from OLCS-15276)
        ];
    }

    /**
     * Tests the isShortNotice calculation for new scottish apps, calls populateShortNotice and checks the result
     *
     * @param $variationNo
     * @param $effectiveDate
     * @param $receivedDate
     * @param $expected
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('isShortNoticeNewScottishProvider')]
    public function testIsShortNoticeNewScottishRules(mixed $variationNo, mixed $receivedDate, mixed $effectiveDate, mixed $expected): void
    {
        /** @var m\Mock|BusNoticePeriodEntity $standardRules */
        $standardRules = m::mock(BusNoticePeriodEntity::class);
        $standardRules->shouldReceive('isScottishRules')->once()->andReturn(true);
        $standardRules->shouldReceive('getStandardPeriod')->once()->andReturn(42);
        $standardRules->shouldReceive('getCancellationPeriod')->never();

        $busReg = new Entity();
        $busReg->setVariationNo($variationNo);
        $busReg->setEffectiveDate($effectiveDate);
        $busReg->setReceivedDate($receivedDate);
        $busReg->setBusNoticePeriod($standardRules);
        $busReg->populateShortNotice();

        $this->assertEquals($expected, $busReg->getIsShortNotice());
    }

    /**
     * @return array
     */
    public static function isShortNoticeNewScottishProvider(): array
    {
        return [
            [0, '2014-05-31', '2014-07-01', 'Y'], //31 days
            [0, '2014-05-31', '2014-07-11', 'Y'], //41 days
            [0, '2014-05-31', '2014-07-12', 'N'], //42 days
            [0, '2014-05-31', '2014-08-28', 'N'], //89 days
        ];
    }

    /**
     * Tests the isShortNotice calculation for wales apps, calls populateShortNotice and checks the result
     *
     * @param $variationNo
     * @param $effectiveDate
     * @param $receivedDate
     * @param $expected
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('isShortNoticeWalesProvider')]
    public function testIsShortNoticeNewWalesRules(mixed $variationNo, mixed $receivedDate, mixed $effectiveDate, mixed $expected): void
    {
        $standardRules = m::mock(BusNoticePeriodEntity::class);
        $standardRules->shouldReceive('isScottishRules')->once()->andReturn(false);
        $standardRules->shouldReceive('getStandardPeriod')->once()->andReturn(56);
        $standardRules->shouldReceive('getCancellationPeriod')->never();

        $busReg = new Entity();
        $busReg->setVariationNo($variationNo);
        $busReg->setEffectiveDate($effectiveDate);
        $busReg->setReceivedDate($receivedDate);
        $busReg->setBusNoticePeriod($standardRules);
        $busReg->populateShortNotice();
        $actual = $busReg->getIsShortNotice();

        $this->assertEquals($expected, $actual);
    }

    /**
     * @return array
     */
    public static function isShortNoticeWalesProvider(): array
    {
        return [
            [0, '2014-05-31', '2014-07-01', 'Y'], //31 days
            [0, '2014-05-31', '2014-07-11', 'Y'], //41 days
            [0, '2014-05-31', '2014-07-12', 'Y'], //42 days
            [0, '2014-05-31', '2014-08-28', 'N'], //89 days
            [0, '2014-05-31', '2014-07-26', 'N'], //56 days
            [0, '2014-05-31', '2014-07-25', 'Y'], //55 days
        ];
    }

    /**
     * Tests the isShortNotice calculation for new scottish variations, calls populateShortNotice and checks the result
     *
     * @param $receivedDate
     * @param $effectiveDate
     * @param $parentDate
     * @param $standardPeriodCalled
     * @param $expected
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('isShortNoticeVariationScottishProvider')]
    public function testIsShortNoticeVariationScottishRules(
        mixed $receivedDate,
        mixed $effectiveDate,
        mixed $parentDate,
        mixed $standardPeriodCalled,
        mixed $expected
    ): void {
        /** @var m\Mock|BusNoticePeriodEntity $scottishRules */
        $scottishRules = m::mock(BusNoticePeriodEntity::class);
        $scottishRules->shouldReceive('isScottishRules')->once()->andReturn(true);
        $scottishRules->shouldReceive('getStandardPeriod')->times($standardPeriodCalled)->andReturn(42);
        $scottishRules->shouldReceive('getCancellationPeriod')->once()->andReturn(90);

        $parentBusReg = new Entity();
        $parentBusReg->setEffectiveDate($parentDate);

        $busReg = new Entity();
        $busReg->setVariationNo(1);
        $busReg->setEffectiveDate($effectiveDate);
        $busReg->setReceivedDate($receivedDate);
        $busReg->setBusNoticePeriod($scottishRules);
        $busReg->setParent($parentBusReg);
        $busReg->populateShortNotice();

        $this->assertEquals($expected, $busReg->getIsShortNotice());
    }

    /**
     * @return array
     */
    public static function isShortNoticeVariationScottishProvider(): array
    {
        return [
            ['2014-07-15', '2014-07-21', '2014-06-11', 0, 'Y'], //parent less than 90 days
            ['2014-07-27', '2014-09-08', '2014-06-11', 0, 'Y'], //parent less than 90 days
            ['2014-07-30', '2014-09-09', '2014-06-11', 0, 'Y'], //parent is 90 days
            ['2014-07-31', '2014-09-10', '2014-06-11', 1, 'Y'], //41 days standard period
            ['2014-07-30', '2014-09-11', '2014-06-11', 1, 'N']  //43 days standard period, 92 days parent
        ];
    }

    /**
     * Tests hort notice returns false if there is no parent value
     *
     * @param $effectiveDate
     * @param $receivedDate
     * @param $parent
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('shortNoticeScottishRulesWithMissingParentProvider')]
    public function testShortNoticeScottishRulesWithMissingParent(mixed $effectiveDate, mixed $receivedDate, mixed $parent): void
    {
        /** @var m\Mock|BusNoticePeriodEntity $scottishRules */
        $scottishRules = m::mock(BusNoticePeriodEntity::class);
        $scottishRules->shouldReceive('isScottishRules')->once()->andReturn(true);

        $busReg = new Entity();
        $busReg->setVariationNo(1);
        $busReg->setEffectiveDate($effectiveDate);
        $busReg->setReceivedDate($receivedDate);
        $busReg->setBusNoticePeriod($scottishRules);
        $busReg->setParent($parent);
        $busReg->populateShortNotice();

        $this->assertEquals('N', $busReg->getIsShortNotice());
    }

    /**
     * @return array
     */
    public static function shortNoticeScottishRulesWithMissingParentProvider(): array
    {
        return [
            ['2016-12-25', '2016-12-26', null],
            ['2016-12-25', '2016-12-26', new Entity()],
        ];
    }

    /**
     * Tests short notice calculation returns false when dates are not yet filled in
     *
     * @param $effectiveDate
     * @param $receivedDate
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('shortNoticeMissingDatesProvider')]
    public function testShortNoticeMissingDates(mixed $effectiveDate, mixed $receivedDate): void
    {
        $scottishRules = m::mock(BusNoticePeriodEntity::class);
        $scottishRules->shouldReceive('isScottishRules')->never();

        $busReg = new Entity();
        $busReg->setEffectiveDate($effectiveDate);
        $busReg->setReceivedDate($receivedDate);
        $busReg->setBusNoticePeriod(new BusNoticePeriodEntity());
        $busReg->populateShortNotice();

        $this->assertEquals('N', $busReg->getIsShortNotice());
    }

    /**
     * @return array
     */
    public static function shortNoticeMissingDatesProvider(): array
    {
        return [
            ['2016-12-25', null],
            [null, '2016-12-25'],
            [null, null]
        ];
    }

    /**
     * tests getOpenTaskIds when there are tasks
     */
    public function testGetOpenTaskIds(): void
    {
        $busReg = new Entity();

        $task1 = m::mock(TaskEntity::class);
        $task1->shouldReceive('getIsClosed')->once()->withNoArgs()->andReturn('N');
        $task1->shouldReceive('getId')->once()->withNoArgs()->andReturn(1);

        $task2 = m::mock(TaskEntity::class);
        $task2->shouldReceive('getIsClosed')->once()->withNoArgs()->andReturn('Y');
        $task2->shouldReceive('getId')->never();

        $task3 = m::mock(TaskEntity::class);
        $task3->shouldReceive('getIsClosed')->once()->withNoArgs()->andReturn('N');
        $task3->shouldReceive('getId')->once()->withNoArgs()->andReturn(3);

        $busReg->setTasks(new ArrayCollection([$task1, $task2, $task3]));

        $this->assertEquals([1, 3], $busReg->getOpenTaskIds());
    }

    /**
     * tests getOpenTaskIds with no tasks
     */
    public function testGetOpenTaskIdsWithEmpty(): void
    {
        $busReg = new Entity();
        $busReg->setTasks(new ArrayCollection());
        $this->assertEquals([], $busReg->getOpenTaskIds());
    }

    public function testIsLatestVariation(): void
    {
        $regNo = 'foo';
        $busReg = new Entity();
        $busReg->setRegNo($regNo);
        /** @var m\Mock|LicenceEntity $mockLicence */
        $mockLicence = m::mock(LicenceEntity::class)
            ->shouldReceive('getLatestBusVariation')
            ->with($regNo)
            ->once()
            ->andReturn(null)
            ->getMock();

        $busReg->setLicence($mockLicence);
        $this->assertTrue($busReg->isLatestVariation());
    }

    public function testNotIsLatestVariation(): void
    {
        $regNo = 'foo';
        $busReg = new Entity();
        $busReg->setRegNo($regNo);
        $busReg->setId(1);

        $busReg1 = new Entity();
        $busReg1->setId(2);

        /** @var m\Mock|LicenceEntity $mockLicence */
        $mockLicence = m::mock(LicenceEntity::class)
            ->shouldReceive('getLatestBusVariation')
            ->with($regNo)
            ->once()
            ->andReturn($busReg1)
            ->getMock();

        $busReg->setLicence($mockLicence);
        $this->assertFalse($busReg->isLatestVariation());
    }

    public function testIsEbsrRefreshNoSubmissions(): void
    {
        $busReg = new Entity();
        $busReg->setEbsrSubmissions(new ArrayCollection());

        $this->assertFalse($busReg->isEbsrRefresh());
    }

    public function testAddOtherServiceNumber(): void
    {
        $busReg = new Entity();
        $busReg->addOtherServiceNumber('foo');
        $otherServices = new ArrayCollection();
        $otherServices->add(new BusRegOtherServiceEntity($busReg, 'foo'));
        $this->assertEquals($otherServices, $busReg->getOtherServices());
    }

    public function testGetRelatedOrganisation(): void
    {
        $busReg = new Entity();
        /** @var m\Mock|LicenceEntity $mockLicence */
        $mockLicence = m::mock(LicenceEntity::class)
            ->shouldReceive('getRelatedOrganisation')
            ->andReturn('foo')
            ->once()
            ->getMock();

        $busReg->setLicence($mockLicence);

        $this->assertEquals('foo', $busReg->getRelatedOrganisation());
    }

    public function testGetRelatedOrganisationNoLicence(): void
    {
        $this->assertNull((new Entity())->getRelatedOrganisation());
    }

    public function testCreateVariationFailsWhenCannotCreateVariation(): void
    {
        $this->expectException(ForbiddenException::class);
        /** @var Entity|m\Mock $busReg */
        $busReg = m::mock(Entity::class)->makePartial();
        $busReg->shouldReceive('canCreateVariation')
            ->with()
            ->andReturn(false);
        $busReg->createVariation(new RefData('foo'), new RefData('bar'));
    }

    /**
     *
     * @param $status
     * @param $expected
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideCanWithdrawCases')]
    public function testCanWithdraw(mixed $status, mixed $expected): void
    {
        $busReg = new Entity();
        $busReg->setStatus(new RefData($status));
        $this->assertSame($expected, $busReg->canWithdraw());
    }

    public static function provideCanWithdrawCases(): \Generator
    {
        $allowedStatuses = [Entity::STATUS_NEW, Entity::STATUS_VAR, Entity::STATUS_CANCEL];
        foreach (self::getAllStatuses() as $status) {
            yield [$status, in_array($status, $allowedStatuses, true)];
        }
    }

    /**
     *
     * @param $status
     * @param $expected
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideCanRefuseCases')]
    public function testCanRefuse(mixed $status, mixed $expected): void
    {
        $busReg = new Entity();
        $busReg->setStatus(new RefData($status));
        $this->assertSame($expected, $busReg->canRefuse());
    }

    public static function provideCanRefuseCases(): \Generator
    {
        $allowedStatuses = [Entity::STATUS_NEW, Entity::STATUS_VAR, Entity::STATUS_CANCEL];
        foreach (self::getAllStatuses() as $status) {
            yield [$status, in_array($status, $allowedStatuses, true)];
        }
    }

    /**
     *
     * @param $status
     * @param $isShortNotice
     * @param $isShortNoticeRefused
     * @param $expected
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideCanRefuseByShortNoticeCases')]
    public function testCanRefuseByShortNotice(mixed $status, mixed $isShortNotice, mixed $isShortNoticeRefused, mixed $expected): void
    {
        $busReg = new Entity();
        $busReg->setStatus(new RefData($status));
        $busReg->setIsShortNotice($isShortNotice);
        $busReg->setShortNoticeRefused($isShortNoticeRefused);
        $this->assertSame($expected, $busReg->canRefuseByShortNotice());
    }

    public static function provideCanRefuseByShortNoticeCases(): \Generator
    {
        $allowedStatuses = [Entity::STATUS_NEW, Entity::STATUS_VAR, Entity::STATUS_CANCEL];
        $isShortNoticeAllowed = [
            'Y' => true,
            'N' => false,
            'something-else' => false,
        ];
        $isShortNoticeRefusedAllowed = [
            'Y' => false,
            'N' => true,
            'something-else' => false,
        ];
        foreach (self::getAllStatuses() as $status) {
            $allowedByStatus = in_array($status, $allowedStatuses, true);
            foreach ($isShortNoticeAllowed as $isShortNotice => $allowedByIsShortNotice) {
                foreach ($isShortNoticeRefusedAllowed as $isShortNoticeRefused => $allowedByIsShortNoticeRefused) {
                    yield [
                        'status' => $status,
                        'isShortNotice' => $isShortNotice,
                        'isShortNoticeRefused' => $isShortNoticeRefused,
                        'expected' => $allowedByStatus && $allowedByIsShortNotice && $allowedByIsShortNoticeRefused,
                    ];
                }
            }
        }
    }

    /**
     *
     * @param $status
     * @param $isLatestVariation
     * @param $expected
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideCanCreateCancellationCases')]
    public function testCanCreateCancellation(mixed $status, mixed $isLatestVariation, mixed $expected): void
    {
        /** @var Entity|m\Mock $busReg */
        $busReg = m::mock(Entity::class)->makePartial();
        $busReg->setStatus(new RefData($status));
        $busReg->shouldReceive('isLatestVariation')->andReturn($isLatestVariation);
        $this->assertSame($expected, $busReg->canCreateCancellation());
    }

    public static function provideCanCreateCancellationCases(): \Generator
    {
        $allowedStatuses = [Entity::STATUS_REGISTERED];
        $isLatestVariationAllowed = [
            [true, true],
            [false, false],
        ];
        foreach (self::getAllStatuses() as $status) {
            $allowedByStatus = in_array($status, $allowedStatuses, true);
            foreach ($isLatestVariationAllowed as [$isLatestVariation, $allowedByIsLatestVariation]) {
                yield [
                    'status' => $status,
                    'isLatestVariation' => $isLatestVariation,
                    'expected' => $allowedByStatus && $allowedByIsLatestVariation
                ];
            }
        }
    }

    /**
     *
     * @param $status
     * @param $expected
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideCanPrintCases')]
    public function testCanPrint(mixed $status, mixed $expected): void
    {
        $busReg = new Entity();
        $busReg->setStatus(new RefData($status));
        $this->assertSame($expected, $busReg->canPrint());
    }

    public static function provideCanPrintCases(): \Generator
    {
        $allowedStatuses = [Entity::STATUS_REGISTERED, Entity::STATUS_CANCELLED];
        foreach (self::getAllStatuses() as $status) {
            yield [$status, in_array($status, $allowedStatuses, true)];
        }
    }

    /**
     *
     * @param $isFromEbsr
     * @param $expected
     * @internal     param $status
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideCanRequestNewRouteMapCases')]
    public function testRequestNewRouteMapPrint(mixed $isFromEbsr, mixed $expected): void
    {
        /** @var Entity|m\Mock $busReg */
        $busReg = m::mock(Entity::class)->makePartial();
        $busReg->shouldReceive('isFromEbsr')->andReturn($isFromEbsr);
        $this->assertSame($expected, $busReg->canRequestNewRouteMap());
    }

    public static function provideCanRequestNewRouteMapCases(): array
    {
        return [
            ['isFromEbsr' => true, 'expected' => true],
            ['isFromEbsr' => false, 'expected' => false],
        ];
    }

    /**
     *
     * @param $status
     * @param $isLatestVariation
     * @param $expected
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideCanRepublishCases')]
    public function testCanRepublish(mixed $status, mixed $isLatestVariation, mixed $expected): void
    {
        /** @var Entity|m\Mock $busReg */
        $busReg = m::mock(Entity::class)->makePartial();
        $busReg->setStatus(new RefData($status));
        $busReg->shouldReceive('isLatestVariation')->andReturn($isLatestVariation);
        $this->assertSame($expected, $busReg->canRepublish());
    }

    public static function provideCanRepublishCases(): \Generator
    {
        $allowedStatuses = [Entity::STATUS_REGISTERED, Entity::STATUS_CANCELLED];
        $isLatestVariationAllowed = [
            [true, true],
            [false, false],
        ];
        foreach (self::getAllStatuses() as $status) {
            $allowedByStatus = in_array($status, $allowedStatuses, true);
            foreach ($isLatestVariationAllowed as [$isLatestVariation, $allowedByIsLatestVariation]) {
                yield [
                    'status' => $status,
                    'isLatestVariation' => $isLatestVariation,
                    'expected' => $allowedByStatus && $allowedByIsLatestVariation
                ];
            }
        }
    }

    /**
     *
     * @param $status
     * @param $isLatestVariation
     * @param $expected
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideCanCancelByAdminCases')]
    public function testCanCancelByAdmin(mixed $status, mixed $isLatestVariation, mixed $expected): void
    {
        /** @var Entity|m\Mock $busReg */
        $busReg = m::mock(Entity::class)->makePartial();
        $busReg->setStatus(new RefData($status));
        $busReg->shouldReceive('isLatestVariation')->andReturn($isLatestVariation);
        $this->assertSame($expected, $busReg->canCancelByAdmin());
    }

    public static function provideCanCancelByAdminCases(): \Generator
    {
        $allowedStatuses = [Entity::STATUS_REGISTERED];
        $isLatestVariationAllowed = [
            [true, true],
            [false, false],
        ];
        foreach (self::getAllStatuses() as $status) {
            $allowedByStatus = in_array($status, $allowedStatuses, true);
            foreach ($isLatestVariationAllowed as [$isLatestVariation, $allowedByIsLatestVariation]) {
                yield [
                    'status' => $status,
                    'isLatestVariation' => $isLatestVariation,
                    'expected' => $allowedByStatus && $allowedByIsLatestVariation
                ];
            }
        }
    }

    /**
     *
     * @param $status
     * @param $isLatestVariation
     * @param $expected
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideCanResetRegistrationCases')]
    public function testCanResetRegistration(mixed $status, mixed $isLatestVariation, mixed $expected): void
    {
        /** @var Entity|m\Mock $busReg */
        $busReg = m::mock(Entity::class)->makePartial();
        $busReg->setStatus(new RefData($status));
        $busReg->shouldReceive('isLatestVariation')->andReturn($isLatestVariation);
        $this->assertSame($expected, $busReg->canResetRegistration());
    }

    public static function provideCanResetRegistrationCases(): \Generator
    {
        $allowedStatuses = [
            Entity::STATUS_ADMIN,
            Entity::STATUS_REGISTERED,
            Entity::STATUS_REFUSED,
            Entity::STATUS_WITHDRAWN,
            Entity::STATUS_CNS,
            Entity::STATUS_CANCELLED,
            Entity::STATUS_EXPIRED,
        ];
        $isLatestVariationAllowed = [
            [true, true],
            [false, false],
        ];
        foreach (self::getAllStatuses() as $status) {
            $allowedByStatus = in_array($status, $allowedStatuses, true);
            foreach ($isLatestVariationAllowed as [$isLatestVariation, $allowedByIsLatestVariation]) {
                yield [
                    'status' => $status,
                    'isLatestVariation' => $isLatestVariation,
                    'expected' => $allowedByStatus && $allowedByIsLatestVariation
                ];
            }
        }
    }

    /**
     *
     * @param $status
     * @param $isLatestVariation
     * @param $expected
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideCanCreateVariationCases')]
    public function testCantCreateVariation(mixed $status, mixed $isLatestVariation, mixed $expected): void
    {
        /** @var Entity|m\Mock $busReg */
        $busReg = m::mock(Entity::class)->makePartial();
        $busReg->setStatus(new RefData($status));
        $busReg->shouldReceive('isLatestVariation')->andReturn($isLatestVariation);
        $this->assertSame($expected, $busReg->canCreateVariation());
    }

    public static function provideCanCreateVariationCases(): \Generator
    {
        $allowedStatuses = [
            Entity::STATUS_REGISTERED,
        ];
        $isLatestVariationAllowed = [
            [true, true],
            [false, false],
        ];
        foreach (self::getAllStatuses() as $status) {
            $allowedByStatus = in_array($status, $allowedStatuses, true);
            foreach ($isLatestVariationAllowed as [$isLatestVariation, $allowedByIsLatestVariation]) {
                yield [
                    'status' => $status,
                    'isLatestVariation' => $isLatestVariation,
                    'expected' => $allowedByStatus && $allowedByIsLatestVariation
                ];
            }
        }
    }

    private static function getAllStatuses(): array
    {
        return [
            Entity::STATUS_NEW,
            Entity::STATUS_VAR,
            Entity::STATUS_CANCEL,
            Entity::STATUS_ADMIN,
            Entity::STATUS_REGISTERED,
            Entity::STATUS_REFUSED,
            Entity::STATUS_WITHDRAWN,
            Entity::STATUS_CNS,
            Entity::STATUS_CANCELLED,
            Entity::STATUS_EXPIRED,
        ];
    }
}
