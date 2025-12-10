<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Licence;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\Application\ApplicationCompletion;
use Dvsa\Olcs\Api\Entity\Bus\BusReg as BusRegEntity;
use Dvsa\Olcs\Api\Entity\Cases\Cases as CaseEntity;
use Dvsa\Olcs\Api\Entity\Cases\Complaint as ComplaintEntity;
use Dvsa\Olcs\Api\Entity\CommunityLic\CommunityLic as CommunityLicEntity;
use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails;
use Dvsa\Olcs\Api\Entity\IrhpInterface;
use Dvsa\Olcs\Api\Entity\Licence\Continuation;
use Dvsa\Olcs\Api\Entity\Licence\ContinuationDetail;
use Dvsa\Olcs\Api\Entity\Licence\Licence as Entity;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Licence\LicenceOperatingCentre;
use Dvsa\Olcs\Api\Entity\Licence\LicenceStatusRule;
use Dvsa\Olcs\Api\Entity\Licence\LicenceVehicle;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation as OrganisationEntity;
use Dvsa\Olcs\Api\Entity\Organisation\TradingName as TradingNameEntity;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitStock;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitType;
use Dvsa\Olcs\Api\Entity\Publication\Publication;
use Dvsa\Olcs\Api\Entity\Publication\PublicationLink;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea;
use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Api\Entity\Cases\ConditionUndertaking;
use Dvsa\Olcs\Api\Entity\OperatingCentre\OperatingCentre;
use Dvsa\Olcs\Api\Entity\ContactDetails\Address;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\OlcsTest\Api\Entity\Traits\TotAuthVehiclesTraitTest;
use RuntimeException;

/**
 * Licence Entity Unit Tests
 *
 * Initially auto-generated but won't be overridden
 *
 * @see Licence
 */
class LicenceEntityTest extends EntityTester
{
    use TotAuthVehiclesTraitTest;

    protected const A_NUMBER_OF_VEHICLES = 2;

    /**
     * @var Licence|null
     */
    protected $sut;

    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    public function testCanBecomeSpecialRestrictedNull()
    {
        /** @var Entity $licence */
        $licence = m::mock(Entity::class)->makePartial();
        $licence->shouldReceive('getGoodsOrPsv')->andReturn(null);
        $licence->shouldReceive('getLicenceType')->andReturn(null);

        $this->assertEquals(true, $licence->canBecomeSpecialRestricted());
    }

    /**
     * @dataProvider licenceTypeProvider
     */
    public function testCanBecomeSpecialRestricted($goodsOrPsv, $licenceType, $expected)
    {
        /** @var Entity $licence */
        $licence = m::mock(Entity::class)->makePartial();
        $licence->shouldReceive('getGoodsOrPsv->getId')->andReturn($goodsOrPsv);
        $licence->shouldReceive('getLicenceType->getId')->andReturn($licenceType);

        $this->assertEquals($expected, $licence->canBecomeSpecialRestricted());
    }

    /**
     * @dataProvider updateSafetyDetails
     */
    public function testUpdateSafetyDetails(
        $safetyInsVehicles,
        $safetyInsTrailers,
        $tachographIns,
        $tachographInsName,
        $safetyInsVaries,
        $expectedException = null
    ) {
        /** @var Entity $licence */
        $licence = $this->instantiate(Entity::class);

        if ($expectedException !== null) {
            $this->expectException($expectedException);
        }

        $licence->updateSafetyDetails(
            $safetyInsVehicles,
            $safetyInsTrailers,
            $tachographIns,
            $tachographInsName,
            $safetyInsVaries
        );

        if ($expectedException == null) {
            $this->assertEquals($safetyInsVehicles, $licence->getSafetyInsVehicles());
            $this->assertEquals($safetyInsTrailers, $licence->getSafetyInsTrailers());
            $this->assertEquals($tachographIns, $licence->getTachographIns());
            $this->assertEquals($tachographInsName, $licence->getTachographInsName());
            $this->assertEquals($safetyInsVaries, $licence->getSafetyInsVaries());
        }
    }

    public function testGetRemainingSpaces()
    {
        $lvCollection = m::mock(ArrayCollection::class);
        $activeCollection = m::mock(ArrayCollection::class);

        $lvCollection->shouldReceive('matching')
            ->once()
            ->with(m::type(Criteria::class))
            ->andReturn($activeCollection);

        $lv = m::mock(LicenceVehicle::class);
        $lv->shouldReceive('getRemovalDate')
           ->times(4)
           ->andReturnNull();

        $lv->shouldReceive('getSpecifiedDate')
           ->times(4)
           ->andReturn(new DateTime());

        $activeCollection->shouldReceive('toArray')
            ->once()
            ->andReturn([$lv, $lv, $lv, $lv]);

        $licence = $this->instantiate(Entity::class);

        $licence->setTotAuthVehicles(10);
        $licence->setLicenceVehicles($lvCollection);

        $this->assertEquals(6, $licence->getRemainingSpaces());
    }

    public function testGetActiveVehiclesCount()
    {
        $lvCollection = m::mock(ArrayCollection::class);
        $activeCollection = m::mock(ArrayCollection::class);

        $lvCollection->shouldReceive('matching')
            ->once()
            ->with(m::type(Criteria::class))
            ->andReturn($activeCollection);

        $lv = m::mock(LicenceVehicle::class);
        $lv->shouldReceive('getRemovalDate')
            ->once()
            ->andReturnNull();

        $lv->shouldReceive('getSpecifiedDate')
           ->once()
           ->andReturn(new DateTime());

        $activeCollection->shouldReceive('toArray')
                         ->once()
                         ->andReturn([$lv]);

        $licence = $this->instantiate(Entity::class);
        $licence->setLicenceVehicles($lvCollection);

        $this->assertEquals(1, $licence->getActiveVehiclesCount());
    }

    public function testGetActiveVehicles()
    {
        $lvCollection = m::mock(ArrayCollection::class);
        $activeCollection = m::mock(ArrayCollection::class);

        $lvCollection->shouldReceive('matching')
            ->once()
            ->with(m::type(Criteria::class))
            ->andReturn($activeCollection);

        $activeCollection->shouldReceive('toArray')
                         ->once()
                         ->andReturn([]);

        $licence = $this->instantiate(Entity::class);
        $licence->setLicenceVehicles($lvCollection);

        $this->assertInstanceOf(ArrayCollection::class, $licence->getActiveVehicles());
    }

    public function testGetOtherActiveLicences()
    {
        $licences = new ArrayCollection();

        /** @var RefData $goodsOrPsv */
        $goodsOrPsv = m::mock(RefData::class)->makePartial();
        $goodsOrPsv->setId(Entity::LICENCE_CATEGORY_PSV);

        $licence = $this->instantiate(Entity::class);
        $licence->setId(111);
        $licence->setGoodsOrPsv($goodsOrPsv);
        $licences->add($licence);

        /** @var RefData $type1 */
        $type1 = m::mock(RefData::class)->makePartial();
        $type1->setId(Entity::LICENCE_TYPE_SPECIAL_RESTRICTED);

        /** @var RefData $type2 */
        $type2 = m::mock(RefData::class)->makePartial();
        $type2->setId(Entity::LICENCE_TYPE_STANDARD_NATIONAL);

        /** @var Entity $otherLicence1 */
        $otherLicence1 = m::mock(Entity::class)->makePartial();
        $otherLicence1->setId(222);
        $otherLicence1->setStatus(new RefData(Entity::LICENCE_STATUS_VALID));
        $otherLicence1->setLicenceType($type1);
        $otherLicence1->setGoodsOrPsv($goodsOrPsv);
        $licences->add($otherLicence1);

        $otherLicence2 = m::mock(Entity::class)->makePartial();
        $otherLicence2->setId(333);
        $otherLicence2->setStatus(new RefData(Entity::LICENCE_STATUS_SUSPENDED));
        $otherLicence2->setLicenceType($type2);
        $otherLicence2->setGoodsOrPsv($goodsOrPsv);
        $licences->add($otherLicence2);

        /** @var OrganisationEntity $org */
        $org = m::mock(OrganisationEntity::class)->makePartial();
        $org->setLicences($licences);

        $licence->setOrganisation($org);

        $otherActiveLicences = $licence->getOtherActiveLicences();

        $this->assertFalse($otherActiveLicences->contains($licence));
        $this->assertFalse($otherActiveLicences->contains($otherLicence1));
        $this->assertTrue($otherActiveLicences->contains($otherLicence2));
    }

    public function updateSafetyDetails()
    {
        return [
            [
                2,
                1,
                'tach_external',
                'Some name',
                'Y',
                null
            ],
            [
                2,
                1,
                'tach_external',
                '',
                'Y',
                ValidationException::class
            ],
            [
                2,
                1,
                'tach_internal',
                '',
                'Y',
                null
            ],
            [
                2,
                1,
                'tach_na',
                '',
                'Y',
                null
            ],
            [
                null,
                1,
                'tach_na',
                '',
                'Y',
                null
            ]
        ];
    }

    public function licenceTypeProvider()
    {
        return [
            [
                Entity::LICENCE_CATEGORY_GOODS_VEHICLE,
                Entity::LICENCE_TYPE_STANDARD_NATIONAL,
                false
            ],
            [
                Entity::LICENCE_CATEGORY_PSV,
                Entity::LICENCE_TYPE_STANDARD_NATIONAL,
                false
            ],
            [
                Entity::LICENCE_CATEGORY_PSV,
                Entity::LICENCE_TYPE_RESTRICTED,
                false
            ],
            [
                Entity::LICENCE_CATEGORY_PSV,
                Entity::LICENCE_TYPE_SPECIAL_RESTRICTED,
                true
            ]
        ];
    }

    public function testUpdateTotalCommunityLicences()
    {
        $sut = m::mock(Entity::class)->makePartial();
        $sut->updateTotalCommunityLicences(10);
        $this->assertEquals(10, $sut->getTotCommunityLicences());
    }

    /**
     * @dataProvider trafficAreaProvider
     */
    public function testGetSerialNoPrefixFromTrafficArea($isNi, $expected)
    {
        $sut = m::mock(Entity::class)->makePartial();
        $sut->shouldReceive('getTrafficArea')
            ->andReturn(
                m::mock()
                ->shouldReceive('getIsNi')
                ->andReturn($isNi)
                ->once()
                ->getMock()
            )
            ->once()
            ->getMock();

        $this->assertEquals($expected, $sut->getSerialNoPrefixFromTrafficArea());
    }

    public function trafficAreaProvider()
    {
        return [
            [true, CommunityLicEntity::PREFIX_NI],
            [false, CommunityLicEntity::PREFIX_GB],
        ];
    }

    /**
     * @dataProvider dpHasCommunityLicenceOfficeCopy
     */
    public function testHasCommunityLicenceOfficeCopy($status, $expected)
    {
        $communityLic1 = new CommunityLicEntity();
        $communityLic1->setId(1);
        $communityLic1->setIssueNo(0);
        $communityLic1->setStatus(new RefData($status));

        $communityLic2 = new CommunityLicEntity();
        $communityLic2->setId(2);
        $communityLic2->setIssueNo(1);
        $communityLic2->setStatus(new RefData($status));

        $collection = new ArrayCollection([$communityLic1, $communityLic2]);

        $licence = m::mock(Entity::class)->makePartial();
        $licence->shouldReceive('getCommunityLics')
            ->withNoArgs()
            ->andReturn($collection);

        $this->assertSame($expected, $licence->hasCommunityLicenceOfficeCopy([1]));
    }

    public function dpHasCommunityLicenceOfficeCopy()
    {
        return [
            [CommunityLicEntity::STATUS_ANNUL, false],
            [CommunityLicEntity::STATUS_ACTIVE, true],
            [CommunityLicEntity::STATUS_EXPIRED, false],
            [CommunityLicEntity::STATUS_PENDING, true],
            [CommunityLicEntity::STATUS_RETURNDED, false],
            [CommunityLicEntity::STATUS_SUSPENDED, true],
            [CommunityLicEntity::STATUS_WITHDRAWN, true],
        ];
    }

    public function testHasApprovedUnfulfilledConditions()
    {
        /** @var Entity $licence */
        $licence = m::mock(Entity::class)->makePartial();
        $licence->shouldReceive('getConditionUndertakings->matching->count')
            ->andReturn(1);

        $this->assertTrue($licence->hasApprovedUnfulfilledConditions());
    }

    public function testHasApprovedUnfulfilledConditionsNone()
    {
        /** @var Entity $licence */
        $licence = m::mock(Entity::class)->makePartial();
        $licence->shouldReceive('getConditionUndertakings->matching->count')
            ->andReturn(0);

        $this->assertFalse($licence->hasApprovedUnfulfilledConditions());
    }

    /**
     * Note: We've already tested the isValid and isGoods in our other tests - so we can safely assume these
     * parts are valid and only check they work together against a list of licence types
     *
     * @dataProvider dpValidSiGoods
     */
    public function testIsValidSiGoods($licenceType, $expectedResult)
    {
        /** @var Entity $licence */
        $licence = $this->instantiate(Entity::class);
        $licence->setStatus(new RefData(Entity::LICENCE_STATUS_VALID));
        $licence->setGoodsOrPsv(new RefData(Entity::LICENCE_CATEGORY_GOODS_VEHICLE));
        $licence->setLicenceType(new RefData($licenceType));

        $this->assertEquals($expectedResult, $licence->isValidSiGoods());
    }

    public function dpValidSiGoods()
    {
        return [
            [Entity::LICENCE_TYPE_RESTRICTED, false],
            [Entity::LICENCE_TYPE_STANDARD_INTERNATIONAL, true],
            [Entity::LICENCE_TYPE_STANDARD_NATIONAL, false],
            [Entity::LICENCE_TYPE_SPECIAL_RESTRICTED, false],
        ];
    }

    /**
     * @dataProvider dpIsValid
     *
     * @param $status
     * @param $expectedResult
     */
    public function testIsValid($status, $expectedResult)
    {
        /** @var Entity $licence */
        $licence = $this->instantiate(Entity::class);
        $licence->setStatus(new RefData($status));

        $this->assertEquals($expectedResult, $licence->isValid());
    }

    public function dpIsValid()
    {
        return [
            [Entity::LICENCE_STATUS_SUSPENDED, true],
            [Entity::LICENCE_STATUS_VALID, true],
            [Entity::LICENCE_STATUS_CURTAILED, true],
            [Entity::LICENCE_STATUS_UNDER_CONSIDERATION, false],
            [Entity::LICENCE_STATUS_NOT_SUBMITTED, false],
            [Entity::LICENCE_STATUS_GRANTED, false],
            [Entity::LICENCE_STATUS_SURRENDERED, false],
            [Entity::LICENCE_STATUS_WITHDRAWN, false],
            [Entity::LICENCE_STATUS_REFUSED, false],
            [Entity::LICENCE_STATUS_REVOKED, false],
            [Entity::LICENCE_STATUS_NOT_TAKEN_UP, false],
            [Entity::LICENCE_STATUS_TERMINATED, false],
            [Entity::LICENCE_STATUS_CONTINUATION_NOT_SOUGHT, false],
            [Entity::LICENCE_STATUS_UNLICENSED, false],
            [Entity::LICENCE_STATUS_CANCELLED, false],
        ];
    }

    /**
     * eligible for permits check currently calls isValidGoods directly
     *
     * @dataProvider dpIsValidGoods
     */
    public function testIsValidGoodsAndIsEligibleForPermits($status, $goodsOrPsv, $expectedResult)
    {
        /** @var Entity $licence */
        $licence = $this->instantiate(Entity::class);
        $licence->setStatus(new RefData($status));
        $licence->setGoodsOrPsv(new RefData($goodsOrPsv));

        $this->assertEquals($expectedResult, $licence->isValidGoods());
        $this->assertEquals($expectedResult, $licence->isEligibleForPermits()); //should call isValidGoods
    }

    public function dpIsValidGoods()
    {
        return [
            //licence statuses with a GV licence
            [Entity::LICENCE_STATUS_SUSPENDED, Entity::LICENCE_CATEGORY_GOODS_VEHICLE, true],
            [Entity::LICENCE_STATUS_VALID, Entity::LICENCE_CATEGORY_GOODS_VEHICLE, true],
            [Entity::LICENCE_STATUS_CURTAILED, Entity::LICENCE_CATEGORY_GOODS_VEHICLE, true],
            [Entity::LICENCE_STATUS_UNDER_CONSIDERATION, Entity::LICENCE_CATEGORY_GOODS_VEHICLE, false],
            [Entity::LICENCE_STATUS_NOT_SUBMITTED, Entity::LICENCE_CATEGORY_GOODS_VEHICLE, false],
            [Entity::LICENCE_STATUS_GRANTED, Entity::LICENCE_CATEGORY_GOODS_VEHICLE, false],
            [Entity::LICENCE_STATUS_SURRENDERED, Entity::LICENCE_CATEGORY_GOODS_VEHICLE, false],
            [Entity::LICENCE_STATUS_WITHDRAWN, Entity::LICENCE_CATEGORY_GOODS_VEHICLE, false],
            [Entity::LICENCE_STATUS_REFUSED, Entity::LICENCE_CATEGORY_GOODS_VEHICLE, false],
            [Entity::LICENCE_STATUS_REVOKED, Entity::LICENCE_CATEGORY_GOODS_VEHICLE, false],
            [Entity::LICENCE_STATUS_NOT_TAKEN_UP, Entity::LICENCE_CATEGORY_GOODS_VEHICLE, false],
            [Entity::LICENCE_STATUS_TERMINATED, Entity::LICENCE_CATEGORY_GOODS_VEHICLE, false],
            [Entity::LICENCE_STATUS_CONTINUATION_NOT_SOUGHT, Entity::LICENCE_CATEGORY_GOODS_VEHICLE, false],
            [Entity::LICENCE_STATUS_UNLICENSED, Entity::LICENCE_CATEGORY_GOODS_VEHICLE, false],
            [Entity::LICENCE_STATUS_CANCELLED, Entity::LICENCE_CATEGORY_GOODS_VEHICLE, false],
            //licence statuses with a psv licence
            [Entity::LICENCE_STATUS_SUSPENDED, Entity::LICENCE_CATEGORY_PSV, false],
            [Entity::LICENCE_STATUS_VALID, Entity::LICENCE_CATEGORY_PSV, false],
            [Entity::LICENCE_STATUS_CURTAILED, Entity::LICENCE_CATEGORY_PSV, false],
            [Entity::LICENCE_STATUS_UNDER_CONSIDERATION, Entity::LICENCE_CATEGORY_PSV, false],
            [Entity::LICENCE_STATUS_NOT_SUBMITTED, Entity::LICENCE_CATEGORY_PSV, false],
            [Entity::LICENCE_STATUS_GRANTED, Entity::LICENCE_CATEGORY_PSV, false],
            [Entity::LICENCE_STATUS_SURRENDERED, Entity::LICENCE_CATEGORY_PSV, false],
            [Entity::LICENCE_STATUS_WITHDRAWN, Entity::LICENCE_CATEGORY_PSV, false],
            [Entity::LICENCE_STATUS_REFUSED, Entity::LICENCE_CATEGORY_PSV, false],
            [Entity::LICENCE_STATUS_REVOKED, Entity::LICENCE_CATEGORY_PSV, false],
            [Entity::LICENCE_STATUS_NOT_TAKEN_UP, Entity::LICENCE_CATEGORY_PSV, false],
            [Entity::LICENCE_STATUS_TERMINATED, Entity::LICENCE_CATEGORY_PSV, false],
            [Entity::LICENCE_STATUS_CONTINUATION_NOT_SOUGHT, Entity::LICENCE_CATEGORY_PSV, false],
            [Entity::LICENCE_STATUS_UNLICENSED, Entity::LICENCE_CATEGORY_PSV, false],
            [Entity::LICENCE_STATUS_CANCELLED, Entity::LICENCE_CATEGORY_PSV, false],
        ];
    }

    /**
     * @dataProvider dpZeroCoalesced
     */
    public function testGetTotAuthHgvVehiclesZeroCoalesced($totAuthHgvVehicles, $expected)
    {
        $licence = m::mock(Entity::class)->makePartial();
        $licence->shouldReceive('getTotAuthHgvVehicles')
            ->withNoArgs()
            ->andReturn($totAuthHgvVehicles);

        $this->assertEquals(
            $expected,
            $licence->getTotAuthHgvVehiclesZeroCoalesced()
        );
    }

    /**
     * @dataProvider dpZeroCoalesced
     */
    public function testGetTotAuthLgvVehiclesZeroCoalesced($totAuthLgvVehicles, $expected)
    {
        $licence = m::mock(Entity::class)->makePartial();
        $licence->shouldReceive('getTotAuthLgvVehicles')
            ->withNoArgs()
            ->andReturn($totAuthLgvVehicles);

        $this->assertEquals(
            $expected,
            $licence->getTotAuthLgvVehiclesZeroCoalesced()
        );
    }

    public function dpZeroCoalesced()
    {
        return [
            [8, 8],
            [null, 0],
        ];
    }

    /**
     * @dataProvider dpCanHaveLgv
     */
    public function testCanHaveLgv($vehicleType, $expected)
    {
        $licence = m::mock(Entity::class)->makePartial();
        $licence->setVehicleType(new RefData($vehicleType));

        $this->assertEquals(
            $expected,
            $licence->canHaveLgv()
        );
    }

    public function dpCanHaveLgv()
    {
        return [
            [RefData::APP_VEHICLE_TYPE_HGV, false],
            [RefData::APP_VEHICLE_TYPE_LGV, true],
            [RefData::APP_VEHICLE_TYPE_MIXED, true],
            [RefData::APP_VEHICLE_TYPE_PSV, false],
        ];
    }

    /**
     * @dataProvider dpMustHaveLgv
     */
    public function testMustHaveLgv($vehicleType, $expected)
    {
        $licence = m::mock(Entity::class)->makePartial();
        $licence->setVehicleType(new RefData($vehicleType));

        $this->assertEquals(
            $expected,
            $licence->mustHaveLgv()
        );
    }

    public function dpMustHaveLgv()
    {
        return [
            [RefData::APP_VEHICLE_TYPE_HGV, false],
            [RefData::APP_VEHICLE_TYPE_LGV, true],
            [RefData::APP_VEHICLE_TYPE_MIXED, false],
            [RefData::APP_VEHICLE_TYPE_PSV, false],
        ];
    }

    /**
     * @dataProvider dpIsLgv
     */
    public function testIsLgv($vehicleType, $expected)
    {
        $licence = m::mock(Entity::class)->makePartial();
        $licence->setVehicleType(new RefData($vehicleType));

        $this->assertEquals(
            $expected,
            $licence->isLgv()
        );
    }

    public function dpIsLgv()
    {
        return [
            [RefData::APP_VEHICLE_TYPE_HGV, false],
            [RefData::APP_VEHICLE_TYPE_LGV, true],
            [RefData::APP_VEHICLE_TYPE_MIXED, false],
            [RefData::APP_VEHICLE_TYPE_PSV, false],
        ];
    }

    /**
     * @dataProvider dpCanHaveHgv
     */
    public function testCanHaveHgv($vehicleType, $expected)
    {
        $licence = m::mock(Entity::class)->makePartial();
        $licence->setVehicleType(new RefData($vehicleType));

        $this->assertEquals(
            $expected,
            $licence->canHaveHgv()
        );
    }

    public function dpCanHaveHgv()
    {
        return [
            [RefData::APP_VEHICLE_TYPE_HGV, true],
            [RefData::APP_VEHICLE_TYPE_LGV, false],
            [RefData::APP_VEHICLE_TYPE_MIXED, true],
            [RefData::APP_VEHICLE_TYPE_PSV, true],
        ];
    }

    /**
     * @dataProvider dpCanHaveOperatingCentre
     */
    public function testCanHaveOperatingCentre($vehicleType, $expected)
    {
        $licence = m::mock(Entity::class)->makePartial();
        $licence->setVehicleType(new RefData($vehicleType));

        $this->assertEquals(
            $expected,
            $licence->canHaveOperatingCentre()
        );
    }

    public function dpCanHaveOperatingCentre()
    {
        return [
            [RefData::APP_VEHICLE_TYPE_HGV, true],
            [RefData::APP_VEHICLE_TYPE_LGV, false],
            [RefData::APP_VEHICLE_TYPE_MIXED, true],
            [RefData::APP_VEHICLE_TYPE_PSV, true],
        ];
    }

    /**
     * @dataProvider dpMustHaveOperatingCentre
     */
    public function testMustHaveOperatingCentre($vehicleType, $expected)
    {
        $licence = m::mock(Entity::class)->makePartial();
        $licence->setVehicleType(new RefData($vehicleType));

        $this->assertEquals(
            $expected,
            $licence->mustHaveOperatingCentre()
        );
    }

    public function dpMustHaveOperatingCentre()
    {
        return [
            [RefData::APP_VEHICLE_TYPE_HGV, true],
            [RefData::APP_VEHICLE_TYPE_LGV, false],
            [RefData::APP_VEHICLE_TYPE_MIXED, true],
            [RefData::APP_VEHICLE_TYPE_PSV, true],
        ];
    }

    /**
     * @dataProvider dpCanHaveTrailer
     */
    public function testCanHaveTrailer($vehicleType, $expected)
    {
        $licence = m::mock(Entity::class)->makePartial();
        $licence->setVehicleType(new RefData($vehicleType));

        $this->assertEquals(
            $expected,
            $licence->canHaveTrailer()
        );
    }

    public function dpCanHaveTrailer()
    {
        return [
            [RefData::APP_VEHICLE_TYPE_HGV, true],
            [RefData::APP_VEHICLE_TYPE_LGV, false],
            [RefData::APP_VEHICLE_TYPE_MIXED, true],
            [RefData::APP_VEHICLE_TYPE_PSV, false],
        ];
    }

    public function testIsGoods()
    {
        $goodsOrPsv = m::mock(RefData::class)->makePartial();
        $goodsOrPsv->setId(Entity::LICENCE_CATEGORY_GOODS_VEHICLE);

        /** @var Entity $licence */
        $licence = $this->instantiate(Entity::class);

        $this->assertNull($licence->isGoods());

        $licence->setGoodsOrPsv($goodsOrPsv);

        $this->assertTrue($licence->isGoods());
    }

    public function testIsGoodsFalse()
    {
        $goodsOrPsv = m::mock(RefData::class)->makePartial();
        $goodsOrPsv->setId(Entity::LICENCE_CATEGORY_PSV);

        /** @var Entity $licence */
        $licence = $this->instantiate(Entity::class);
        $licence->setGoodsOrPsv($goodsOrPsv);

        $this->assertFalse($licence->isGoods());
    }

    public function testIsPsv()
    {
        $goodsOrPsv = m::mock(RefData::class)->makePartial();
        $goodsOrPsv->setId(Entity::LICENCE_CATEGORY_PSV);

        /** @var Entity $licence */
        $licence = $this->instantiate(Entity::class);

        $this->assertNull($licence->isPsv());

        $licence->setGoodsOrPsv($goodsOrPsv);

        $this->assertTrue($licence->isPsv());
    }

    public function testIsPsvFalse()
    {
        $goodsOrPsv = m::mock(RefData::class)->makePartial();
        $goodsOrPsv->setId(Entity::LICENCE_CATEGORY_GOODS_VEHICLE);

        /** @var Entity $licence */
        $licence = $this->instantiate(Entity::class);
        $licence->setGoodsOrPsv($goodsOrPsv);

        $this->assertFalse($licence->isPsv());
    }

    public function testIsSpecialRestricted()
    {
        $licenceType = m::mock(RefData::class)->makePartial();
        $licenceType->setId(Entity::LICENCE_TYPE_SPECIAL_RESTRICTED);

        /** @var Entity $licence */
        $licence = $this->instantiate(Entity::class);

        $this->assertNull($licence->isSpecialRestricted());

        $licence->setLicenceType($licenceType);

        $this->assertTrue($licence->isSpecialRestricted());
    }

    public function testIsSpecialRestrictedFalse()
    {
        $licenceType = m::mock(RefData::class)->makePartial();
        $licenceType->setId(Entity::LICENCE_TYPE_STANDARD_NATIONAL);

        /** @var Entity $licence */
        $licence = $this->instantiate(Entity::class);
        $licence->setLicenceType($licenceType);

        $this->assertFalse($licence->isSpecialRestricted());
    }

    public function testGetTradingNameNone()
    {
        $licence = m::mock(Entity::class)->makePartial();
        $licence
            ->shouldReceive('getTradingNames->getIterator')
            ->once()
            ->andReturn([]);

        $this->assertEquals('None', $licence->getTradingName());
    }

    public function testGetTradingNamesSorting()
    {
        $tradingName1 = m::mock(TradingNameEntity::class)
            ->makePartial()
            ->setName('Foo')
            ->setCreatedOn('2015-06-01 00:00:00');
        $tradingName2 = m::mock(TradingNameEntity::class)
            ->makePartial()
            ->setName('Bar')
            ->setCreatedOn('2015-06-01 00:00:00');
        $tradingName3 = m::mock(TradingNameEntity::class)
            ->makePartial()
            ->setName('Baz')
            ->setCreatedOn('2015-07-01 00:00:00');
        $tradingNames = new ArrayCollection();
        $tradingNames->add($tradingName1);
        $tradingNames->add($tradingName2);
        $tradingNames->add($tradingName3);

        $licence = m::mock(Entity::class)->makePartial();
        $licence
            ->shouldReceive('getTradingNames')
            ->once()
            ->andReturn($tradingNames);

        $this->assertEquals('Bar', $licence->getTradingName());
    }

    public function testGetAllTradingNames()
    {
        $tradingName1 = m::mock(TradingNameEntity::class)
            ->makePartial()
            ->setName('Foo')
            ->setCreatedOn('2015-06-01 00:00:00');
        $tradingName2 = m::mock(TradingNameEntity::class)
            ->makePartial()
            ->setName('Bar')
            ->setCreatedOn('2015-06-01 00:00:00');
        $tradingName3 = m::mock(TradingNameEntity::class)
            ->makePartial()
            ->setName('Baz')
            ->setCreatedOn('2015-07-01 00:00:00');
        $tradingNames = new ArrayCollection();
        $tradingNames->add($tradingName1);
        $tradingNames->add($tradingName2);
        $tradingNames->add($tradingName3);

        $licence = m::mock(Entity::class)->makePartial();
        $licence
            ->shouldReceive('getTradingNames')
            ->once()
            ->andReturn($tradingNames);

        $this->assertEquals(['Bar', 'Foo', 'Baz'], $licence->getAllTradingNames());
    }

    public function testGetOpenComplaintsCount()
    {
        $case1 = m::mock(CaseEntity::class)->makePartial();
        $case2 = m::mock(CaseEntity::class)->makePartial();
        $cases = new ArrayCollection();
        $cases->add($case1);
        $cases->add($case2);

        $case1
            ->shouldReceive('getComplaints')
            ->andReturn(
                [
                    m::mock(ComplaintEntity::class)
                        ->makePartial()
                        ->shouldReceive('getIsCompliance')
                        ->andReturn(0)
                        ->shouldReceive('isOpen')
                        ->andReturn(true)
                        ->getMock()
                ]
            );
        $case2
            ->shouldReceive('getComplaints')
            ->andReturn(
                [
                    m::mock(ComplaintEntity::class)
                        ->makePartial()
                        ->shouldReceive('getIsCompliance')
                        ->andReturn(1)
                        ->shouldReceive('isOpen')
                        ->andReturn(true)
                        ->getMock()
                ]
            );

        $licence = m::mock(Entity::class)->makePartial();
        $licence->setCases($cases);

        $this->assertEquals(1, $licence->getOpenComplaintsCount());
    }

    public function testGetOpenCases()
    {
        $case1 = m::mock(CaseEntity::class)->makePartial();
        $case2 = m::mock(CaseEntity::class)->makePartial();
        $cases = new ArrayCollection();
        $cases->add($case1);
        $cases->add($case2);

        $case1
            ->shouldReceive('isOpen')
            ->andReturn(true);
        $case2
            ->shouldReceive('isOpen')
            ->andReturn(false);

        $licence = m::mock(Entity::class)->makePartial();
        $licence->setCases($cases);

        $this->assertEquals([$case1], $licence->getOpenCases());
    }

    public function testGetOcForInspectionRequest()
    {
        $mockOperatingCentre = m::mock()
            ->shouldReceive('getOperatingCentre')
            ->andReturn('oc')
            ->once()
            ->getMock();

        $mockLicence = m::mock(Entity::class)->makePartial();
        $mockLicence->shouldReceive('getOperatingCentres')
            ->andReturn([$mockOperatingCentre])
            ->once()
            ->getMock();

        $this->assertEquals(['oc'], $mockLicence->getOcForInspectionRequest());
    }

    public function testIsRestricted()
    {
        /** @var Entity $licence */
        $licence = $this->instantiate(Entity::class);

        $this->assertNull($licence->isRestricted());

        $licenceType = m::mock(RefData::class)->makePartial();
        $licenceType->setId(Entity::LICENCE_TYPE_STANDARD_NATIONAL);
        $licence->setLicenceType($licenceType);

        $this->assertFalse($licence->isRestricted());

        $licenceType = m::mock(RefData::class)->makePartial();
        $licenceType->setId(Entity::LICENCE_TYPE_RESTRICTED);
        $licence->setLicenceType($licenceType);

        $this->assertTrue($licence->isRestricted());
    }

    public function testIsStandardInternational()
    {
        /** @var Entity $licence */
        $licence = $this->instantiate(Entity::class);

        $this->assertNull($licence->isStandardInternational());

        $licenceType = m::mock(RefData::class)->makePartial();
        $licenceType->setId(Entity::LICENCE_TYPE_STANDARD_NATIONAL);
        $licence->setLicenceType($licenceType);

        $this->assertFalse($licence->isStandardInternational());

        $licenceType = m::mock(RefData::class)->makePartial();
        $licenceType->setId(Entity::LICENCE_TYPE_STANDARD_INTERNATIONAL);
        $licence->setLicenceType($licenceType);

        $this->assertTrue($licence->isStandardInternational());
    }

    public function testIsStandardNational()
    {
        /** @var Entity $licence */
        $licence = $this->instantiate(Entity::class);

        $this->assertNull($licence->isStandardNational());

        $licenceType = m::mock(RefData::class)->makePartial();
        $licenceType->setId(Entity::LICENCE_TYPE_STANDARD_INTERNATIONAL);
        $licence->setLicenceType($licenceType);

        $this->assertFalse($licence->isStandardNational());

        $licenceType = m::mock(RefData::class)->makePartial();
        $licenceType->setId(Entity::LICENCE_TYPE_STANDARD_NATIONAL);
        $licence->setLicenceType($licenceType);

        $this->assertTrue($licence->isStandardNational());
    }

    public function testCanHaveCommunityLicencesStandardInt()
    {
        /** @var Entity $licence */
        $licence = $this->instantiate(Entity::class);

        $licenceType = m::mock(RefData::class)->makePartial();
        $licenceType->setId(Entity::LICENCE_TYPE_STANDARD_INTERNATIONAL);
        $licence->setLicenceType($licenceType);

        $this->assertTrue($licence->canHaveCommunityLicences());
    }

    public function testCanHaveCommunityLicencesPsvRestricted()
    {
        /** @var Entity $licence */
        $licence = $this->instantiate(Entity::class);

        $goodsOrPsv = m::mock(RefData::class)->makePartial();
        $goodsOrPsv->setId(Entity::LICENCE_CATEGORY_PSV);
        $licence->setGoodsOrPsv($goodsOrPsv);

        $licenceType = m::mock(RefData::class)->makePartial();
        $licenceType->setId(Entity::LICENCE_TYPE_RESTRICTED);
        $licence->setLicenceType($licenceType);

        $this->assertTrue($licence->canHaveCommunityLicences());
    }

    public function testCanHaveCommunityLicencesSomethingElse()
    {
        /** @var Entity $licence */
        $licence = $this->instantiate(Entity::class);

        $goodsOrPsv = m::mock(RefData::class)->makePartial();
        $goodsOrPsv->setId(Entity::LICENCE_CATEGORY_GOODS_VEHICLE);
        $licence->setGoodsOrPsv($goodsOrPsv);

        $licenceType = m::mock(RefData::class)->makePartial();
        $licenceType->setId(Entity::LICENCE_TYPE_STANDARD_NATIONAL);
        $licence->setLicenceType($licenceType);

        $this->assertFalse($licence->canHaveCommunityLicences());
    }

    /**
     * @test
     */
    public function copyInformationFromApplicationIsCallable()
    {
        // Setup
        $this->setUpSut();

        // Assert
        $this->assertIsCallable([$this->sut, 'copyInformationFromApplication']);
    }

    /**
     * @depends copyInformationFromApplicationIsCallable
     */
    public function testCopyInformationFromNewApplication()
    {
        /** @var Application $application */
        $application = m::mock(Application::class)->makePartial();

        $licenceType = m::mock(RefData::class)->makePartial();
        $licenceType->setId(Entity::LICENCE_TYPE_STANDARD_NATIONAL);
        $application->setLicenceType($licenceType);
        $vehicleType = m::mock(RefData::class)->makePartial();
        $vehicleType->setId(RefData::APP_VEHICLE_TYPE_HGV);
        $application->setVehicleType($vehicleType);
        $application->setLgvDeclarationConfirmation(true);
        $application->shouldReceive('isVariation')->once()->withNoArgs()->andReturn(false);

        $goodsOrPsv = m::mock(RefData::class)->makePartial();
        $goodsOrPsv->setId(Entity::LICENCE_CATEGORY_GOODS_VEHICLE);
        $application->setGoodsOrPsv($goodsOrPsv);

        $application->setTotAuthTrailers(9);
        $application->updateTotAuthHgvVehicles(12);

        /** @var Entity $licence */
        $licence = $this->instantiate(Entity::class);

        $licence->copyInformationFromApplication($application);

        $this->assertSame($licenceType, $licence->getLicenceType());
        $this->assertSame($vehicleType, $licence->getVehicleType());
        $this->assertTrue($licence->getLgvDeclarationConfirmation());
        $this->assertSame($goodsOrPsv, $licence->getGoodsOrPsv());
        $this->assertEquals(9, $licence->getTotAuthTrailers());
        $this->assertEquals(12, $licence->getTotAuthVehicles());
    }

    /**
     * @test
     * @depends copyInformationFromApplicationIsCallable
     */
    public function copyInformationFromNewApplicationSetsHgvTotalForApplication()
    {
        // Setup
        $this->setUpSut();
        $application = $this->applicationForNewLicence();
        $application->setTotAuthHgvVehicles($expectedNumber = static::A_NUMBER_OF_VEHICLES);

        // Execute
        $this->sut->copyInformationFromApplication($application);

        // Assert
        $this->assertSame($expectedNumber, $this->sut->getTotAuthHgvVehicles());
    }

    /**
     * @test
     * @depends copyInformationFromApplicationIsCallable
     */
    public function copyInformationFromNewApplicationSetsLgvTotalForApplication()
    {
        // Setup
        $this->setUpSut();
        $application = $this->applicationForNewLicence();
        $application->setTotAuthLgvVehicles($expectedNumber = static::A_NUMBER_OF_VEHICLES);

        // Execute
        $this->sut->copyInformationFromApplication($application);

        // Assert
        $this->assertSame($expectedNumber, $this->sut->getTotAuthLgvVehicles());
    }

    /**
     * @depends copyInformationFromApplicationIsCallable
     */
    public function testCopyInformationFromVariationApplication()
    {
        $appCompletion = m::mock(ApplicationCompletion::class);
        $appCompletion->shouldReceive('variationSectionUpdated')->with('typeOfLicence')->once()->andReturn(true);
        $appCompletion->shouldReceive('variationSectionUpdated')->with('operatingCentres')->once()->andReturn(true);

        $licenceType = m::mock(RefData::class);
        $vehicleType = m::mock(RefData::class);
        $goodsOrPsv = m::mock(RefData::class);
        $totAuthTrailers = 9;
        $totAuthVehicles = 12;

        $application = m::mock(Application::class);
        $application->shouldIgnoreMissing();
        $application->shouldReceive('isVariation')->once()->withNoArgs()->andReturn(true);
        $application->shouldReceive('getApplicationCompletion')->once()->withNoArgs()->andReturn($appCompletion);
        $application->shouldReceive('getLicenceType')->once()->withNoArgs()->andReturn($licenceType);
        $application->shouldReceive('getVehicleType')->once()->withNoArgs()->andReturn($vehicleType);
        $application->shouldReceive('getLgvDeclarationConfirmation')->once()->withNoArgs()->andReturnTrue();
        $application->shouldReceive('getGoodsOrPsv')->once()->withNoArgs()->andReturn($goodsOrPsv);
        $application->shouldReceive('getTotAuthTrailers')->once()->withNoArgs()->andReturn($totAuthTrailers);
        $application->allows('getTotAuthVehicles')->andReturn($totAuthVehicles);

        /** @var Entity $licence */
        $licence = $this->instantiate(Entity::class);

        $licence->copyInformationFromApplication($application);

        $this->assertSame($licenceType, $licence->getLicenceType());
        $this->assertSame($vehicleType, $licence->getVehicleType());
        $this->assertTrue($licence->getLgvDeclarationConfirmation());
        $this->assertSame($goodsOrPsv, $licence->getGoodsOrPsv());
        $this->assertEquals($totAuthTrailers, $licence->getTotAuthTrailers());
        $this->assertEquals($totAuthVehicles, $licence->getTotAuthVehicles());
    }

    /**
     * @depends copyInformationFromApplicationIsCallable
     */
    public function testCopyInformationFromUnchangedVariationApplication()
    {
        $appCompletion = m::mock(ApplicationCompletion::class);
        $appCompletion->shouldReceive('variationSectionUpdated')->with('typeOfLicence')->once()->andReturn(false);
        $appCompletion->shouldReceive('variationSectionUpdated')->with('operatingCentres')->once()->andReturn(false);

        $goodsOrPsv = m::mock(RefData::class);

        $application = m::mock(Application::class);
        $application->shouldReceive('isVariation')->once()->withNoArgs()->andReturn(true);
        $application->shouldReceive('getApplicationCompletion')->once()->withNoArgs()->andReturn($appCompletion);
        $application->shouldReceive('getGoodsOrPsv')->once()->withNoArgs()->andReturn($goodsOrPsv);
        $application->shouldReceive('getLicenceType')->never();
        $application->shouldReceive('getVehicleType')->never();
        $application->shouldReceive('getLgvDeclarationConfirmation')->never();
        $application->shouldReceive('getTotAuthTrailers')->never();
        $application->shouldReceive('getTotAuthVehicles')->never();

        $originalLicenceType = m::mock(RefData::class);
        $originalVehicleType = m::mock(RefData::class);
        $originalTotAuthTrailers = 6;
        $originalTotAuthVehicles = 10;

        /** @var Entity $licence */
        $licence = $this->instantiate(Entity::class);
        $licence->setLicenceType($originalLicenceType);
        $licence->setVehicleType($originalVehicleType);
        $licence->setLgvDeclarationConfirmation(false);
        $licence->setTotAuthTrailers($originalTotAuthTrailers);
        $licence->updateTotAuthHgvVehicles($originalTotAuthVehicles);

        $licence->copyInformationFromApplication($application);

        //only goodsOrPsv should have changed
        $this->assertSame($originalLicenceType, $licence->getLicenceType());
        $this->assertSame($originalVehicleType, $licence->getVehicleType());
        $this->assertFalse($licence->getLgvDeclarationConfirmation());
        $this->assertSame($goodsOrPsv, $licence->getGoodsOrPsv());
        $this->assertEquals($originalTotAuthTrailers, $licence->getTotAuthTrailers());
        $this->assertEquals($originalTotAuthVehicles, $licence->getTotAuthVehicles());
    }

    /**
     * @test
     * @depends copyInformationFromApplicationIsCallable
     */
    public function copyInformationFromNewApplicationSetsHgvTotalForVariationWithUpdatedOperatingCentres()
    {
        // Setup
        $this->setUpSut();
        $variation = $this->variationWithUpdatedOperatingCentres();
        $variation->setTotAuthHgvVehicles($expectedNumber = static::A_NUMBER_OF_VEHICLES);

        // Execute
        $this->sut->copyInformationFromApplication($variation);

        // Assert
        $this->assertSame($expectedNumber, $this->sut->getTotAuthHgvVehicles());
    }

    /**
     * @test
     * @depends copyInformationFromApplicationIsCallable
     */
    public function copyInformationFromNewApplicationSetsLgvTotalForVariationWithUpdatedOperatingCentres()
    {
        // Setup
        $this->setUpSut();
        $variation = $this->variationWithUpdatedOperatingCentres();
        $variation->setTotAuthLgvVehicles($expectedNumber = static::A_NUMBER_OF_VEHICLES);

        // Execute
        $this->sut->copyInformationFromApplication($variation);

        // Assert
        $this->assertSame($expectedNumber, $this->sut->getTotAuthLgvVehicles());
    }

    public function testGetPsvDiscsNotCeased()
    {
        $psvDiscsCollection = m::mock(ArrayCollection::class);
        $psvDiscsNotCeasedCollection = m::mock(ArrayCollection::class);

        $psvDiscsCollection->shouldReceive('matching')->once()->with(m::type(Criteria::class))->andReturnUsing(
            function (Criteria $criteria) use ($psvDiscsNotCeasedCollection) {
                $expectedCriteria = Criteria::create()
                    ->where(Criteria::expr()->isNull('ceasedDate'));

                $this->assertEquals($expectedCriteria, $criteria);

                return $psvDiscsNotCeasedCollection;
            }
        );

        $licence = $this->instantiate(Entity::class);

        $licence->setPsvDiscs($psvDiscsCollection);
        $this->assertSame($psvDiscsNotCeasedCollection, $licence->getPsvDiscsNotCeased());
    }

    /**
     * @dataProvider dpCanHaveVariationProvider
     */
    public function testCanHaveVariation($status, $expected)
    {
        $licence = m::mock(Entity::class)->makePartial();

        $licence->shouldReceive('getStatus->getId')
            ->andReturn($status);

        $this->assertEquals($licence->canHaveVariation(), $expected);
    }

    public function dpCanHaveVariationProvider()
    {
        return [
            [
                Entity::LICENCE_STATUS_VALID,
                true
            ],
            [
                Entity::LICENCE_STATUS_SURRENDERED,
                false
            ],
            [
                Entity::LICENCE_STATUS_REVOKED,
                false
            ],
            [
                Entity::LICENCE_STATUS_TERMINATED,
                false
            ]
        ];
    }

    /**
     * @param string $categoryId 'lcat_psv'|'lcat_gv'
     * @param string $expected 'O'|'P'
     * @dataProvider categoryPrefixDp
     */
    public function testGetCategoryPrefix($categoryId, $expected)
    {
        $category = new RefData($categoryId);

        $licence = $this->instantiate(Entity::class);
        $licence->setGoodsOrPsv($category);

        $this->assertEquals($expected, $licence->getCategoryPrefix());
    }

    public function categoryPrefixDp()
    {
        return [
            [Entity::LICENCE_CATEGORY_PSV, 'P'],
            [Entity::LICENCE_CATEGORY_GOODS_VEHICLE, 'O'],
        ];
    }

    public function testGetRemainingSpacesPsv()
    {
        $licence = m::mock(Entity::class)->makePartial()
            ->shouldReceive('getTotAuthVehicles')
            ->andReturn(2)
            ->once()
            ->shouldReceive('getPsvDiscsNotCeased')
            ->andReturn(new \ArrayObject([1, 2, 3]))
            ->once()
            ->getMock();

        $this->assertEquals(-1, $licence->getRemainingSpacesPsv());
    }

    /**
     * @dataProvider dpTestGetAuthorisations
     */
    public function testGetAuthorisations($vehicleTypeId, $licenceTypeId, $expectedAuthorisations)
    {
        $licence = m::mock(Entity::class)->makePartial();

        $licence->setTotAuthVehicles(7);
        $licence->setTotAuthHgvVehicles(4);
        $licence->setTotAuthLgvVehicles(3);
        $licence->setTotAuthTrailers(1);

        $vehicleTypeRefdata = new RefData($vehicleTypeId);
        $licence->setVehicleType($vehicleTypeRefdata);

        $licenceTypeRefdata = new RefData($licenceTypeId);
        $licence->setLicenceType($licenceTypeRefdata);

        $this->assertEquals(
            $expectedAuthorisations,
            $licence->getAuthorisations()
        );
    }

    public function dpTestGetAuthorisations()
    {
        return [
            'goods/standard international/lgv' => [
                RefData::APP_VEHICLE_TYPE_LGV,
                Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                [
                    Entity::AUTHORISATION_LGV_COUNT => 3,
                ],
            ],
            'goods/standard international/mixed' => [
                RefData::APP_VEHICLE_TYPE_MIXED,
                Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                [
                    Entity::AUTHORISATION_HGV_COUNT => 4,
                    Entity::AUTHORISATION_LGV_COUNT => 3,
                    Entity::AUTHORISATION_TRAILER_COUNT => 1,
                ],
            ],
            'goods/other' => [
                RefData::APP_VEHICLE_TYPE_HGV,
                Licence::LICENCE_TYPE_STANDARD_NATIONAL,
                [
                    Entity::AUTHORISATION_VEHICLE_COUNT => 7,
                    Entity::AUTHORISATION_TRAILER_COUNT => 1,
                ],
            ],
            'psv/special restricted' => [
                RefData::APP_VEHICLE_TYPE_PSV,
                Licence::LICENCE_TYPE_SPECIAL_RESTRICTED,
                [],
            ],
            'psv/other' => [
                RefData::APP_VEHICLE_TYPE_PSV,
                Licence::LICENCE_TYPE_STANDARD_NATIONAL,
                [
                    Entity::AUTHORISATION_VEHICLE_COUNT => 7,
                ],
            ],
        ];
    }

    public function testGetAuthorisationsNullVehicleType()
    {
        $licence = m::mock(Entity::class)->makePartial();

        $this->assertEquals(
            [],
            $licence->getAuthorisations()
        );
    }

    /**
     * @dataProvider dpTestGetLicenceTypeShortCode
     * @param string $licenceType
     * @param string $shortCode
     */
    public function testGetLicenceTypeShortCode($licenceType, $shortCode)
    {
        $licence = m::mock(Entity::class)->makePartial();
        $licence->setLicenceType((new RefData())->setId($licenceType));

        $this->assertSame($shortCode, $licence->getLicenceTypeShortCode());
    }

    public function dpTestGetLicenceTypeShortCode()
    {
        return [
            ['ltyp_r', 'R'],
            ['ltyp_si', 'SI'],
            ['ltyp_sn', 'SN'],
            ['ltyp_sr', 'SR'],
            ['ltyp_cbp', 'CBP'],
            ['ltyp_dbp', 'DBP'],
            ['ltyp_lbp', 'LBP'],
            ['ltyp_sbp', 'SBP'],
            ['XXXX', null],
        ];
    }

    public function testGetContextValue()
    {
        $entity = $this->instantiate(Entity::class);
        $entity->setLicNo(111);

        $this->assertEquals(111, $entity->getContextValue());
    }

    public function testGetVariations()
    {
        $licence = $this->instantiate(Entity::class);
        $app1 = new Application($licence, $this->getRefData(Application::APPLICATION_STATUS_UNDER_CONSIDERATION), 0);
        $app1->setIsVariation(true);
        $app1->setId(1);
        $app2 = new Application($licence, $this->getRefData(Application::APPLICATION_STATUS_UNDER_CONSIDERATION), 0);
        $app2->setIsVariation(false);
        $app2->setId(2);
        $licence->setApplications(new ArrayCollection([$app1, $app2]));

        $this->assertEquals(1, $licence->getVariations()[0]->getId());
    }

    public function testGetNewApplications()
    {
        $licence = $this->instantiate(Entity::class);
        $app1 = new Application($licence, $this->getRefData(Application::APPLICATION_STATUS_UNDER_CONSIDERATION), 0);
        $app1->setIsVariation(false);
        $app1->setId(1);
        $app2 = new Application($licence, $this->getRefData(Application::APPLICATION_STATUS_UNDER_CONSIDERATION), 0);
        $app2->setIsVariation(true);
        $app2->setId(2);
        $licence->setApplications(new ArrayCollection([$app1, $app2]));

        $this->assertEquals(1, $licence->getNewApplications()[0]->getId());
    }

    public function testIsRelatedToApplicationTrue(): void
    {
        $licence = $this->instantiate(Entity::class);
        $appId = 999;

        $app1 = m::mock(Application::class);
        $app1->expects('getId')->withNoArgs()->andReturn(888);

        $app2 = m::mock(Application::class);
        $app2->expects('getId')->withNoArgs()->andReturn($appId);

        $licence->setApplications(new ArrayCollection([$app1, $app2]));

        $this->assertTrue($licence->isRelatedToApplication($appId));
    }

    public function testIsRelatedToApplicationFalse(): void
    {
        $licence = $this->instantiate(Entity::class);

        $app1 = m::mock(Application::class);
        $app1->expects('getId')->withNoArgs()->andReturn(888);

        $app2 = m::mock(Application::class);
        $app2->expects('getId')->withNoArgs()->andReturn(777);

        $licence->setApplications(new ArrayCollection([$app1, $app2]));

        $this->assertFalse($licence->isRelatedToApplication(999));
    }

    protected function getRefData($id)
    {
        $refData = new RefData();
        $refData->setId($id);

        return $refData;
    }

    public function testGetCalculatedBundleValues()
    {
        $licence = m::mock(Entity::class)->makePartial();
        $licence->shouldReceive('getNiFlag')->withNoArgs()->once()->andReturn('Y');

        $result = $licence->getCalculatedBundleValues();

        $expected = [
            'niFlag' => 'Y',
        ];

        $this->assertSame($expected, $result);
    }

    public function testGetCalculatedValues()
    {
        $licence = m::mock(Entity::class)->makePartial();

        $this->assertSame($licence->getCalculatedBundleValues(), $licence->getCalculatedValues());
    }

    public function testGetApplicationDocuments()
    {
        $licence = m::mock(Entity::class)->makePartial();
        $mockDocument1 = m::mock()
            ->shouldReceive('getcategory')
            ->andReturn('category')
            ->once()
            ->shouldReceive('getsubCategory')
            ->andReturn('subCategory')
            ->once()
            ->getMock();

        $mockDocument2 = m::mock()
            ->shouldReceive('getcategory')
            ->andReturn('category1')
            ->once()
            ->shouldReceive('getsubCategory')
            ->andReturn('subCategory1')
            ->never()
            ->getMock();

        $documentsCollection = new ArrayCollection([$mockDocument1, $mockDocument2]);
        $expected = new ArrayCollection([$mockDocument1]);

        $licence->setDocuments($documentsCollection);
        $this->assertEquals($expected, $licence->getLicenceDocuments('category', 'subCategory'));
    }

    /**
     * @dataProvider dpGetOutstandingOrganisations
     */
    public function testGetOutstandingOrganisations($status, $includeNotSubmitted, $expected)
    {
        $application = m::mock(Application::class)->makePartial();
        $application->shouldReceive('getStatus')
            ->andReturn(new RefData($status));

        $applications = new ArrayCollection();
        $applications->add($application);

        $licence = m::mock(Entity::class)->makePartial();
        $licence->setApplications($applications);

        $this->assertEquals($expected, $licence->getOutstandingApplications($includeNotSubmitted)->contains($application));
    }

    public function dpGetOutstandingOrganisations()
    {
        return [
            // exclude Not Submitted
            [Application::APPLICATION_STATUS_NOT_SUBMITTED, false, false],
            [Application::APPLICATION_STATUS_GRANTED, false, true],
            [Application::APPLICATION_STATUS_UNDER_CONSIDERATION, false, true],
            [Application::APPLICATION_STATUS_VALID, false, false],
            [Application::APPLICATION_STATUS_WITHDRAWN, false, false],
            [Application::APPLICATION_STATUS_REFUSED, false, false],
            [Application::APPLICATION_STATUS_NOT_TAKEN_UP, false, false],
            [Application::APPLICATION_STATUS_CURTAILED, false, false],
            [Application::APPLICATION_STATUS_CANCELLED, false, false],

            // include Not Submitted
            [Application::APPLICATION_STATUS_NOT_SUBMITTED, true, true],
            [Application::APPLICATION_STATUS_GRANTED, true, true],
            [Application::APPLICATION_STATUS_UNDER_CONSIDERATION, true, true],
            [Application::APPLICATION_STATUS_VALID, true, false],
            [Application::APPLICATION_STATUS_WITHDRAWN, true, false],
            [Application::APPLICATION_STATUS_REFUSED, true, false],
            [Application::APPLICATION_STATUS_NOT_TAKEN_UP, true, false],
            [Application::APPLICATION_STATUS_CURTAILED, true, false],
            [Application::APPLICATION_STATUS_CANCELLED, true, false],
        ];
    }

    /**
     * @dataProvider firstApplicationIdProvider
     */
    public function testGetFirstApplicationId($status, $firstApplicationId)
    {
        $licence = m::mock(Entity::class)->makePartial();
        $licence->shouldReceive('getStatus')
            ->andReturn(
                m::mock()
                ->shouldReceive('getId')
                ->once()
                ->andReturn($status)
                ->getMock()
            )
            ->once()
            ->getMock();
        $application = m::mock(Entity::class)->makePartial();
        $application->shouldReceive('getId')
            ->andReturn($firstApplicationId)
            ->getMock();

        $applications = new ArrayCollection();
        $applications->add($application);
        $licence->setApplications($applications);
        $this->assertEquals($firstApplicationId, $licence->getFirstApplicationId());
    }

    public function firstApplicationIdProvider()
    {
        return [
            [Entity::LICENCE_STATUS_NOT_SUBMITTED, 1],
            [Entity::LICENCE_STATUS_VALID, null],
        ];
    }

    public function testGetApplicationsByStatus()
    {
        $applications = new ArrayCollection();

        $appNotSubmitted = m::mock(Application::class)->makePartial();
        $appNotSubmitted->shouldReceive('getStatus')->andReturn(new RefData(Application::APPLICATION_STATUS_NOT_SUBMITTED));
        $applications->add($appNotSubmitted);

        $appGranted = m::mock(Application::class)->makePartial();
        $appGranted->shouldReceive('getStatus')->andReturn(new RefData(Application::APPLICATION_STATUS_GRANTED));
        $applications->add($appGranted);

        $appUnderConsideration = m::mock(Application::class)->makePartial();
        $appUnderConsideration->shouldReceive('getStatus')->andReturn(new RefData(Application::APPLICATION_STATUS_UNDER_CONSIDERATION));
        $applications->add($appUnderConsideration);

        $appValid = m::mock(Application::class)->makePartial();
        $appValid->shouldReceive('getStatus')->andReturn(new RefData(Application::APPLICATION_STATUS_VALID));
        $applications->add($appValid);

        $licence = m::mock(Entity::class)->makePartial();
        $licence->setApplications($applications);

        $this->assertEquals(
            [$appNotSubmitted, $appGranted, $appUnderConsideration, $appValid],
            $licence->getApplicationsByStatus(
                [
                    Application::APPLICATION_STATUS_NOT_SUBMITTED,
                    Application::APPLICATION_STATUS_GRANTED,
                    Application::APPLICATION_STATUS_UNDER_CONSIDERATION,
                    Application::APPLICATION_STATUS_VALID
                ]
            )->getValues()
        );

        $this->assertEquals(
            [$appGranted, $appValid],
            $licence->getApplicationsByStatus(
                [
                    Application::APPLICATION_STATUS_GRANTED,
                    Application::APPLICATION_STATUS_VALID
                ]
            )->getValues()
        );

        $this->assertEquals(
            [$appValid],
            $licence->getApplicationsByStatus(
                [
                    Application::APPLICATION_STATUS_VALID
                ]
            )->getValues()
        );
    }

    public function testGetTrafficAreaForTaskAllocationNonMlh()
    {
        $licence = m::mock(Entity::class)->makePartial()
            ->shouldReceive('getOrganisation')
            ->andReturn(
                m::mock()
                    ->shouldReceive('isMlh')
                    ->andReturn(false)
                    ->once()
                    ->getMock()
            )
            ->once()
            ->shouldReceive('getTrafficArea')
            ->andReturn('B')
            ->once()
            ->shouldReceive('isGoods')
            ->andReturn(null)
            ->once()
            ->shouldReceive('isGoodsApplication')
            ->andReturn(true)
            ->once()
            ->getMock();

        $this->assertEquals('B', $licence->getTrafficAreaForTaskAllocation());
    }

    public function testGetTrafficAreaForTaskAllocationGoodsMlh()
    {
        $licence = m::mock(Entity::class)->makePartial()
            ->shouldReceive('getOrganisation')
            ->andReturn(
                m::mock()
                    ->shouldReceive('isMlh')
                    ->andReturn(true)
                    ->once()
                    ->shouldReceive('getLeadTcArea')
                    ->andReturn('B')
                    ->twice()
                    ->getMock()
            )
            ->once()
            ->shouldReceive('isGoods')
            ->andReturn(true)
            ->once()
            ->getMock();

        $this->assertEquals('B', $licence->getTrafficAreaForTaskAllocation());
    }

    public function testGetTrafficAreaForTaskAllocationGoodsMlhLeadTcEmpty()
    {
        $licence = m::mock(Entity::class)->makePartial()
            ->shouldReceive('getOrganisation')
            ->andReturn(
                m::mock()
                    ->shouldReceive('isMlh')
                    ->andReturn(true)
                    ->once()
                    ->shouldReceive('getLeadTcArea')
                    ->andReturn(null)
                    ->once()
                    ->getMock()
            )
            ->once()
            ->shouldReceive('getTrafficArea')
            ->andReturn('B')
            ->once()
            ->shouldReceive('isGoods')
            ->andReturn(null)
            ->once()
            ->shouldReceive('isGoodsApplication')
            ->andReturn(true)
            ->once()
            ->getMock();

        $this->assertEquals('B', $licence->getTrafficAreaForTaskAllocation());
    }

    public function testIsGoodsApplication()
    {
        $application = m::mock()
            ->shouldReceive('isGoods')
            ->andReturn(true)
            ->getMock();

        $applications = new ArrayCollection();
        $applications->add($application);

        $licence = m::mock(Entity::class)->makePartial();
        $licence->shouldReceive('getStatus')
            ->andReturn(
                m::mock()
                    ->shouldReceive('getId')
                    ->once()
                    ->andReturn(Entity::LICENCE_STATUS_NOT_SUBMITTED)
                    ->getMock()
            )
            ->once()
            ->shouldReceive('getApplications')
            ->andReturn($applications)
            ->once()
            ->getMock();

        $this->assertTrue($licence->isGoodsApplication());
    }

    /**
     * @dataProvider expiryDateProvider
     */
    public function testGetExpiryDateAsDate($expiryDate, $expected)
    {
        /** @var Entity $sut */
        $sut = m::mock(Entity::class)->makePartial()
            ->shouldReceive('getExpiryDate')
            ->andReturn($expiryDate)
            ->once()
            ->getMock();

        $this->assertEquals($sut->getExpiryDateAsDate(), $expected);
    }

    public function expiryDateProvider()
    {
        $today = new DateTime('now');
        $date2017 = \DateTime::createFromFormat('Y-m-d', '2017-01-01');
        $date2017->setTime(0, 0, 0);
        $date2017->setTimezone(new \DateTimeZone(date_default_timezone_get()));
        return [
            [
                null,
                null
            ],
            [
                $today,
                $today
            ],
            [
                '2017-01-01',
                $date2017
            ]
        ];
    }

    public function testGetLatestBusVariation()
    {
        $collection = new ArrayCollection();

        $busReg1 = m::mock(BusRegEntity::class)->makePartial();
        $busReg1->setRegNo('1234567');
        $busReg1->setVariationNo(1);
        $busReg1->setStatus(new RefData(BusRegEntity::STATUS_NEW));
        $collection->add($busReg1);

        $busReg2 = m::mock(BusRegEntity::class)->makePartial();
        $busReg2->setRegNo('1234567');
        $busReg2->setVariationNo(2);
        $busReg2->setStatus(new RefData(BusRegEntity::STATUS_VAR));
        $collection->add($busReg2);

        $busReg3 = m::mock(BusRegEntity::class)->makePartial();
        $busReg3->setRegNo('1234567');
        $busReg3->setVariationNo(3);
        $busReg3->setStatus(new RefData(BusRegEntity::STATUS_REFUSED));
        $collection->add($busReg3);

        $busReg4 = m::mock(BusRegEntity::class)->makePartial();
        $busReg4->setRegNo('1234567');
        $busReg4->setVariationNo(4);
        $busReg4->setStatus(new RefData(BusRegEntity::STATUS_WITHDRAWN));
        $collection->add($busReg4);

        $busReg5 = m::mock(BusRegEntity::class)->makePartial();
        $busReg5->setRegNo('1234567');
        $busReg5->setVariationNo(5);
        $busReg5->setStatus(new RefData(BusRegEntity::STATUS_EXPIRED));
        $collection->add($busReg5);

        $busReg6 = m::mock(BusRegEntity::class)->makePartial();
        $busReg6->setRegNo('123');
        $busReg6->setVariationNo(1);
        $busReg6->setStatus(new RefData(BusRegEntity::STATUS_NEW));
        $collection->add($busReg6);

        $busReg7 = m::mock(BusRegEntity::class)->makePartial();
        $busReg7->setRegNo('123');
        $busReg7->setVariationNo(2);
        $busReg7->setStatus(new RefData(BusRegEntity::STATUS_EXPIRED));
        $collection->add($busReg7);

        /** @var Entity|\Mockery\MockInterface $licence */
        $licence = m::mock(Entity::class)->makePartial();
        $licence->shouldReceive('getBusRegs')->andReturn($collection);

        $this->assertEquals($busReg2, $licence->getLatestBusVariation('1234567'));
        $this->assertEquals($busReg5, $licence->getLatestBusVariation('1234567', []));
        $this->assertEquals(
            $busReg4,
            $licence->getLatestBusVariation('1234567', [BusRegEntity::STATUS_EXPIRED])
        );
        $this->assertEquals(
            $busReg3,
            $licence->getLatestBusVariation(
                '1234567',
                [
                    BusRegEntity::STATUS_EXPIRED,
                    BusRegEntity::STATUS_WITHDRAWN,
                ]
            )
        );
        $this->assertEquals(
            $busReg2,
            $licence->getLatestBusVariation(
                '1234567',
                [
                    BusRegEntity::STATUS_EXPIRED,
                    BusRegEntity::STATUS_WITHDRAWN,
                    BusRegEntity::STATUS_REFUSED,
                ]
            )
        );
        $this->assertEquals(
            $busReg1,
            $licence->getLatestBusVariation(
                '1234567',
                [
                    BusRegEntity::STATUS_EXPIRED,
                    BusRegEntity::STATUS_WITHDRAWN,
                    BusRegEntity::STATUS_REFUSED,
                    BusRegEntity::STATUS_VAR,
                ]
            )
        );

        $this->assertEquals($busReg6, $licence->getLatestBusVariation('123'));
        $this->assertEquals($busReg7, $licence->getLatestBusVariation('123', []));
        $this->assertEquals($busReg6, $licence->getLatestBusVariation('123', [BusRegEntity::STATUS_EXPIRED]));
        $this->assertEquals($busReg7, $licence->getLatestBusVariation('123', [BusRegEntity::STATUS_REFUSED]));
    }

    /**
     * @dataProvider operatorLocationProvider
     */
    public function testGetOperatorLocation($niFlag, $expected)
    {
        $mockLicence = m::mock(Entity::class)->makePartial();
        $mockLicence->shouldReceive('getNiFlag')
            ->andReturn($niFlag)
            ->once()
            ->getMock();

        $this->assertEquals($expected, $mockLicence->getOperatorLocation());
    }

    public function operatorLocationProvider()
    {
        return [
            [
                'Y', 'Northern Ireland'
            ],
            [
                'N', 'Great Britain'
            ]
        ];
    }

    public function testGetOperatorType()
    {
        $mockLicence1 = m::mock(Entity::class)->makePartial();
        $mockLicence1->shouldReceive('isGoods')
            ->andReturn(true)
            ->once()
            ->getMock();

        $mockLicence2 = m::mock(Entity::class)->makePartial();
        $mockLicence2->shouldReceive('isGoods')
            ->andReturn(false)
            ->once()
            ->getMock();

        $this->assertEquals('Goods', $mockLicence1->getOperatorType());
        $this->assertEquals('PSV', $mockLicence2->getOperatorType());
    }

    /**
     * @dataProvider dpIsExpiredDataProvider
     *
     * @param bool     $expected   Is Expiring
     * @param DateTime $expiryDate Licence expiry date
     */
    public function testIsExpired($expected, $expiryDate)
    {
        /** @var Entity $licence */
        $licence = $this->instantiate(Entity::class);
        $licence->setExpiryDate($expiryDate);
        $this->assertSame($expected, $licence->isExpired());
    }

    public function dpIsExpiredDataProvider()
    {
        return [
            'Null expiry date' => [false, null],
            [true, (new DateTime())->setTime(0, 0, 0)->sub(new \DateInterval('P2Y4M'))],
            [true, (new DateTime())->setTime(0, 0, 0)->sub(new \DateInterval('P1Y'))],
            [true, (new DateTime())->setTime(0, 0, 0)->sub(new \DateInterval('P1M'))],
            [true, (new DateTime())->setTime(0, 0, 0)->sub(new \DateInterval('P1D'))],
            'Expiry is today' => [false, (new DateTime())->setTime(0, 0, 0)],
            [false, (new DateTime())->setTime(0, 0, 0)->add(new \DateInterval('P3M'))],
            [false, (new DateTime())->setTime(0, 0, 0)->add(new \DateInterval('P1Y'))],
        ];
    }

    /**
     * @dataProvider dpIsExpiringDataProvider
     *
     * @param bool     $expected   Is Expiring
     * @param DateTime $expiryDate Licence expiry date
     */
    public function testIsExpiringNoContinuation($expected, $expiryDate)
    {
        /** @var Entity $licence */
        $licence = $this->instantiate(Entity::class);
        $licence->setExpiryDate($expiryDate);
        $expected = false;
        $this->assertSame($expected, $licence->isExpiring());
    }

    /**
     * @dataProvider dpIsExpiringDataProvider
     *
     * @param bool     $expected   Is Expiring
     * @param DateTime $expiryDate Licence expiry date
     */
    public function testIsExpiringCurrentContinuation($expected, $expiryDate)
    {
        /** @var Entity $licence */
        $licence = $this->instantiate(Entity::class);
        $licence->setExpiryDate($expiryDate);

        $continuation = new Continuation();
        $continuation->setMonth($expiryDate->format('n'));
        $continuation->setYear($expiryDate->format('Y'));
        $continuationDetail1 = new ContinuationDetail();
        $continuationDetail1->setContinuation($continuation);
        $continuationDetail1->setStatus(new RefData(ContinuationDetail::STATUS_PRINTED));

        $continuation = new Continuation();
        $continuation->setMonth($expiryDate->format('n'));
        $continuation->setYear(2010);
        $continuationDetail2 = new ContinuationDetail();
        $continuationDetail2->setContinuation($continuation);
        $continuationDetail2->setStatus(new RefData(ContinuationDetail::STATUS_PRINTED));
        $licence->setContinuationDetails(new ArrayCollection([$continuationDetail2, $continuationDetail1]));

        $this->assertSame($expected, $licence->isExpiring());
    }

    public function dpIsExpiringDataProvider()
    {
        return [
            '-2 years 4 monsth' => [false, (new DateTime())->sub(new \DateInterval('P2Y4M'))],
            '-1 year' => [false, (new DateTime())->sub(new \DateInterval('P1Y'))],
            '-1 month' => [false, (new DateTime())->sub(new \DateInterval('P1M'))],
            '-1 day' => [false, (new DateTime())->sub(new \DateInterval('P1D'))],
            'Expiry is now' => [true, (new DateTime())],
            '+1 day' => [true, (new DateTime())->add(new \DateInterval('P1D'))],
            '+1 month' => [true, (new DateTime())->add(new \DateInterval('P1M'))],
            '+2 months' => [true, (new DateTime())->add(new \DateInterval('P2M'))],
            '+75 days' => [false, (new DateTime())->add(new \DateInterval('P75D'))],
            '+3 months' => [false, (new DateTime())->add(new \DateInterval('P3M'))],
            '+1 year' => [false, (new DateTime())->add(new \DateInterval('P1Y'))],
        ];
    }

    public function testGetActiveContinuationDetails()
    {
        $continuationDetail1 = new ContinuationDetail();
        $continuationDetail1->setStatus(new RefData(ContinuationDetail::STATUS_PRINTED));
        $continuationDetail2 = new ContinuationDetail();
        $continuationDetail2->setStatus(new RefData(ContinuationDetail::STATUS_ERROR));
        $continuationDetail3 = new ContinuationDetail();
        $continuationDetail3->setStatus(new RefData(ContinuationDetail::STATUS_UNACCEPTABLE));
        $continuationDetail4 = new ContinuationDetail();
        $continuationDetail4->setStatus(new RefData(ContinuationDetail::STATUS_PRINTING));
        $continuationDetail5 = new ContinuationDetail();
        $continuationDetail5->setStatus(new RefData(ContinuationDetail::STATUS_ACCEPTABLE));

        /** @var Entity $licence */
        $licence = $this->instantiate(Entity::class);
        $licence->setContinuationDetails(
            new ArrayCollection(
                [
                    $continuationDetail1,
                    $continuationDetail2,
                    $continuationDetail3,
                    $continuationDetail4,
                    $continuationDetail5,
                ]
            )
        );

        $activeContinuatioNDetails = $licence->getActiveContinuationDetails();

        $this->assertCount(3, $activeContinuatioNDetails);
        $this->assertSame($continuationDetail1, $activeContinuatioNDetails[0]);
        $this->assertSame($continuationDetail3, $activeContinuatioNDetails[2]);
        $this->assertSame($continuationDetail5, $activeContinuatioNDetails[4]);
    }

    public function testSerialize()
    {
        /** @var Entity $licence */
        $licence = $this->instantiate(Entity::class);
        $serialized = $licence->serialize();

        $this->assertArrayNotHasKey('isExpired', $serialized);
        $this->assertArrayNotHasKey('isExpiring', $serialized);
    }

    public function testSerializeWithExpired()
    {
        /** @var Entity $licence */
        $licence = $this->instantiate(Entity::class);
        $serialized = $licence->serialize(['isExpired']);

        $this->assertArrayHasKey('isExpired', $serialized);
        $this->assertArrayNotHasKey('isExpiring', $serialized);
    }

    public function testSerializeWithExpiring()
    {
        /** @var Entity $licence */
        $licence = $this->instantiate(Entity::class);
        $serialized = $licence->serialize(['isExpiring']);

        $this->assertArrayNotHasKey('isExpired', $serialized);
        $this->assertArrayHasKey('isExpiring', $serialized);
    }

    public function testGetNotSubmittedOrUnderConsiderationVariations()
    {
        $app1 = m::mock()
            ->shouldReceive('getStatus')
            ->andReturn(new RefData(Application::APPLICATION_STATUS_NOT_SUBMITTED))
            ->once()
            ->shouldReceive('getIsVariation')
            ->andReturn(true)
            ->once()
            ->getMock();

        $app2 = m::mock()
            ->shouldReceive('getStatus')
            ->andReturn(new RefData(Application::APPLICATION_STATUS_UNDER_CONSIDERATION))
            ->once()
            ->shouldReceive('getIsVariation')
            ->andReturn(true)
            ->once()
            ->getMock();

        $app3 = m::mock()
            ->shouldReceive('getIsVariation')
            ->andReturn(false)
            ->once()
            ->getMock();

        $applications = new ArrayCollection();
        $applications->add($app1);
        $applications->add($app2);
        $applications->add($app3);

        $licence = m::mock(Entity::class)->makePartial()
            ->shouldReceive('getApplications')
            ->andReturn($applications)
            ->once()
            ->getMock();

        $expected = new ArrayCollection();
        $expected->add($app1);
        $expected->add($app2);

        $result = $licence->getNotSubmittedOrUnderConsiderationVariations();

        $this->assertEquals($result, $expected);
    }

    public function testGetOcPendingChangesNoChanges()
    {
        $licence = m::mock(Entity::class)->makePartial()
            ->shouldReceive('getNotSubmittedOrUnderConsiderationVariations')
            ->andReturn(new ArrayCollection())
            ->once()
            ->getMock();

        $this->assertEquals(0, $licence->getOcPendingChanges());
    }

    public function testGetOcPendingChanges()
    {
        $operatingCentres = new ArrayCollection();
        $operatingCentres->add(['oc1']);

        $variation = m::mock()
            ->shouldReceive('getOperatingCentres')
            ->andReturn($operatingCentres)
            ->once()
            ->shouldReceive('getTotAuthTrailers')
            ->andReturn(2)
            ->once()
            ->shouldReceive('getTotAuthVehicles')
            ->andReturn(2)
            ->once()
            ->getMock();

        $variations = new ArrayCollection();
        $variations->add($variation);

        $licence = m::mock(Entity::class)->makePartial()
            ->shouldReceive('getNotSubmittedOrUnderConsiderationVariations')
            ->andReturn($variations)
            ->once()
            ->shouldReceive('getTotAuthTrailers')
            ->andReturn(1)
            ->once()
            ->shouldReceive('getTotAuthVehicles')
            ->andReturn(1)
            ->once()
            ->getMock();

        $this->assertEquals(3, $licence->getOcPendingChanges());
    }

    public function testGetTmPendingChangesNoChanges()
    {
        $licence = m::mock(Entity::class)->makePartial()
            ->shouldReceive('getNotSubmittedOrUnderConsiderationVariations')
            ->andReturn(new ArrayCollection())
            ->once()
            ->getMock();

        $this->assertEquals(0, $licence->getTmPendingChanges());
    }

    public function testGetTmPendingChanges()
    {
        $transportManagers = new ArrayCollection();
        $transportManagers->add(['tm1']);

        $variation = m::mock()
            ->shouldReceive('getTransportManagers')
            ->andReturn($transportManagers)
            ->once()
            ->getMock();

        $variations = new ArrayCollection();
        $variations->add($variation);

        $licence = m::mock(Entity::class)->makePartial()
            ->shouldReceive('getNotSubmittedOrUnderConsiderationVariations')
            ->andReturn($variations)
            ->once()
            ->getMock();

        $this->assertEquals(1, $licence->getTmPendingChanges());
    }

    public function testGetGroupedConditionsUndertakings()
    {
        $sut = new Licence(new OrganisationEntity(), new RefData(Entity::LICENCE_STATUS_VALID));

        $conditionType = new RefData(ConditionUndertaking::TYPE_CONDITION);
        $undertakingType = new RefData(ConditionUndertaking::TYPE_UNDERTAKING);
        $attachedToLicence = new RefData(ConditionUndertaking::ATTACHED_TO_LICENCE);
        $attachedToOc = new RefData(ConditionUndertaking::ATTACHED_TO_OPERATING_CENTRE);

        $licenceCondition1 = new ConditionUndertaking($conditionType, 'N', 'N');
        $licenceCondition1->setNotes('lic cond 1');
        $licenceCondition1->setCreatedOn(\DateTime::createFromFormat('Y-m-d H:i:s', '2017-01-04 00:00:00'));
        $licenceCondition1->setAttachedTo($attachedToLicence);

        $licenceCondition2 = new ConditionUndertaking($conditionType, 'N', 'N');
        $licenceCondition2->setNotes('lic cond 2');
        $licenceCondition2->setCreatedOn(\DateTime::createFromFormat('Y-m-d H:i:s', '2017-01-03 00:00:00'));
        $licenceCondition2->setAttachedTo($attachedToLicence);

        $licenceCondition3 = new ConditionUndertaking($conditionType, 'N', 'Y');
        $licenceCondition3->setNotes('lic cond 3');
        $licenceCondition3->setCreatedOn(\DateTime::createFromFormat('Y-m-d H:i:s', '2017-01-02 00:00:00'));
        $licenceCondition3->setAttachedTo($attachedToLicence);

        $licenceCondition4 = new ConditionUndertaking($conditionType, 'Y', 'N');
        $licenceCondition4->setNotes('lic cond 4');
        $licenceCondition4->setCreatedOn(\DateTime::createFromFormat('Y-m-d H:i:s', '2017-01-01 00:00:00'));
        $licenceCondition4->setAttachedTo($attachedToLicence);

        $licenceCondition5 = new ConditionUndertaking($conditionType, 'N', 'N');
        $licenceCondition5->setNotes('lic cond 5');
        $licenceCondition5->setCreatedOn(\DateTime::createFromFormat('Y-m-d H:i:s', '2017-01-03 00:00:00'));
        $licenceCondition5->setAttachedTo($attachedToLicence);

        $licenceUndertaking1 = new ConditionUndertaking($undertakingType, 'N', 'N');
        $licenceUndertaking1->setNotes('lic und 1');
        $licenceUndertaking1->setCreatedOn(\DateTime::createFromFormat('Y-m-d H:i:s', '2016-01-04 00:00:00'));
        $licenceUndertaking1->setAttachedTo($attachedToLicence);

        $licenceUndertaking2 = new ConditionUndertaking($undertakingType, 'N', 'N');
        $licenceUndertaking2->setNotes('lic und 2');
        $licenceUndertaking2->setCreatedOn(\DateTime::createFromFormat('Y-m-d H:i:s', '2015-01-03 00:00:00'));
        $licenceUndertaking2->setAttachedTo($attachedToLicence);

        $address1 = new Address();
        $address1->setAddressLine1('line1');
        $address1->setTown('town');
        $address1->setPostcode('pc');
        $oc1 = new OperatingCentre();
        $oc1->setId(1);
        $oc1->setAddress($address1);

        $oc1Condition1 =  new ConditionUndertaking($conditionType, 'N', 'N');
        $oc1Condition1->setNotes('oc 1 cond 1');
        $oc1Condition1->setCreatedOn(\DateTime::createFromFormat('Y-m-d H:i:s', '2017-01-04 00:00:00'));
        $oc1Condition1->setAttachedTo($attachedToOc);
        $oc1Condition1->setOperatingCentre($oc1);

        $oc1Condition2 =  new ConditionUndertaking($conditionType, 'N', 'N');
        $oc1Condition2->setNotes('oc 1 cond 2');
        $oc1Condition2->setCreatedOn(\DateTime::createFromFormat('Y-m-d H:i:s', '2016-01-04 00:00:00'));
        $oc1Condition2->setAttachedTo($attachedToOc);
        $oc1Condition2->setOperatingCentre($oc1);

        $oc1Undertaking1 =  new ConditionUndertaking($undertakingType, 'N', 'N');
        $oc1Undertaking1->setNotes('oc 1 und 1');
        $oc1Undertaking1->setCreatedOn(\DateTime::createFromFormat('Y-m-d H:i:s', '2017-01-04 00:00:00'));
        $oc1Undertaking1->setAttachedTo($attachedToOc);
        $oc1Undertaking1->setOperatingCentre($oc1);

        $oc1Undertaking2 =  new ConditionUndertaking($undertakingType, 'N', 'N');
        $oc1Undertaking2->setNotes('oc 1 und 2');
        $oc1Undertaking2->setCreatedOn(\DateTime::createFromFormat('Y-m-d H:i:s', '2016-01-04 00:00:00'));
        $oc1Undertaking2->setAttachedTo($attachedToOc);
        $oc1Undertaking2->setOperatingCentre($oc1);

        $address2 = new Address();
        $address2->setAddressLine1('line12');
        $address2->setTown('town1');
        $address2->setPostcode('pc1');
        $oc2 = new OperatingCentre();
        $oc2->setId(2);
        $oc2->setAddress($address2);

        $oc2Condition1 =  new ConditionUndertaking($conditionType, 'N', 'N');
        $oc2Condition1->setNotes('oc 2 cond 1');
        $oc2Condition1->setCreatedOn(\DateTime::createFromFormat('Y-m-d H:i:s', '2017-01-04 00:00:00'));
        $oc2Condition1->setAttachedTo($attachedToOc);
        $oc2Condition1->setOperatingCentre($oc2);

        $oc2Condition2 =  new ConditionUndertaking($conditionType, 'N', 'N');
        $oc2Condition2->setNotes('oc 2 cond 2');
        $oc2Condition2->setCreatedOn(\DateTime::createFromFormat('Y-m-d H:i:s', '2016-01-04 00:00:00'));
        $oc2Condition2->setAttachedTo($attachedToOc);
        $oc2Condition2->setOperatingCentre($oc2);

        $oc2Undertaking1 =  new ConditionUndertaking($undertakingType, 'N', 'N');
        $oc2Undertaking1->setNotes('oc 2 und 1');
        $oc2Undertaking1->setCreatedOn(\DateTime::createFromFormat('Y-m-d H:i:s', '2017-01-04 00:00:00'));
        $oc2Undertaking1->setAttachedTo($attachedToOc);
        $oc2Undertaking1->setOperatingCentre($oc2);

        $oc2Undertaking2 =  new ConditionUndertaking($undertakingType, 'N', 'N');
        $oc2Undertaking2->setNotes('oc 2 und 2');
        $oc2Undertaking2->setCreatedOn(\DateTime::createFromFormat('Y-m-d H:i:s', '2016-01-04 00:00:00'));
        $oc2Undertaking2->setAttachedTo($attachedToOc);
        $oc2Undertaking2->setOperatingCentre($oc2);

        $conditionsUndertakings = new ArrayCollection();

        $conditionsUndertakings->add($licenceCondition1);
        $conditionsUndertakings->add($licenceCondition2);
        $conditionsUndertakings->add($licenceCondition3);
        $conditionsUndertakings->add($licenceCondition4);
        $conditionsUndertakings->add($licenceCondition5);
        $conditionsUndertakings->add($licenceUndertaking1);
        $conditionsUndertakings->add($licenceUndertaking2);
        $conditionsUndertakings->add($oc1Condition1);
        $conditionsUndertakings->add($oc1Condition2);
        $conditionsUndertakings->add($oc1Undertaking1);
        $conditionsUndertakings->add($oc1Undertaking2);
        $conditionsUndertakings->add($oc2Condition1);
        $conditionsUndertakings->add($oc2Condition2);
        $conditionsUndertakings->add($oc2Undertaking1);
        $conditionsUndertakings->add($oc2Undertaking2);

        $sut->setConditionUndertakings($conditionsUndertakings);

        $expected = [
            'licence' => [
                'conditions' => [
                    [
                        'notes' => 'lic cond 2',
                        'createdOn' => \DateTime::createFromFormat('Y-m-d H:i:s', '2017-01-03 00:00:00')
                    ],
                    [
                        'notes' => 'lic cond 5',
                        'createdOn' => \DateTime::createFromFormat('Y-m-d H:i:s', '2017-01-03 00:00:00')
                    ],
                    [
                        'notes' => 'lic cond 1',
                        'createdOn' => \DateTime::createFromFormat('Y-m-d H:i:s', '2017-01-04 00:00:00')
                    ],
                ],
                'undertakings' => [
                    [
                        'notes' => 'lic und 2',
                        'createdOn' => \DateTime::createFromFormat('Y-m-d H:i:s', '2015-01-03 00:00:00')
                    ],
                    [
                        'notes' => 'lic und 1',
                        'createdOn' => \DateTime::createFromFormat('Y-m-d H:i:s', '2016-01-04 00:00:00')
                    ],
                ]
            ],
            'operatingCentres' => [
                '1' => [
                    'conditions' => [
                        [
                            'notes' => 'oc 1 cond 2',
                            'createdOn' => \DateTime::createFromFormat('Y-m-d H:i:s', '2016-01-04 00:00:00'),
                            'address' => [
                                'addressLine1' => 'line1',
                                'addressLine2' => null,
                                'addressLine3' => null,
                                'addressLine4' => null,
                                'town' => 'town',
                                'postcode' => 'pc'
                            ]
                        ],
                        [
                            'notes' => 'oc 1 cond 1',
                            'createdOn' => \DateTime::createFromFormat('Y-m-d H:i:s', '2017-01-04 00:00:00'),
                            'address' => [
                                'addressLine1' => 'line1',
                                'addressLine2' => null,
                                'addressLine3' => null,
                                'addressLine4' => null,
                                'town' => 'town',
                                'postcode' => 'pc'
                            ]
                        ],
                    ],
                    'undertakings' => [
                        [
                            'notes' => 'oc 1 und 2',
                            'createdOn' => \DateTime::createFromFormat('Y-m-d H:i:s', '2016-01-04 00:00:00'),
                            'address' => [
                                'addressLine1' => 'line1',
                                'addressLine2' => null,
                                'addressLine3' => null,
                                'addressLine4' => null,
                                'town' => 'town',
                                'postcode' => 'pc'
                            ]
                        ],
                        [
                            'notes' => 'oc 1 und 1',
                            'createdOn' => \DateTime::createFromFormat('Y-m-d H:i:s', '2017-01-04 00:00:00'),
                            'address' => [
                                'addressLine1' => 'line1',
                                'addressLine2' => null,
                                'addressLine3' => null,
                                'addressLine4' => null,
                                'town' => 'town',
                                'postcode' => 'pc'
                            ]
                        ],
                    ]
                ],
                '2' => [
                    'conditions' => [
                        [
                            'notes' => 'oc 2 cond 2',
                            'createdOn' => \DateTime::createFromFormat('Y-m-d H:i:s', '2016-01-04 00:00:00'),
                            'address' => [
                                'addressLine1' => 'line12',
                                'addressLine2' => null,
                                'addressLine3' => null,
                                'addressLine4' => null,
                                'town' => 'town1',
                                'postcode' => 'pc1'
                            ]
                        ],
                        [
                            'notes' => 'oc 2 cond 1',
                            'createdOn' => \DateTime::createFromFormat('Y-m-d H:i:s', '2017-01-04 00:00:00'),
                            'address' => [
                                'addressLine1' => 'line12',
                                'addressLine2' => null,
                                'addressLine3' => null,
                                'addressLine4' => null,
                                'town' => 'town1',
                                'postcode' => 'pc1'
                            ]
                        ],
                    ],
                    'undertakings' => [
                        [
                            'notes' => 'oc 2 und 2',
                            'createdOn' => \DateTime::createFromFormat('Y-m-d H:i:s', '2016-01-04 00:00:00'),
                            'address' => [
                                'addressLine1' => 'line12',
                                'addressLine2' => null,
                                'addressLine3' => null,
                                'addressLine4' => null,
                                'town' => 'town1',
                                'postcode' => 'pc1'
                            ]
                        ],
                        [
                            'notes' => 'oc 2 und 1',
                            'createdOn' => \DateTime::createFromFormat('Y-m-d H:i:s', '2017-01-04 00:00:00'),
                            'address' => [
                                'addressLine1' => 'line12',
                                'addressLine2' => null,
                                'addressLine3' => null,
                                'addressLine4' => null,
                                'town' => 'town1',
                                'postcode' => 'pc1'
                            ]
                        ],
                    ]
                ]
            ]
        ];

        $this->assertEquals($expected, $sut->getGroupedConditionsUndertakings());
    }

    public function testGetLatestBusRouteNo()
    {
        /** @var BusRegEntity|m\Mock $busReg */
        $busReg = m::mock(BusRegEntity::class)->makePartial();
        $busReg->setRouteNo(52);

        $licence = m::mock(Entity::class)->makePartial();
        $licence->shouldReceive('getBusRegs->matching->current')->with()->twice()->andReturn($busReg);

        $this->assertSame(52, $licence->getLatestBusRouteNo());
    }

    public function testGetLatestBusRouteNoNull()
    {
        $licence = m::mock(Entity::class)->makePartial();
        $licence->shouldReceive('getBusRegs->matching->current')->with()->once()->andReturn(false);

        $this->assertSame(0, $licence->getLatestBusRouteNo());
    }

    public function testGetActiveCommunityLicences()
    {
        $licence = new Licence(m::mock(OrganisationEntity::class), m::mock(RefData::class));

        $communityLicStatuses = [
            CommunityLicEntity::STATUS_ANNUL,
            CommunityLicEntity::STATUS_ACTIVE,
            CommunityLicEntity::STATUS_EXPIRED,
            CommunityLicEntity::STATUS_PENDING,
            CommunityLicEntity::STATUS_RETURNDED,
            CommunityLicEntity::STATUS_SUSPENDED,
            CommunityLicEntity::STATUS_WITHDRAWN,
        ];

        $communityLics = [];
        foreach ($communityLicStatuses as $status) {
            $communityLic = new CommunityLicEntity();
            $communityLic->setIssueNo(0);
            $communityLic->setStatus(new RefData($status));
            $communityLics[$status . '-' . 0] = $communityLic;

            $communityLic = new CommunityLicEntity();
            $communityLic->setIssueNo(1);
            $communityLic->setStatus(new RefData($status));
            $communityLics[$status . '-' . 1] = $communityLic;
        }
        $licence->setCommunityLics(new ArrayCollection($communityLics));

        $result = $licence->getActiveCommunityLicences();

        $this->assertCount(3, $result);
        $this->assertTrue($result->contains($communityLics[CommunityLicEntity::STATUS_ACTIVE . '-1']));
        $this->assertTrue($result->contains($communityLics[CommunityLicEntity::STATUS_PENDING . '-1']));
        $this->assertTrue($result->contains($communityLics[CommunityLicEntity::STATUS_SUSPENDED . '-1']));
    }

    public function testGetActiveVariations()
    {
        $licence = new Licence(m::mock(OrganisationEntity::class), m::mock(RefData::class));

        $applicationStatuses = [
            Application::APPLICATION_STATUS_CANCELLED,
            Application::APPLICATION_STATUS_CURTAILED,
            Application::APPLICATION_STATUS_GRANTED,
            Application::APPLICATION_STATUS_NOT_SUBMITTED,
            Application::APPLICATION_STATUS_NOT_TAKEN_UP,
            Application::APPLICATION_STATUS_REFUSED,
            Application::APPLICATION_STATUS_UNDER_CONSIDERATION,
            Application::APPLICATION_STATUS_VALID,
            Application::APPLICATION_STATUS_WITHDRAWN,
        ];

        $applications = [];
        foreach ($applicationStatuses as $status) {
            $application = m::mock(Application::class)->makePartial();
            $application->setIsVariation(false);
            $application->setStatus(new RefData($status));
            $applications[$status . '-' . 0] = $application;

            $application = m::mock(Application::class)->makePartial();
            $application->setIsVariation(true);
            $application->setStatus(new RefData($status));
            $applications[$status . '-' . 1] = $application;
        }
        $licence->setApplications(new ArrayCollection($applications));

        $result = $licence->getActiveVariations();

        $this->assertCount(1, $result);
        $this->assertTrue($result->contains($applications[Application::APPLICATION_STATUS_UNDER_CONSIDERATION . '-1']));
    }

    /**
     * @dataProvider dataProviderTestDetermineNpNumber
     */
    public function testDetermineNpNumber($expected, $publication)
    {
        $licence = m::mock(Entity::class)->makePartial();
        $licence->shouldReceive('getLatestPublicationByType')->with(Publication::PUB_TYPE_N_P)->once()
            ->andReturn($publication);

        $this->assertSame($expected, $licence->determineNpNumber());
    }

    public function dataProviderTestDetermineNpNumber()
    {
        $publication = m::mock(Publication::class);
        $publication->shouldReceive('getPublicationNo')->with()->andReturn(99);
        return [
            [99, $publication],
            [null, 'X'],
            [null, new \stdClass()]
        ];
    }

    public function testGetPiRecordCount()
    {
        $licence = $this->instantiate(Entity::class);
        $case1 = m::mock(CaseEntity::class)->makePartial();
        $case1->setPublicInquiry('FOO');
        $case2 = m::mock(CaseEntity::class)->makePartial();
        $case3 = m::mock(CaseEntity::class)->makePartial();
        $case3->setPublicInquiry('BAR');
        $licence->setCases(new ArrayCollection([$case1, $case2, $case3]));

        $this->assertSame(2, $licence->getPiRecordCount());
    }

    /**
     * @dataProvider dataProviderTestAllowFeePayments
     */
    public function testAllowFeePayments($expected, $licenceStatusId)
    {
        $licence = $this->instantiate(Entity::class);
        $licence->setStatus(new RefData($licenceStatusId));
        $this->assertSame($expected, $licence->allowFeePayments());
    }

    public function dataProviderTestAllowFeePayments()
    {
        return [
            [true, Licence::LICENCE_STATUS_CANCELLED],
            [true, Licence::LICENCE_STATUS_UNDER_CONSIDERATION],
            [true, Licence::LICENCE_STATUS_NOT_SUBMITTED],
            [true, Licence::LICENCE_STATUS_SUSPENDED],
            [true, Licence::LICENCE_STATUS_VALID],
            [true, Licence::LICENCE_STATUS_CURTAILED],
            [true, Licence::LICENCE_STATUS_GRANTED],
            [false, Licence::LICENCE_STATUS_SURRENDERED],
            [false, Licence::LICENCE_STATUS_WITHDRAWN],
            [false, Licence::LICENCE_STATUS_REFUSED],
            [false, Licence::LICENCE_STATUS_REVOKED],
            [false, Licence::LICENCE_STATUS_NOT_TAKEN_UP],
            [false, Licence::LICENCE_STATUS_TERMINATED],
            [false, Licence::LICENCE_STATUS_CONTINUATION_NOT_SOUGHT],
            [true, Licence::LICENCE_STATUS_UNLICENSED],
            [true, Licence::LICENCE_STATUS_CANCELLED],
        ];
    }

    public function testGetConditionUndertakingsAddedViaLicence()
    {
        /** @var Licence $licence */
        $licence = $this->instantiate(Entity::class);

        /** @var ConditionUndertaking $cu1 */
        $cu1 = m::mock(ConditionUndertaking::class)->makePartial();
        $cu1->setAddedVia(new RefData(ConditionUndertaking::ADDED_VIA_APPLICATION));
        $cu2 = m::mock(ConditionUndertaking::class)->makePartial();
        $cu2->setAddedVia(new RefData(ConditionUndertaking::ADDED_VIA_CASE));
        $cu3 = m::mock(ConditionUndertaking::class)->makePartial();
        $cu3->setAddedVia(new RefData(ConditionUndertaking::ADDED_VIA_LICENCE));

        $licence->setConditionUndertakings(new ArrayCollection([$cu1, $cu2, $cu3]));

        $result = $licence->getConditionUndertakingsAddedViaLicence();
        $this->assertCount(1, $result);
        $this->assertSame($cu3, $result->current());
    }

    public function testGetConditionUndertakingsAddedViaImport()
    {
        /** @var Licence $licence */
        $licence = $this->instantiate(Entity::class);

        /** @var ConditionUndertaking $cu1 */
        $cu1 = m::mock(ConditionUndertaking::class)->makePartial();
        $cu1->setAddedVia(new RefData(ConditionUndertaking::ADDED_VIA_APPLICATION));
        $cu1->setApplication(m::mock(Application::class));
        $cu2 = m::mock(ConditionUndertaking::class)->makePartial();
        $cu2->setAddedVia(new RefData(ConditionUndertaking::ADDED_VIA_CASE));
        $cu3 = m::mock(ConditionUndertaking::class)->makePartial();
        $cu3->setAddedVia(new RefData(ConditionUndertaking::ADDED_VIA_LICENCE));
        $cu4 = m::mock(ConditionUndertaking::class)->makePartial();
        $cu4->setAddedVia(new RefData(ConditionUndertaking::ADDED_VIA_APPLICATION));

        $licence->setConditionUndertakings(new ArrayCollection([$cu1, $cu2, $cu3, $cu4]));

        $result = $licence->getConditionUndertakingsAddedViaImport();
        $this->assertCount(1, $result);
        $this->assertSame($cu4, $result->current());
    }

    /**
     * @dataProvider dataProviderTestGetNiFlag
     */
    public function testGetNiFlag($expected, $trafficArea)
    {
        /** @var Licence $licence */
        $licence = $this->instantiate(Entity::class);
        $licence->setTrafficArea($trafficArea);

        $this->assertSame($expected, $licence->getNiFlag());
    }

    /**
     * @dataProvider dataProviderTestGetNiFlag
     */
    public function testIsNi($expected, $trafficArea)
    {
        /** @var Licence $licence */
        $licence = $this->instantiate(Entity::class);
        $licence->setTrafficArea($trafficArea);

        $this->assertSame($expected === 'Y', $licence->isNi());
    }

    public function dataProviderTestGetNiFlag()
    {
        return [
            ['Y', (new TrafficArea())->setIsNi(true)],
            ['N', (new TrafficArea())->setIsNi(false)],
            ['N', null],
        ];
    }

    public function testGetLatestPublicationByType()
    {
        /** @var Publication $publicationNp */
        $publicationNp1 = m::mock(Publication::class)->makePartial();
        $publicationNp1->setPubType(new RefData(Publication::PUB_TYPE_N_P));
        $publicationNp1->setPubDate('2017-10-02 12:45');
        $publicationNp2 = m::mock(Publication::class)->makePartial();
        $publicationNp2->setPubType(new RefData(Publication::PUB_TYPE_N_P));
        $publicationNp2->setPubDate('2017-10-03 12:45');
        $publicationAd1 = m::mock(Publication::class)->makePartial();
        $publicationAd1->setPubType(new RefData(Publication::PUB_TYPE_A_D));
        $publicationAd1->setPubDate('2017-09-02 12:45');
        $publicationAd2 = m::mock(Publication::class)->makePartial();
        $publicationAd2->setPubType(new RefData(Publication::PUB_TYPE_A_D));
        $publicationAd2->setPubDate('2017-09-02 13:45');

        /** @var PublicationLink $publicationLink1 */
        $publicationLink1 = m::mock(PublicationLink::class)->makePartial();
        $publicationLink1->setPublication($publicationNp1);
        $publicationLink2 = m::mock(PublicationLink::class)->makePartial();
        $publicationLink2->setPublication($publicationNp2);
        $publicationLink3 = m::mock(PublicationLink::class)->makePartial();
        $publicationLink3->setPublication($publicationAd1);
        $publicationLink4 = m::mock(PublicationLink::class)->makePartial();
        $publicationLink4->setPublication($publicationAd2);

        /** @var Licence $licence */
        $licence = $this->instantiate(Entity::class);
        $licence->setPublicationLinks(
            new ArrayCollection([$publicationLink1, $publicationLink2, $publicationLink3, $publicationLink4])
        );

        $this->assertSame(
            $publicationAd2,
            $licence->getLatestPublicationByType(new RefData(Publication::PUB_TYPE_A_D))
        );
        $this->assertSame(
            $publicationNp2,
            $licence->getLatestPublicationByType(new RefData(Publication::PUB_TYPE_N_P))
        );
    }

    public function testGetLatestPublicationByTypeNull()
    {
        /** @var Publication $publicationNp */
        $publicationNp1 = m::mock(Publication::class)->makePartial();
        $publicationNp1->setPubType(new RefData(Publication::PUB_TYPE_N_P));
        $publicationNp1->setPubDate('2017-10-02 12:45');
        $publicationNp2 = m::mock(Publication::class)->makePartial();
        $publicationNp2->setPubType(new RefData(Publication::PUB_TYPE_N_P));
        $publicationNp2->setPubDate('2017-10-03 12:45');

        /** @var PublicationLink $publicationLink1 */
        $publicationLink1 = m::mock(PublicationLink::class)->makePartial();
        $publicationLink1->setPublication($publicationNp1);
        $publicationLink2 = m::mock(PublicationLink::class)->makePartial();
        $publicationLink2->setPublication($publicationNp2);

        /** @var Licence $licence */
        $licence = $this->instantiate(Entity::class);
        $licence->setPublicationLinks(
            new ArrayCollection([$publicationLink1, $publicationLink2])
        );

        $this->assertNull(
            $licence->getLatestPublicationByType(new RefData(Publication::PUB_TYPE_A_D))
        );
    }

    public function testGetLocByOc()
    {
        $oc1 = m::mock(OperatingCentre::class);
        $oc2 = m::mock(OperatingCentre::class);
        $oc3 = m::mock(OperatingCentre::class);
        $loc1 = m::mock(LicenceOperatingCentre::class)->makePartial()->setOperatingCentre($oc1);
        $loc2 = m::mock(LicenceOperatingCentre::class)->makePartial()->setOperatingCentre($oc2);
        $loc3 = m::mock(LicenceOperatingCentre::class)->makePartial()->setOperatingCentre($oc2);

        /** @var Licence $licence */
        $licence = $this->instantiate(Entity::class);
        $licence->setOperatingCentres(new ArrayCollection([$loc1, $loc2, $loc3]));
        $this->assertSame($loc2, $licence->getLocByOc($oc2));
        $this->assertSame($loc1, $licence->getLocByOc($oc1));
        $this->assertNull($licence->getLocByOc($oc3));
    }

    /**
     * @dataProvider dataProviderTestHasQueuedRevocation
     */
    public function testHasQueuedRevocation($expected, ArrayCollection $licenceStatusRules)
    {
        /** @var Licence $licence */
        $licence = $this->instantiate(Entity::class);
        $licence->setLicenceStatusRules($licenceStatusRules);
        $this->assertSame($expected, $licence->hasQueuedRevocation());
    }

    public function dataProviderTestHasQueuedRevocation()
    {
        $lsr1 = m::mock(LicenceStatusRule::class)->makePartial()
            ->setLicenceStatus(new RefData(Licence::LICENCE_STATUS_VALID));
        $lsr1->shouldReceive('isQueued')->with()->andReturn(false);
        $lsr2 = m::mock(LicenceStatusRule::class)->makePartial()
            ->setLicenceStatus(new RefData(Licence::LICENCE_STATUS_GRANTED));
        $lsr2->shouldReceive('isQueued')->with()->andReturn(false);
        $lsr3 = m::mock(LicenceStatusRule::class)->makePartial()
            ->setLicenceStatus(new RefData(Licence::LICENCE_STATUS_REVOKED));
        $lsr3->shouldReceive('isQueued')->with()->andReturn(false);
        $lsr4 = m::mock(LicenceStatusRule::class)->makePartial()
            ->setLicenceStatus(new RefData(Licence::LICENCE_STATUS_REVOKED));
        $lsr4->shouldReceive('isQueued')->with()->andReturn(true);

        return [
            [true, new ArrayCollection([$lsr1, $lsr2, $lsr3, $lsr4])],
            [true, new ArrayCollection([$lsr4])],
            [false, new ArrayCollection()],
            [false, new ArrayCollection([$lsr1, $lsr2, $lsr3])],
        ];
    }

    public function testGetRelatedOrganisation()
    {
        $organisation = m::mock(OrganisationEntity::class);

        /** @var Licence $licence */
        $licence = $this->instantiate(Entity::class);
        $licence->setOrganisation($organisation);

        $this->assertSame($organisation, $licence->getRelatedOrganisation());
    }

    /**
     * Return an entity eligible for permits (prevents having to retest eligibleForPermits method each time)
     *
     * @return Entity|m\mockInterface
     */
    private function createEligibleForPermits($isEligibleForPermits)
    {
        $licence = m::mock(Entity::class)->makePartial();
        $licence->shouldReceive('isEligibleForPermits')
            ->withNoArgs()
            ->andReturn($isEligibleForPermits);

        return $licence;
    }

    /**
     * Bring back a list of Irhp applications, prove that non-matching apps  work as intended
     * The non-matching apps are an excluded app (same app id) and an app where the stock id does not match
     * The final (active) application is a match and therefore an application can't be made
     */
    public function testCanMakeIrhpApplication()
    {
        $excludedAppId = 111;
        $excludedApp = m::mock(IrhpApplication::class);
        //id is checked once for each app
        $excludedApp->shouldReceive('getId')->times(3)->withNoArgs()->andReturn($excludedAppId);

        $irhpPermitType = m::mock(IrhpPermitType::class);
        $irhpPermitType->shouldReceive('getId')->withNoArgs()->andReturn(IrhpPermitType::IRHP_PERMIT_TYPE_ID_MULTILATERAL);

        $stockId = 999;
        $stock = m::mock(IrhpPermitStock::class);
        $stock->shouldReceive('getIrhpPermitType')->withNoArgs()->andReturn($irhpPermitType);
        $stock->shouldReceive('getId')->once()->withNoArgs()->andReturn($stockId);
        $stock->shouldReceive('isCertificateOfRoadworthiness')->withNoArgs()->andReturnFalse();

        $matchExcludedApp = m::mock(IrhpApplication::class);
        $matchExcludedApp->shouldReceive('getIrhpPermitType')->once()->withNoArgs()->andReturn($irhpPermitType);
        $matchExcludedApp->shouldReceive('getStatus')->once()->withNoArgs()->andReturn(new RefData(IrhpInterface::STATUS_NOT_YET_SUBMITTED));
        $matchExcludedApp->shouldReceive('getId')->withNoArgs()->andReturn($excludedAppId);
        $matchExcludedApp->shouldReceive('isMultiStock')->never();
        $matchExcludedApp->shouldReceive('getAssociatedStock->getId')->never();

        $nonMatchingStockApp = m::mock(IrhpApplication::class);
        $nonMatchingStockApp->shouldReceive('getIrhpPermitType')->once()->withNoArgs()->andReturn($irhpPermitType);
        $nonMatchingStockApp->shouldReceive('getStatus')->once()->withNoArgs()->andReturn(new RefData(IrhpInterface::STATUS_NOT_YET_SUBMITTED));
        $nonMatchingStockApp->shouldReceive('getId')->withNoArgs()->andReturn(1010);
        $nonMatchingStockApp->shouldReceive('isMultiStock')->once()->andReturn(false);
        $nonMatchingStockApp->shouldReceive('getAssociatedStock->getId')
            ->once()
            ->withNoArgs()
            ->andReturn(222);

        $activeApp = m::mock(IrhpApplication::class);
        $activeApp->shouldReceive('getIrhpPermitType')->once()->withNoArgs()->andReturn($irhpPermitType);
        $activeApp->shouldReceive('getStatus')->once()->withNoArgs()->andReturn(new RefData(IrhpInterface::STATUS_NOT_YET_SUBMITTED));
        $activeApp->shouldReceive('getId')->withNoArgs()->andReturn(2020);
        $activeApp->shouldReceive('isMultiStock')->once()->andReturn(false);
        $activeApp->shouldReceive('getAssociatedStock->getId')->once()->withNoArgs()->andReturn($stockId);

        $collection = new ArrayCollection([$matchExcludedApp, $nonMatchingStockApp, $activeApp]);

        $licence = $this->createEligibleForPermits(true);
        $licence->shouldReceive('getIrhpApplications')
            ->withNoArgs()
            ->andReturn($collection);

        $this->assertFalse($licence->canMakeIrhpApplication($stock, $excludedApp));
    }

    /**
     * Bring back a single multi stock application, an application still cannot be made as we don't check the stock
     * Tests the multi stock switch operates correctly
     */
    public function testCanMakeIrhpApplicationWithOneIgnoredApp()
    {
        $irhpPermitType = m::mock(IrhpPermitType::class);
        $irhpPermitType->shouldReceive('getId')->withNoArgs()->andReturn(IrhpPermitType::IRHP_PERMIT_TYPE_ID_MULTILATERAL);

        $stockId = 999;
        $stock = m::mock(IrhpPermitStock::class);
        $stock->shouldReceive('getIrhpPermitType')->withNoArgs()->andReturn($irhpPermitType);
        $stock->shouldReceive('getId')->once()->withNoArgs()->andReturn($stockId);
        $stock->shouldReceive('isCertificateOfRoadworthiness')->withNoArgs()->andReturnFalse();

        $nonMatchingStockApp = m::mock(IrhpApplication::class);
        $nonMatchingStockApp->shouldReceive('getIrhpPermitType')->once()->withNoArgs()->andReturn($irhpPermitType);
        $nonMatchingStockApp->shouldReceive('getStatus')->once()->withNoArgs()->andReturn(new RefData(IrhpInterface::STATUS_UNDER_CONSIDERATION));
        $nonMatchingStockApp->shouldReceive('getId')->never();
        $nonMatchingStockApp->shouldReceive('isMultiStock')->once()->andReturn(true);
        $nonMatchingStockApp->shouldReceive('getAssociatedStock->getId')->never();

        $collection = new ArrayCollection([$nonMatchingStockApp]);

        $licence = $this->createEligibleForPermits(true);
        $licence->shouldReceive('getIrhpApplications')
            ->withNoArgs()
            ->andReturn($collection);

        $this->assertFalse($licence->canMakeIrhpApplication($stock, null));
    }

    public function testCanMakeIrhpApplicationNoApplications()
    {
        $stock = m::mock(IrhpPermitStock::class);
        $stock->shouldReceive('getIrhpPermitType')->never();
        $stock->shouldReceive('isCertificateOfRoadworthiness')->withNoArgs()->andReturnFalse();

        $licence = $this->createEligibleForPermits(true);
        $licence->shouldReceive('getIrhpApplications')
            ->withNoArgs()
            ->andReturn(new ArrayCollection());

        $this->assertTrue($licence->canMakeIrhpApplication($stock, null));
    }

    /**
     * @dataProvider dpCanMakeIrhpApplicationCertificateOfRoadworthiness
     */
    public function testCanMakeIrhpApplicationCertificateOfRoadworthiness($irhpApplication)
    {
        $stock = m::mock(IrhpPermitStock::class);
        $stock->shouldReceive('isCertificateOfRoadworthiness')
            ->withNoArgs()
            ->andReturnTrue();

        $licence = $this->createEligibleForPermits(true);

        $this->assertTrue($licence->canMakeIrhpApplication($stock, $irhpApplication));
    }

    public function dpCanMakeIrhpApplicationCertificateOfRoadworthiness()
    {
        return [
            [null],
            [m::mock(IrhpApplication::class)],
        ];
    }

    public function testCanMakeIrhpApplicationNotEligibleForPermits()
    {
        $stock = m::mock(IrhpPermitStock::class);
        $licence = $this->createEligibleForPermits(false);
        $irhpApplication = m::mock(IrhpApplication::class);

        $this->assertFalse($licence->canMakeIrhpApplication($stock, $irhpApplication));
    }

    /**
     * @dataProvider dpValidSuspendedOrCurtailed
     */
    public function testHasStatusRequiredForCommunityLicenceReprint($status, $expected)
    {
        $sut = m::mock(Licence::class)->makePartial();
        $sut->shouldReceive('getStatus->getId')
            ->andReturn($status);

        $this->assertEquals(
            $expected,
            $sut->hasStatusRequiredForCommunityLicenceReprint()
        );
    }

    /**
     * @dataProvider dpValidSuspendedOrCurtailed
     */
    public function testHasStatusRequiredForPostScoringEmail($status, $expected)
    {
        $sut = m::mock(Licence::class)->makePartial();
        $sut->shouldReceive('getStatus->getId')
            ->andReturn($status);

        $this->assertEquals(
            $expected,
            $sut->hasStatusRequiredForPostScoringEmail()
        );
    }

    public function dpValidSuspendedOrCurtailed()
    {
        return [
            [Licence::LICENCE_STATUS_UNDER_CONSIDERATION, false],
            [Licence::LICENCE_STATUS_NOT_SUBMITTED, false],
            [Licence::LICENCE_STATUS_SUSPENDED, true],
            [Licence::LICENCE_STATUS_VALID, true],
            [Licence::LICENCE_STATUS_CURTAILED, true],
            [Licence::LICENCE_STATUS_GRANTED, false],
            [Licence::LICENCE_STATUS_SURRENDER_UNDER_CONSIDERATION, false],
            [Licence::LICENCE_STATUS_SURRENDERED, false],
            [Licence::LICENCE_STATUS_WITHDRAWN, false],
            [Licence::LICENCE_STATUS_REFUSED, false],
            [Licence::LICENCE_STATUS_REVOKED, false],
            [Licence::LICENCE_STATUS_NOT_TAKEN_UP, false],
            [Licence::LICENCE_STATUS_TERMINATED, false],
            [Licence::LICENCE_STATUS_CONTINUATION_NOT_SOUGHT, false],
            [Licence::LICENCE_STATUS_UNLICENSED, false],
            [Licence::LICENCE_STATUS_CANCELLED, false],
        ];
    }

    public function testIsExempt()
    {
        /** @var Licence $licence */
        $licence = $this->instantiate(Entity::class);

        $licence->setLicNo('EPB2000001');
        $this->assertTrue($licence->isExempt());

        $licence->setLicNo('UPB2000001');
        $this->assertFalse($licence->isExempt());
    }

    public function testGetOngoingPermitApplications()
    {
        $irhpApplication1 = m::mock(IrhpApplication::class);
        $irhpApplication1->shouldReceive('isOngoing')
            ->withNoArgs()
            ->andReturnTrue();

        $irhpApplication2 = m::mock(IrhpApplication::class);
        $irhpApplication2->shouldReceive('isOngoing')
            ->withNoArgs()
            ->andReturnFalse();

        $irhpApplication3 = m::mock(IrhpApplication::class);
        $irhpApplication3->shouldReceive('isOngoing')
            ->withNoArgs()
            ->andReturnTrue();

        $licence = $this->instantiate(Entity::class);

        $licence->setIrhpApplications(
            new ArrayCollection([$irhpApplication1, $irhpApplication2, $irhpApplication3])
        );

        $ongoingIrhpApplications = $licence->getOngoingIrhpApplications();
        $ongoingIrhpApplicationsArray = $ongoingIrhpApplications->toArray();

        $this->assertCount(2, $ongoingIrhpApplicationsArray);
        $this->assertSame($irhpApplication1, $ongoingIrhpApplicationsArray[0]);
        $this->assertSame($irhpApplication3, $ongoingIrhpApplicationsArray[1]);
    }

    public function testGetValidIrhpApplications()
    {
        $irhpApplication1 = m::mock(IrhpApplication::class);
        $irhpApplication1->shouldReceive('isValid')
            ->withNoArgs()
            ->andReturnTrue();

        $irhpApplication2 = m::mock(IrhpApplication::class);
        $irhpApplication2->shouldReceive('isValid')
            ->withNoArgs()
            ->andReturnFalse();

        $irhpApplication3 = m::mock(IrhpApplication::class);
        $irhpApplication3->shouldReceive('isValid')
            ->withNoArgs()
            ->andReturnTrue();

        $licence = $this->instantiate(Entity::class);

        $licence->setIrhpApplications(
            new ArrayCollection([$irhpApplication1, $irhpApplication2, $irhpApplication3])
        );

        $validIrhpApplications = $licence->getValidIrhpApplications();

        $this->assertCount(2, $validIrhpApplications);
        $this->assertContains($irhpApplication1, $validIrhpApplications);
        $this->assertContains($irhpApplication3, $validIrhpApplications);
    }

    /**
     * @dataProvider dpHasUnderConsiderationOrAwaitingFeeApplicationForStock
     */
    public function testHasUnderConsiderationOrAwaitingFeeApplicationForStock(
        $irhpPermitStock,
        $irhpApplications,
        $expected
    ) {
        $licence = $this->instantiate(Entity::class);
        $licence->setIrhpApplications($irhpApplications);

        $this->assertEquals(
            $expected,
            $licence->hasUnderConsiderationOrAwaitingFeeApplicationForStock($irhpPermitStock)
        );
    }

    public function dpHasUnderConsiderationOrAwaitingFeeApplicationForStock()
    {
        $irhpPermitStock = m::mock(IrhpPermitStock::class);

        $irhpApplicationSatisfyingCriteria = m::mock(IrhpApplication::class);
        $irhpApplicationSatisfyingCriteria->shouldReceive('isUnderConsiderationOrAwaitingFeeAndAssociatedWithStock')
            ->with($irhpPermitStock)
            ->andReturnTrue();

        $irhpApplicationNotSatisfyingCriteria = m::mock(IrhpApplication::class);
        $irhpApplicationNotSatisfyingCriteria->shouldReceive('isUnderConsiderationOrAwaitingFeeAndAssociatedWithStock')
            ->with($irhpPermitStock)
            ->andReturnFalse();

        return [
            [
                $irhpPermitStock,
                [
                    $irhpApplicationNotSatisfyingCriteria,
                    $irhpApplicationNotSatisfyingCriteria,
                    $irhpApplicationNotSatisfyingCriteria
                ],
                false
            ],
            [
                $irhpPermitStock,
                [
                    $irhpApplicationNotSatisfyingCriteria,
                    $irhpApplicationSatisfyingCriteria,
                    $irhpApplicationNotSatisfyingCriteria
                ],
                true
            ],
            [
                $irhpPermitStock,
                [
                    $irhpApplicationSatisfyingCriteria,
                    $irhpApplicationSatisfyingCriteria,
                    $irhpApplicationSatisfyingCriteria
                ],
                true
            ],
        ];
    }

    /**
     * @dataProvider dpHasValidCertificateOfRoadworthinessApplications
     */
    public function testHasValidCertificateOfRoadworthinessApplications($irhpApplications, $expected)
    {
        $licence = $this->instantiate(Entity::class);

        $licence->setIrhpApplications(
            new ArrayCollection($irhpApplications)
        );

        $this->assertEquals(
            $expected,
            $licence->hasValidCertificateOfRoadworthinessApplications()
        );
    }

    public function dpHasValidCertificateOfRoadworthinessApplications()
    {
        $irhpApplicationValidCor = m::mock(IrhpApplication::class);
        $irhpApplicationValidCor->shouldReceive('isValidCertificateOfRoadworthiness')
            ->withNoArgs()
            ->andReturnTrue();

        $irhpApplicationNotValidCor = m::mock(IrhpApplication::class);
        $irhpApplicationNotValidCor->shouldReceive('isValidCertificateOfRoadworthiness')
            ->withNoArgs()
            ->andReturnFalse();

        return [
            'no applications' => [
                [],
                false,
            ],
            'all valid cor' => [
                [$irhpApplicationValidCor, $irhpApplicationValidCor, $irhpApplicationValidCor],
                true,
            ],
            'some valid cor' => [
                [$irhpApplicationValidCor, $irhpApplicationNotValidCor, $irhpApplicationValidCor],
                true,
            ],
            'no valid cor' => [
                [$irhpApplicationNotValidCor, $irhpApplicationNotValidCor],
                false,
            ]
        ];
    }

    protected function setUpSut()
    {
        $this->sut = new Licence($this->organisation(), new RefData(Licence::LICENCE_STATUS_VALID));
    }

    /**
     * @return Organisation
     */
    protected function organisation(): Organisation
    {
        return new Organisation();
    }

    /**
     * @return Application
     */
    protected function applicationForNewLicence(): Application
    {
        return new Application($this->sut, new RefData(Application::APPLICATION_STATUS_GRANTED), false);
    }

    /**
     * @return Application
     */
    protected function variation(): Application
    {
        return new Application($this->sut, new RefData(Application::APPLICATION_STATUS_GRANTED), true);
    }

    /**
     * @return Application
     */
    protected function variationWithUpdatedOperatingCentres(): Application
    {
        $instance = $this->variation();
        $applicationCompletion = new ApplicationCompletion($instance);
        $applicationCompletion->setOperatingCentresStatus(ApplicationCompletion::STATUS_VARIATION_UPDATED);
        $instance->setApplicationCompletion($applicationCompletion);
        return $instance;
    }

    /**
     * @dataProvider dpGetApplicableAuthProperties
     */
    public function testGetApplicableAuthProperties(
        $vehicleTypeId,
        $licenceTypeId,
        $totAuthLgvVehicles,
        $expectedAuthProperties
    ) {
        $licence = m::mock(Entity::class)->makePartial();

        $vehicleTypeRefdata = new RefData($vehicleTypeId);
        $licence->setVehicleType($vehicleTypeRefdata);

        $licenceTypeRefdata = new RefData($licenceTypeId);
        $licence->setLicenceType($licenceTypeRefdata);

        $licence->setTotAuthLgvVehicles($totAuthLgvVehicles);

        $this->assertEquals(
            $expectedAuthProperties,
            $licence->getApplicableAuthProperties()
        );
    }

    public function dpGetApplicableAuthProperties()
    {
        return [
            'goods/standard international/lgv/null lgv count' => [
                RefData::APP_VEHICLE_TYPE_LGV,
                Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                null,
                [
                    'totAuthLgvVehicles',
                ],
            ],
            'goods/standard international/lgv/nonzero lgv count' => [
                RefData::APP_VEHICLE_TYPE_LGV,
                Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                5,
                [
                    'totAuthLgvVehicles',
                ],
            ],
            'goods/standard international/mixed/null lgv count' => [
                RefData::APP_VEHICLE_TYPE_MIXED,
                Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                null,
                [
                    'totAuthVehicles',
                    'totAuthTrailers',
                ],
            ],
            'goods/standard international/mixed/zero lgv count' => [
                RefData::APP_VEHICLE_TYPE_MIXED,
                Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                0,
                [
                    'totAuthHgvVehicles',
                    'totAuthLgvVehicles',
                    'totAuthTrailers',
                ],
            ],
            'goods/standard international/mixed/nonzero lgv count' => [
                RefData::APP_VEHICLE_TYPE_MIXED,
                Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                4,
                [
                    'totAuthHgvVehicles',
                    'totAuthLgvVehicles',
                    'totAuthTrailers',
                ],
            ],
            'goods/other' => [
                RefData::APP_VEHICLE_TYPE_HGV,
                Licence::LICENCE_TYPE_STANDARD_NATIONAL,
                null,
                [
                    'totAuthVehicles',
                    'totAuthTrailers',
                ],
            ],
            'psv/special restricted' => [
                RefData::APP_VEHICLE_TYPE_PSV,
                Licence::LICENCE_TYPE_SPECIAL_RESTRICTED,
                null,
                [],
            ],
            'psv/other' => [
                RefData::APP_VEHICLE_TYPE_PSV,
                Licence::LICENCE_TYPE_STANDARD_NATIONAL,
                null,
                [
                    'totAuthVehicles',
                ],
            ],
        ];
    }

    public function testGetApplicableAuthPropertiesException()
    {
        $this->expectException(RuntimeException::class);

        $licence = m::mock(Entity::class)->makePartial();

        $vehicleTypeRefdata = new RefData('foo');
        $licence->setVehicleType($vehicleTypeRefdata);

        $licence->getApplicableAuthProperties();
    }

    /**
     * @dataProvider dpIsVehicleTypeMixedWithLgv
     */
    public function testIsVehicleTypeMixedWithLgv($vehicleTypeId, $totAuthLgvVehicles, $expected)
    {
        $licence = $this->instantiate(Entity::class);

        $licence->setVehicleType(
            new RefData($vehicleTypeId)
        );

        $licence->setTotAuthLgvVehicles($totAuthLgvVehicles);

        $this->assertEquals(
            $expected,
            $licence->isVehicleTypeMixedWithLgv()
        );
    }

    public function dpIsVehicleTypeMixedWithLgv()
    {
        return [
            [RefData::APP_VEHICLE_TYPE_PSV, null, false],
            [RefData::APP_VEHICLE_TYPE_HGV, null, false],
            [RefData::APP_VEHICLE_TYPE_MIXED, null, false],
            [RefData::APP_VEHICLE_TYPE_LGV, null, false],
            [RefData::APP_VEHICLE_TYPE_PSV, 0, false],
            [RefData::APP_VEHICLE_TYPE_HGV, 0, false],
            [RefData::APP_VEHICLE_TYPE_MIXED, 0, true],
            [RefData::APP_VEHICLE_TYPE_LGV, 0, false],
            [RefData::APP_VEHICLE_TYPE_PSV, 1, false],
            [RefData::APP_VEHICLE_TYPE_HGV, 1, false],
            [RefData::APP_VEHICLE_TYPE_MIXED, 1, true],
            [RefData::APP_VEHICLE_TYPE_LGV, 1, false],
        ];
    }

    /**
     * Checks when all addresses are populated, the correspondence address gets preference
     */
    public function testGetContactAddressCorrespondence(): void
    {
        $correspondenceCd = m::mock(ContactDetails::class);
        $establishmentCd = m::mock(ContactDetails::class);
        $tmCd = m::mock(ContactDetails::class);

        $licence = $this->getLicenceWithContactDetails($correspondenceCd, $establishmentCd, $tmCd);
        $this->assertEquals($correspondenceCd, $licence->getContactAddress());
    }

    /**
     * Checks establishment address gets priority over transport consultant
     */
    public function testGetContactAddressEstablishment(): void
    {
        $establishmentCd = m::mock(ContactDetails::class);
        $tmCd = m::mock(ContactDetails::class);

        $licence = $this->getLicenceWithContactDetails(null, $establishmentCd, $tmCd);
        $this->assertEquals($establishmentCd, $licence->getContactAddress());
    }

    /**
     * Checks transport consultant address used if no other addresses
     */
    public function testGetContactAddressConsultant(): void
    {
        $tmCd = m::mock(ContactDetails::class);

        $licence = $this->getLicenceWithContactDetails(null, null, $tmCd);
        $this->assertEquals($tmCd, $licence->getContactAddress());
    }

    /**
     * Checks null returned when no addresses present
     */
    public function testGetContactAddressNull(): void
    {
        $licence = $this->getLicenceWithContactDetails(null, null, null);
        $this->assertNull($licence->getContactAddress());
    }

    private function getLicenceWithContactDetails(
        ?m\MockInterface $correspondenceCd,
        ?m\MockInterface $establishmentCd,
        ?m\MockInterface $tmCd
    ): Entity {
        $licence = $this->instantiate(Entity::class);
        $licence->setCorrespondenceCd($correspondenceCd);
        $licence->setEstablishmentCd($establishmentCd);
        $licence->setTransportConsultantCd($tmCd);

        return $licence;
    }
}
