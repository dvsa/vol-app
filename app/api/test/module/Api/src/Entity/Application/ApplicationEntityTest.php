<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Application;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Api\Entity as Entities;
use Dvsa\Olcs\Api\Entity\Application\Application as Entity;
use Dvsa\Olcs\Api\Entity\Application\ApplicationCompletion;
use Dvsa\Olcs\Api\Entity\Application\ApplicationOperatingCentre;
use Dvsa\Olcs\Api\Entity\Application\S4;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Licence\LicenceVehicle;
use Dvsa\Olcs\Api\Entity\OperatingCentre\OperatingCentre;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea;
use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Dvsa\Olcs\Api\Entity\Publication\PublicationSection as PublicationSectionEntity;
use Dvsa\Olcs\Api\Entity\Application\ApplicationOrganisationPerson;
use Mockery as m;
use Dvsa\OlcsTest\Api\Entity\Traits\TotAuthVehiclesTraitTest;
use RuntimeException;

/**
 * @covers \Dvsa\Olcs\Api\Entity\Application\Application
 * @covers \Dvsa\Olcs\Api\Entity\Application\AbstractApplication
 */
class ApplicationEntityTest extends EntityTester
{
    use TotAuthVehiclesTraitTest;

    protected const A_NUMBER_OF_VEHICLES = 2;

    /**
     * @var Entity|null
     */
    protected $sut;

    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    /**
     * @var Entity
     */
    protected $entity;

    /**
     * @var  Licence
     */
    private $licence;

    public function testSetGet()
    {
        /** @var Entity $sut */
        $sut = $this->instantiate(Entity::class);

        $sut->setPublicationNo('unit_PubNo');
        static::assertEquals('unit_PubNo', $sut->getPublicationNo());

        $sut->setOooDate('unit_OooDate');
        static::assertEquals('unit_OooDate', $sut->getOooDate());

        $sut->setOorDate('unit_OorDate');
        static::assertEquals('unit_OorDate', $sut->getOorDate());

        $sut->setIsOpposed('unit_IsOpposed');
        static::assertEquals('unit_IsOpposed', $sut->getIsOpposed());

        $sut->setPublishedDate('unit_PublDate');
        static::assertEquals('unit_PublDate', $sut->getPublishedDate());
    }

    public function testUpdateDigitalSignature(): void
    {
        $signatureType = m::mock(RefData::class);
        $signature = m::mock(Entities\DigitalSignature::class);

        $sut = m::mock(Entity::class)->makePartial();
        $sut->updateDigitalSignature($signatureType, $signature);
        $this->assertEquals($signatureType, $sut->getSignatureType());
        $this->assertEquals($signature, $sut->getDigitalSignature());
    }

    public function testUpdateTypeOfLicenceTrue()
    {
        $niFlag = 'unit_niFlag';
        $gop = 'unit_goodsOrPsv';
        $licType = 'unit_licType';
        $vehType = 'unit_vehType';
        $lgvDecCon = 'unit_lgvDec';

        /** @var Entity $sut */
        $sut = m::mock(Entity::class)->makePartial();
        $sut->shouldReceive('validateTol')
            ->once()
            ->with($niFlag, $gop, $licType, $vehType, $lgvDecCon)
            ->andReturnTrue();

        static::assertTrue($sut->updateTypeOfLicence($niFlag, $gop, $licType, $vehType, $lgvDecCon));

        static::assertEquals($niFlag, $sut->getNiFlag());
        static::assertEquals($gop, $sut->getGoodsOrPsv());
        static::assertEquals($licType, $sut->getLicenceType());
        static::assertEquals($vehType, $sut->getVehicleType());
        static::assertEquals($lgvDecCon, $sut->getLgvDeclarationConfirmation());
    }

    public function testUpdateTypeOfLicenceNull()
    {
        $niFlag = 'unit_niFlag';
        $gop = 'unit_goodsOrPsv';
        $licType = 'unit_licType';
        $vehType = 'unit_vehType';
        $lgvDecCon = 'unit_lgvDec';

        /** @var Entity $sut */
        $sut = m::mock(Entity::class)->makePartial();
        $sut->shouldReceive('validateTol')
            ->once()
            ->with($niFlag, $gop, $licType, $vehType, $lgvDecCon)
            ->andReturnFalse();

        static::assertNull($sut->updateTypeOfLicence($niFlag, $gop, $licType, $vehType, $lgvDecCon));

        static::assertNull($sut->getNiFlag());
        static::assertNull($sut->getGoodsOrPsv());
        static::assertNull($sut->getLicenceType());
        static::assertNull($sut->getVehicleType());
        static::assertEquals(0, $sut->getLgvDeclarationConfirmation());
    }

    /** @dataProvider dpUpdateTypeOfLicenceFromMixed */
    public function testUpdateTypeOfLicenceFromMixed($vehType, $expectTotAuthLgvVehicles, $expectTotAuthHgvVehicles, $expectTotAuthTrailers)
    {
        $niFlag = 'unit_niFlag';
        $gop = 'unit_goodsOrPsv';
        $licType = 'unit_licType';
        $lgvDecCon = 'unit_lgvDec';

        /** @var Entity $sut */
        $sut = m::mock(Entity::class)->makePartial();
        $sut->shouldReceive('validateTol')
            ->once()
            ->with($niFlag, $gop, $licType, $vehType, $lgvDecCon)
            ->andReturnTrue();
        $sut->setVehicleType(RefData::APP_VEHICLE_TYPE_MIXED);
        $sut->updateTotAuthLgvVehicles(3);
        $sut->updateTotAuthHgvVehicles(10);
        $sut->setTotAuthTrailers(5);

        static::assertTrue($sut->updateTypeOfLicence($niFlag, $gop, $licType, $vehType, $lgvDecCon));

        static::assertEquals($vehType, $sut->getVehicleType());
        static::assertEquals($expectTotAuthLgvVehicles, $sut->getTotAuthLgvVehicles());
        static::assertEquals($expectTotAuthHgvVehicles, $sut->getTotAuthHgvVehicles());
        static::assertEquals($expectTotAuthTrailers, $sut->getTotAuthTrailers());
    }

    public function dpUpdateTypeOfLicenceFromMixed()
    {
        return [
            [
                'vehType' => RefData::APP_VEHICLE_TYPE_HGV,
                'expectTotAuthLgvVehicles' => null,
                'expectTotAuthHgvVehicles' => 10,
                'expectTotAuthTrailers' => 5,
            ],
            [
                'vehType' => RefData::APP_VEHICLE_TYPE_PSV,
                'expectTotAuthLgvVehicles' => null,
                'expectTotAuthHgvVehicles' => 10,
                'expectTotAuthTrailers' => null,
            ],
            [
                'vehType' => RefData::APP_VEHICLE_TYPE_LGV,
                'expectTotAuthLgvVehicles' => 3,
                'expectTotAuthHgvVehicles' => null,
                'expectTotAuthTrailers' => null,
            ],
            [
                'vehType' => RefData::APP_VEHICLE_TYPE_MIXED,
                'expectTotAuthLgvVehicles' => 3,
                'expectTotAuthHgvVehicles' => 10,
                'expectTotAuthTrailers' => 5,
            ],
        ];
    }

    /** @dataProvider dpUpdateTypeOfLicenceFromLgvOnly */
    public function testUpdateTypeOfLicenceFromLgvOnly($vehType, $expectTotAuthLgvVehicles)
    {
        $niFlag = 'unit_niFlag';
        $gop = 'unit_goodsOrPsv';
        $licType = 'unit_licType';
        $lgvDecCon = 'unit_lgvDec';

        /** @var Entity $sut */
        $sut = m::mock(Entity::class)->makePartial();
        $sut->shouldReceive('validateTol')
            ->once()
            ->with($niFlag, $gop, $licType, $vehType, $lgvDecCon)
            ->andReturnTrue();
        $sut->setVehicleType(RefData::APP_VEHICLE_TYPE_LGV);
        $sut->updateTotAuthLgvVehicles(10);

        static::assertTrue($sut->updateTypeOfLicence($niFlag, $gop, $licType, $vehType, $lgvDecCon));

        static::assertEquals($vehType, $sut->getVehicleType());
        static::assertEquals($expectTotAuthLgvVehicles, $sut->getTotAuthLgvVehicles());
    }

    public function dpUpdateTypeOfLicenceFromLgvOnly()
    {
        return [
            [
                'vehType' => RefData::APP_VEHICLE_TYPE_HGV,
                'expectTotAuthLgvVehicles' => null,
            ],
            [
                'vehType' => RefData::APP_VEHICLE_TYPE_PSV,
                'expectTotAuthLgvVehicles' => null,
            ],
            [
                'vehType' => RefData::APP_VEHICLE_TYPE_LGV,
                'expectTotAuthLgvVehicles' => 10,
            ],
            [
                'vehType' => RefData::APP_VEHICLE_TYPE_MIXED,
                'expectTotAuthLgvVehicles' => 10,
            ],
        ];
    }

    /** @dataProvider dpUpdateTypeOfLicenceFromHgvOnly */
    public function testUpdateTypeOfLicenceFromHgvOnly($vehType, $expectTotAuthHgvVehicles, $expectTotAuthTrailers)
    {
        $niFlag = 'unit_niFlag';
        $gop = 'unit_goodsOrPsv';
        $licType = 'unit_licType';
        $lgvDecCon = 'unit_lgvDec';

        /** @var Entity $sut */
        $sut = m::mock(Entity::class)->makePartial();
        $sut->shouldReceive('validateTol')
            ->once()
            ->with($niFlag, $gop, $licType, $vehType, $lgvDecCon)
            ->andReturnTrue();
        $sut->setVehicleType(RefData::APP_VEHICLE_TYPE_HGV);
        $sut->updateTotAuthHgvVehicles(10);
        $sut->setTotAuthTrailers(5);

        static::assertTrue($sut->updateTypeOfLicence($niFlag, $gop, $licType, $vehType, $lgvDecCon));

        static::assertEquals($vehType, $sut->getVehicleType());
        static::assertEquals($expectTotAuthHgvVehicles, $sut->getTotAuthHgvVehicles());
        static::assertEquals($expectTotAuthTrailers, $sut->getTotAuthTrailers());
    }

    public function dpUpdateTypeOfLicenceFromHgvOnly()
    {
        return [
            [
                'vehType' => RefData::APP_VEHICLE_TYPE_HGV,
                'expectTotAuthHgvVehicles' => 10,
                'expectTotAuthTrailers' => 5,
            ],
            [
                'vehType' => RefData::APP_VEHICLE_TYPE_PSV,
                'expectTotAuthHgvVehicles' => 10,
                'expectTotAuthTrailers' => null,
            ],
            [
                'vehType' => RefData::APP_VEHICLE_TYPE_LGV,
                'expectTotAuthHgvVehicles' => null,
                'expectTotAuthTrailers' => null,
            ],
            [
                'vehType' => RefData::APP_VEHICLE_TYPE_MIXED,
                'expectTotAuthHgvVehicles' => 10,
                'expectTotAuthTrailers' => 5,
            ],
        ];
    }

    /** @dataProvider dpUpdateTypeOfLicenceFromPsv */
    public function testUpdateTypeOfLicenceFromPsv($vehType, $expectTotAuthHgvVehicles)
    {
        $niFlag = 'unit_niFlag';
        $gop = 'unit_goodsOrPsv';
        $licType = 'unit_licType';
        $lgvDecCon = 'unit_lgvDec';

        /** @var Entity $sut */
        $sut = m::mock(Entity::class)->makePartial();
        $sut->shouldReceive('validateTol')
            ->once()
            ->with($niFlag, $gop, $licType, $vehType, $lgvDecCon)
            ->andReturnTrue();
        $sut->setVehicleType(RefData::APP_VEHICLE_TYPE_PSV);
        $sut->updateTotAuthHgvVehicles(10);

        static::assertTrue($sut->updateTypeOfLicence($niFlag, $gop, $licType, $vehType, $lgvDecCon));

        static::assertEquals($vehType, $sut->getVehicleType());
        static::assertEquals($expectTotAuthHgvVehicles, $sut->getTotAuthHgvVehicles());
    }

    public function dpUpdateTypeOfLicenceFromPsv()
    {
        return [
            [
                'vehType' => RefData::APP_VEHICLE_TYPE_HGV,
                'expectTotAuthHgvVehicles' => 10,
            ],
            [
                'vehType' => RefData::APP_VEHICLE_TYPE_PSV,
                'expectTotAuthHgvVehicles' => 10,
            ],
            [
                'vehType' => RefData::APP_VEHICLE_TYPE_LGV,
                'expectTotAuthHgvVehicles' => null,
            ],
            [
                'vehType' => RefData::APP_VEHICLE_TYPE_MIXED,
                'expectTotAuthHgvVehicles' => 10,
            ],
        ];
    }

    public function testIsValidTolReturnValue()
    {
        $niFlag = 'unit_niFlag';
        $gop = 'unit_goodsOrPsv';
        $licType = 'unit_licType';
        $vehType = 'unit_vehType';
        $lgvDecCon = 'unit_lgvDec';

        /** @var Entity $sut */
        $sut = m::mock(Entity::class)->makePartial()
            ->shouldReceive('validateTol')
            ->once()
            ->with($niFlag, $gop, $licType, $vehType, $lgvDecCon)
            ->andReturn('EXPECTED')
            ->getMock();

        static::assertEquals('EXPECTED', $sut->isValidTol($niFlag, $gop, $licType, $vehType, $lgvDecCon));
    }

    public function testIsValidTolReturnNull()
    {
        /** @var Entity $sut */
        $sut = m::mock(Entity::class)->makePartial()
            ->shouldReceive('validateTol')
            ->andThrowExceptions([new ValidationException([])])
            ->getMock();

        static::assertFalse($sut->isValidTol('A', 'B', 'C', 'D', 'E'));
    }

    public function dataProviderTestHasUpgrade()
    {
        return [
            [false, null, null],
            [false, Licence::LICENCE_TYPE_RESTRICTED, null],
            [false, null, Licence::LICENCE_TYPE_RESTRICTED],
            [false, Licence::LICENCE_TYPE_RESTRICTED, Licence::LICENCE_TYPE_RESTRICTED],
            [false, Licence::LICENCE_TYPE_STANDARD_NATIONAL, Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL],
            [false, Licence::LICENCE_TYPE_STANDARD_NATIONAL, Licence::LICENCE_TYPE_SPECIAL_RESTRICTED],
            [false, Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL, Licence::LICENCE_TYPE_SPECIAL_RESTRICTED],
            [true, Licence::LICENCE_TYPE_STANDARD_NATIONAL, Licence::LICENCE_TYPE_RESTRICTED],
            [true, Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL, Licence::LICENCE_TYPE_RESTRICTED],
            [true, Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL, Licence::LICENCE_TYPE_STANDARD_NATIONAL],
        ];
    }

    /**
     * @dataProvider dataProviderTestHasUpgrade
     */
    public function testHasUpgrade($expected, $applicationLicenceTypeId, $licenceTypeId)
    {
        $sut = m::mock(Entity::class)->makePartial();

        $mockApplicationLicenceType = m::mock();
        $sut->shouldReceive('getLicenceType')->with()->andReturn($mockApplicationLicenceType);
        $mockApplicationLicenceType->shouldReceive('getId')->with()->once()->andReturn($applicationLicenceTypeId);

        $mockLicence = m::mock();
        $sut->shouldReceive('getLicence')->with()->andReturn($mockLicence);
        $mockLicenceType = m::mock();
        $mockLicence->shouldReceive('getLicenceType')->with()->andReturn($mockLicenceType);
        $mockLicenceType->shouldReceive('getId')->with()->once()->andReturn($licenceTypeId);

        $this->assertSame($expected, $sut->hasUpgrade());
    }

    public function testHasUpgradeWithoutLicenceType()
    {
        $sut = m::mock(Entity::class)->makePartial();

        $sut->shouldReceive('getLicenceType')->with()->andReturn(null);

        $mockLicence = m::mock();
        $sut->shouldReceive('getLicence')->with()->andReturn($mockLicence);
        $mockLicence->shouldReceive('getLicenceType')->with()->andReturn(null);

        $this->assertFalse($sut->hasUpgrade());
    }

    public function dpHasAuthTrailersIncrease()
    {
        return [
            [false, null, null],
            [false, null, 23],
            [true, 1, null],
            [false, 12, 22],
            [false, 4, 4],
            [true, 12, 11],
        ];
    }

    /**
     * @dataProvider dpHasAuthTrailersIncrease
     */
    public function testHasAuthTrailersIncrease($expected, $applicationCount, $licenceCount)
    {
        $sut = m::mock(Entity::class)->makePartial();

        $sut->shouldReceive('getTotAuthTrailers')->with()->andReturn($applicationCount);

        $mockLicence = m::mock();
        $sut->shouldReceive('getLicence')->with()->andReturn($mockLicence);
        $mockLicence->shouldReceive('getTotAuthTrailers')->with()->andReturn($licenceCount);

        $this->assertSame($expected, $sut->hasAuthTrailersIncrease());
    }

    public function dataProviderTestHasNewOperatingCentre()
    {
        return [
            [false, ['D', 'D', 'D']],
            [false, ['D', 'U', 'D']],
            [false, ['U', 'U', 'U']],
            [true, ['D', 'A', 'D']],
            [true, ['A', 'U', 'U']],
            [true, ['A', 'A', 'A']],
        ];
    }

    /**
     * @group applicationEntity
     */
    public function testGetApplicationDocuments()
    {
        $mockDocument1 = m::mock()
            ->shouldReceive('getcategory')
            ->andReturn('category')
            ->once()
            ->shouldReceive('getsubCategory')
            ->andReturn('subCategory')
            ->once()
            ->shouldReceive('getoperatingCentre')
            ->andReturn('operatingCentre')
            ->once()
            ->getMock();

        $mockDocument2 = m::mock()
            ->shouldReceive('getcategory')
            ->andReturn('category1')
            ->once()
            ->shouldReceive('getsubCategory')
            ->andReturn('subCategory1')
            ->never()
            ->shouldReceive('getoperatingCentre')
            ->andReturn('operatingCentre1')
            ->never()
            ->getMock();

        $documentsCollection = new ArrayCollection([$mockDocument1, $mockDocument2]);
        $expected = new ArrayCollection([$mockDocument1]);

        $this->entity->setDocuments($documentsCollection);
        $this->assertEquals(
            $expected,
            $this->entity->getApplicationDocuments('category', 'subCategory', 'operatingCentre')
        );
    }

    public function testGetApplicationOperatingCentreById()
    {
        $mockAoc1 = m::mock()
            ->shouldReceive('getid')
            ->andReturn(1)
            ->once()
            ->getMock();

        $mockAoc2 = m::mock()
            ->shouldReceive('getid')
            ->andReturn(2)
            ->once()
            ->getMock();

        $aocCollection = new ArrayCollection([$mockAoc1, $mockAoc2]);
        $this->entity->setOperatingCentres($aocCollection);

        $this->assertEquals($mockAoc1, $this->entity->getApplicationOperatingCentreById(1));
    }

    /**
     * @dataProvider dpTestUpdateFinancialHistory
     */
    public function testUpdateFinancialHistory(
        $bankrupt,
        $liquidation,
        $receivership,
        $administration,
        $disqualified,
        $insolvencyDetails,
        $insolvencyConfirmation,
        $expect
    ) {
        $this->entity->updateFinancialHistory(
            $bankrupt,
            $liquidation,
            $receivership,
            $administration,
            $disqualified,
            $insolvencyDetails,
            $insolvencyConfirmation
        );

        static::assertEquals($this->entity->getBankrupt(), $bankrupt);
        static::assertEquals($this->entity->getLiquidation(), $liquidation);
        static::assertEquals($this->entity->getReceivership(), $receivership);
        static::assertEquals($this->entity->getAdministration(), $administration);
        static::assertEquals($this->entity->getDisqualified(), $disqualified);
        static::assertEquals($this->entity->getInsolvencyDetails(), $insolvencyDetails);
        static::assertEquals($this->entity->getInsolvencyConfirmation(), $expect['insolvencyConfirmation']);
    }

    public function dpTestUpdateFinancialHistory()
    {
        return [
            [
                'bankrupt' => 'N',
                'liquidation' => 'N',
                'receivership' => 'N',
                'administration' => 'N',
                'disqualified' => 'N',
                'insolvencyDetails' => '',
                'insolvencyConfirmation' => false,
                'expect' => [
                    'insolvencyConfirmation' => null,
                ],
            ],
            [
                'bankrupt' => 'Y',
                'liquidation' => 'N',
                'receivership' => 'N',
                'administration' => 'N',
                'disqualified' => 'N',
                'insolvencyDetails' => str_repeat('X', 200),
                'insolvencyConfirmation' => 1,
                'expect' => [
                    'insolvencyConfirmation' => 'Y',
                ],
            ],
        ];
    }

    /**
     * @dataProvider dpTestUpdatePsvVehicleSize
     */
    public function testUpdatePsvVehicleSizeWhenPreviouslyNull(string $size, ?string $psvOperateSmallVhl, bool $isVariation): void
    {
        $newVehicleSize = m::mock(RefData::class);
        $newVehicleSize->expects('getId')->andReturn($size);

        $applicationCompletion = m::mock(ApplicationCompletion::class);
        $applicationCompletion->expects('clearVehiclesSizeSectionsForApplication')->never();
        $this->entity->setApplicationCompletion($applicationCompletion);
        $this->entity->setIsVariation($isVariation);

        $this->entity->updatePsvVehicleSize($newVehicleSize);

        $this->assertEquals($newVehicleSize, $this->entity->getPsvWhichVehicleSizes());
        $this->assertEquals($psvOperateSmallVhl, $this->entity->getPsvOperateSmallVhl());
    }

    /**
     * @dataProvider dpTestUpdatePsvVehicleSize
     */
    public function testUpdatePsvVehicleSizeWithNoChange(string $size, ?string $psvOperateSmallVhl, bool $isVariation): void
    {
        $oldVehicleSize = m::mock(RefData::class);
        $oldVehicleSize->expects('getId')->andReturn($size);

        $applicationCompletion = m::mock(ApplicationCompletion::class);
        $applicationCompletion->expects('clearVehiclesSizeSectionsForApplication')->never();
        $this->entity->setApplicationCompletion($applicationCompletion);
        $this->entity->setIsVariation($isVariation);

        //set the fields that we later will modify
        $this->entity->setPsvWhichVehicleSizes($oldVehicleSize);
        $this->entity->setPsvOperateSmallVhl(null);

        $newVehicleSize = m::mock(RefData::class);
        $newVehicleSize->expects('getId')->andReturn($size);

        $this->entity->updatePsvVehicleSize($newVehicleSize);

        $this->assertEquals($newVehicleSize, $this->entity->getPsvWhichVehicleSizes());
        $this->assertEquals($psvOperateSmallVhl, $this->entity->getPsvOperateSmallVhl());
    }

    /**
     * Tests fields are cleared when the vehicle size is changed
     *
     * @dataProvider dpTestUpdatePsvVehicleSize
     */
    public function testUpdatePsvVehicleSizeWithChange(string $size, ?string $psvOperateSmallVhl, bool $isVariation): void
    {
        $oldVehicleSize = m::mock(RefData::class);
        $oldVehicleSize->expects('getId')->andReturn('id to be changed');

        $applicationCompletion = m::mock(ApplicationCompletion::class);
        $applicationCompletion->expects('clearVehiclesSizeSectionsForApplication')->times($isVariation ? 0 : 1)->withNoArgs()->andReturnSelf();
        $this->entity->setApplicationCompletion($applicationCompletion);
        $this->entity->setIsVariation($isVariation);

        //set the fields that we later will modify
        $this->entity->setPsvWhichVehicleSizes($oldVehicleSize);
        $this->entity->setPsvOperateSmallVhl(null);
        $this->entity->setPsvOccupationRecordsConfirmation('Y');
        $this->entity->setPsvIncomeRecordsConfirmation('Y');
        $this->entity->setPsvSmallVhlNotes('notes');
        $this->entity->setPsvTotalVehicleSmall(10);
        $this->entity->setPsvTotalVehicleLarge(11);
        $this->entity->setPsvNoSmallVhlConfirmation('Y');
        $this->entity->setPsvSmallVhlConfirmation('Y');
        $this->entity->setPsvOnlyLimousinesConfirmation('Y');
        $this->entity->setPsvNoLimousineConfirmation('Y');
        $this->entity->setPsvLimousines('Y');
        $this->entity->setSmallVehicleEvidenceUploaded(Entity::FINANCIAL_EVIDENCE_UPLOADED);
        $this->entity->setOccupationEvidenceUploaded(Entity::FINANCIAL_EVIDENCE_UPLOAD_LATER);

        $newVehicleSize = m::mock(RefData::class);
        $newVehicleSize->expects('getId')->andReturn($size);

        $this->entity->updatePsvVehicleSize($newVehicleSize);

        $this->assertEquals($newVehicleSize, $this->entity->getPsvWhichVehicleSizes());
        $this->assertEquals($psvOperateSmallVhl, $this->entity->getPsvOperateSmallVhl());
        $this->assertNull($this->entity->getPsvOccupationRecordsConfirmation());
        $this->assertNull($this->entity->getPsvIncomeRecordsConfirmation());
        $this->assertNull($this->entity->getPsvSmallVhlNotes());
        $this->assertNull($this->entity->getPsvTotalVehicleSmall());
        $this->assertNull($this->entity->getPsvTotalVehicleLarge());
        $this->assertNull($this->entity->getPsvNoSmallVhlConfirmation());
        $this->assertNull($this->entity->getPsvSmallVhlConfirmation());
        $this->assertNull($this->entity->getPsvOnlyLimousinesConfirmation());
        $this->assertNull($this->entity->getPsvNoLimousineConfirmation());
        $this->assertNull($this->entity->getPsvLimousines());
        $this->assertNull($this->entity->getSmallVehicleEvidenceUploaded());
        $this->assertNull($this->entity->getOccupationEvidenceUploaded());
    }

    public function dpTestUpdatePsvVehicleSize(): array
    {
        return [
            'small variation' => [
                Entity::PSV_VEHICLE_SIZE_SMALL,
                'Y',
                true,
            ],
            'large variation' => [
                Entity::PSV_VEHICLE_SIZE_MEDIUM_LARGE,
                'N',
                true,
            ],
            'both variation' => [
                Entity::PSV_VEHICLE_SIZE_BOTH,
                null,
                true,
            ],
            'small new app' => [
                Entity::PSV_VEHICLE_SIZE_SMALL,
                'Y',
                false,
            ],
            'large new app' => [
                Entity::PSV_VEHICLE_SIZE_MEDIUM_LARGE,
                'N',
                false,
            ],
            'both new app' => [
                Entity::PSV_VEHICLE_SIZE_BOTH,
                null,
                false,
            ],
        ];
    }

    public function testUpdatePsvNoveltyVehicles(): void
    {
        $usingLimousines = 'Y';
        $psvNoLimousineConfirmation = 'N';
        $psvOnlyLimousineConfirmation = 'Y';

        $this->entity->updatePsvNoveltyVehicles(
            $usingLimousines,
            $psvNoLimousineConfirmation,
            $psvOnlyLimousineConfirmation
        );

        $this->assertEquals($usingLimousines, $this->entity->getPsvLimousines());
        $this->assertEquals($psvNoLimousineConfirmation, $this->entity->getPsvNoLimousineConfirmation());
        $this->assertEquals($psvOnlyLimousineConfirmation, $this->entity->getPsvOnlyLimousinesConfirmation());
    }

    public function testUpdateWrittenEvidence(): void
    {
        $psvSmallVhlNotes = 'notes';
        $psvTotalVehicleSmall = 10;
        $psvTotalVehicleLarge = 11;

        $this->entity->updateWrittenEvidence(
            $psvSmallVhlNotes,
            $psvTotalVehicleSmall,
            $psvTotalVehicleLarge
        );

        $this->assertEquals($psvSmallVhlNotes, $this->entity->getPsvSmallVhlNotes());
        $this->assertEquals($psvTotalVehicleSmall, $this->entity->getPsvTotalVehicleSmall());
        $this->assertEquals($psvTotalVehicleLarge, $this->entity->getPsvTotalVehicleLarge());
    }

    public function testUpdateMainOccupationUndertakings(): void
    {
        $psvOccupationRecordsConfirmation = 'Y';
        $psvIncomeRecordsConfirmation = 'N'; //wouldn't happen in reality but allows differentiation for this test

        $this->entity->updateMainOccupationUndertakings(
            $psvOccupationRecordsConfirmation,
            $psvIncomeRecordsConfirmation
        );

        $this->assertEquals($psvOccupationRecordsConfirmation, $this->entity->getPsvOccupationRecordsConfirmation());
        $this->assertEquals($psvIncomeRecordsConfirmation, $this->entity->getPsvIncomeRecordsConfirmation());
    }

    /**
     * @dataProvider dpTestIsMainOccupationUndertakingsSectionCompleted
     */
    public function testIsMainOccupationUndertakingsSectionCompleted(
        bool $isCompleted,
        ?string $psvOccupationRecordsConfirmation,
        ?string $psvIncomeRecordsConfirmation
    ): void
    {
        $this->entity->setPsvOccupationRecordsConfirmation($psvOccupationRecordsConfirmation);
        $this->entity->setPsvIncomeRecordsConfirmation($psvIncomeRecordsConfirmation);
        $this->assertEquals($isCompleted, $this->entity->isSectionCompleted('PsvMainOccupationUndertakings'));
    }

    public function dpTestIsMainOccupationUndertakingsSectionCompleted(): array
    {
        return [
            'both fields null' => [
                false,
                null,
                null,
            ],
            'missing occupation record' => [
                false,
                null,
                'Y',
            ],
            'missing income record' => [
                false,
                'Y',
                null,
            ],
            'both fields present' => [
                true,
                'Y',
                'Y',
            ],
        ];
    }

    /**
     * @dataProvider dpTestIsWrittenEvidenceSectionCompleted
     */
    public function testIsWrittenEvidenceSectionCompleted(
        bool $isCompleted,
        ?string $psvSmallVhlNotes,
        ?int $psvTotalVehicleSmall,
        ?int $psvTotalVehicleLarge
    ): void
    {
        $this->entity->setPsvSmallVhlNotes($psvSmallVhlNotes);
        $this->entity->setPsvTotalVehicleSmall($psvTotalVehicleSmall);
        $this->entity->setPsvTotalVehicleLarge($psvTotalVehicleLarge);
        $this->assertEquals($isCompleted, $this->entity->isSectionCompleted('PsvSmallPartWritten'));
    }

    public function dpTestIsWrittenEvidenceSectionCompleted(): array
    {
        return [
            'all fields null' => [
                false,
                null,
                null,
                null,
            ],
            'notes field missing' => [
                false,
                null,
                10,
                20,
            ],
            'small vehicles missing' => [
                false,
                'notes',
                null,
                20,
            ],
            'large vehicles missing' => [
                false,
                'notes',
                10,
                null,
            ],
            'all fields present' => [
                true,
                'notes',
                10,
                20,
            ],
        ];
    }

    public function testIsNoveltyVehiclesSectionCompletedWithEmptyLimoField(): void
    {
        $this->entity->setPsvLimousines(null);
        $this->assertFalse($this->entity->isSectionCompleted('PsvOperateNovelty'));
    }

    public function testIsNoveltyVehiclesSectionCompletedUsingLimosSmallVehicles(): void
    {
        $vehicleSize = m::mock(RefData::class);
        $vehicleSize->expects('getId')->andReturn(Entity::PSV_VEHICLE_SIZE_SMALL);

        $this->entity->setPsvWhichVehicleSizes($vehicleSize);
        $this->entity->setPsvLimousines('Y');

        $this->assertTrue($this->entity->isSectionCompleted('PsvOperateNovelty'));
    }

    /**
     * @dataProvider dpTestIsNoveltyVehiclesSectionCompletedUsingLimosNotSmallVehicles
     */
    public function testIsNoveltyVehiclesSectionCompletedUsingLimosNotSmallVehicles(
        bool $isCompleted,
        string $size,
        ?string $psvNoLimousineConfirmation,
        ?string $psvOnlyLimousineConfirmation
    ): void
    {
        $vehicleSize = m::mock(RefData::class);
        $vehicleSize->expects('getId')->andReturn($size);

        $this->entity->setPsvWhichVehicleSizes($vehicleSize);
        $this->entity->setPsvLimousines('Y');
        $this->entity->setPsvNoLimousineConfirmation($psvNoLimousineConfirmation);
        $this->entity->setPsvOnlyLimousinesConfirmation($psvOnlyLimousineConfirmation);

        $this->assertEquals($isCompleted, $this->entity->isSectionCompleted('PsvOperateNovelty'));
    }

    public function dpTestIsNoveltyVehiclesSectionCompletedUsingLimosNotSmallVehicles(): array
    {
        return [
            'both with no confirmation' => [
                false,
                Entity::PSV_VEHICLE_SIZE_BOTH,
                null,
                null,
            ],
            'large with no confirmation' => [
                false,
                Entity::PSV_VEHICLE_SIZE_MEDIUM_LARGE,
                null,
                null,
            ],
            'both with incorrect confirmation' => [
                false,
                Entity::PSV_VEHICLE_SIZE_BOTH,
                'Y',
                null,
            ],
            'large with incorrect confirmation' => [
                false,
                Entity::PSV_VEHICLE_SIZE_MEDIUM_LARGE,
                'Y',
                null,
            ],
            'both with correct confirmation' => [
                true,
                Entity::PSV_VEHICLE_SIZE_BOTH,
                null,
                'Y',
            ],
            'large with correct confirmation' => [
                true,
                Entity::PSV_VEHICLE_SIZE_MEDIUM_LARGE,
                null,
                'Y',
            ],
        ];
    }

    /**
     * @dataProvider dpTestIsNoveltyVehiclesSectionCompletedNotUsingLimos
     */
    public function testIsNoveltyVehiclesSectionCompletedNotUsingLimos(
        bool $isCompleted,
        ?string $psvNoLimousineConfirmation,
        ?string $psvOnlyLimousineConfirmation
    ): void
    {
        $this->entity->setPsvLimousines('N');
        $this->entity->setPsvNoLimousineConfirmation($psvNoLimousineConfirmation);
        $this->entity->setPsvOnlyLimousinesConfirmation($psvOnlyLimousineConfirmation);
        $this->assertEquals($isCompleted, $this->entity->isSectionCompleted('PsvOperateNovelty'));
    }

    public function dpTestIsNoveltyVehiclesSectionCompletedNotUsingLimos(): array
    {
        return [
            'no confirmation' => [
                false,
                null,
                null,
            ],
            'with wrong confirmation' => [
                false,
                null,
                'Y',
            ],
            'with correct confirmation' => [
                true,
                'Y',
                null,
            ],
        ];
    }

    /**
     * @dataProvider dpTestPsvEvidenceSectionsCompleted
     */
    public function testPsvEvidenceSectionsCompleted(bool $isCompleted, ?int $value): void
    {
        $this->entity->setSmallVehicleEvidenceUploaded($value);
        $this->entity->setOccupationEvidenceUploaded($value);
        $this->assertEquals($isCompleted, $this->entity->isSectionCompleted('PsvDocumentaryEvidenceSmall'));
        $this->assertEquals($isCompleted, $this->entity->isSectionCompleted('PsvDocumentaryEvidenceLarge'));
    }

    public function dpTestPsvEvidenceSectionsCompleted(): array
    {
        return [
            'field is null' => [
                false,
                null,
            ],
            'uploaded' => [
                true,
                Entity::FINANCIAL_EVIDENCE_UPLOADED,
            ],
            'upload later' => [
                true,
                Entity::FINANCIAL_EVIDENCE_UPLOAD_LATER,
            ],
        ];
    }

    public function testOperateLargeVehiclesSectionCompleted(): void
    {
        $this->assertFalse($this->entity->isSectionCompleted('PsvOperateLarge'));

        $this->entity->setPsvNoSmallVhlConfirmation('Y');
        $this->assertTrue($this->entity->isSectionCompleted('PsvOperateLarge'));
    }

    public function testOperateSmallVehiclesSectionCompleted(): void
    {
        $this->assertFalse($this->entity->isSectionCompleted('PsvOperateSmall'));

        $this->entity->setPsvOperateSmallVhl('Y');
        $this->assertTrue($this->entity->isSectionCompleted('PsvOperateSmall'));
    }

    public function testSmallVehicleConditionsSectionCompleted(): void
    {
        $this->assertFalse($this->entity->isSectionCompleted('PsvSmallConditions'));

        $this->entity->setPsvSmallVhlConfirmation('Y');
        $this->assertTrue($this->entity->isSectionCompleted('PsvSmallConditions'));
    }

    public function testVehiclesSizeSectionCompleted(): void
    {
        $this->assertFalse($this->entity->isSectionCompleted('VehiclesSize'));

        $this->entity->setPsvOperateSmallVhl(Entity::PSV_VEHICLE_SIZE_SMALL);
        $this->assertTrue($this->entity->isSectionCompleted('PsvOperateSmall'));
    }

    public function testIsSectionCompletedNoValidation(): void
    {
        $applicationSection = 'made up section';
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('There is no validation for this section: ' . $applicationSection);

        $this->entity->isSectionCompleted($applicationSection);
    }

    /**
     * @dataProvider dpTestValidateFinancialHistory
     */
    public function testValidateFinancialHistory($flags, $text)
    {
        /** @var Entity $sut */
        $sut = m::mock($this->entity)->makePartial();

        try {
            static::assertTrue($sut->validateFinancialHistory($flags, $text));
        } catch (ValidationException $e) {
            static::assertEquals(
                [
                    'insolvencyDetails' => [
                        Entity::ERROR_FINANCIAL_HISTORY_DETAILS_REQUIRED => 'FhAdditionalInfo.api.validation.too_short',
                    ],
                ],
                $e->getMessages()
            );
        }
    }

    public function dpTestValidateFinancialHistory()
    {
        $a50 = str_repeat('a', 50);
        $b50 = str_repeat('b', 50);
        $c45 = str_repeat('c', 45);

        return [
            [
                'flags' => ['N'],
                'text' => '',
                'expect' => true,
            ],
            [
                'flags' => ['Y', 'N'],
                'text' => 'to short',
                'expect' => false,
            ],
            [
                'flags' => ['Y'],
                'text' => $a50 . PHP_EOL . ' ' . $b50 . PHP_EOL . ' ' . PHP_EOL . $c45,
                'expect' => false,
            ],
            [
                'flags' => ['Y'],
                'text' => $a50 . PHP_EOL . ' ' . $b50 . PHP_EOL . ' ' . PHP_EOL . $a50,
                'expect' => true,
            ],
        ];
    }


    /**
     * @dataProvider dataProviderTestHasNewOperatingCentre
     *
     * @param bool  $expected
     * @param array $operatingCenterActions
     */
    public function testHasNewOperatingCentre($expected, $operatingCenterActions)
    {
        $sut = m::mock(Entity::class)->makePartial();

        $application = m::mock(Entity::class)->makePartial();
        $oc = m::mock(OperatingCentre::class)->makePartial();

        foreach ($operatingCenterActions as $action) {
            $applicationOperatingCentre = new ApplicationOperatingCentre($application, $oc);
            $applicationOperatingCentre->setAction($action);
            $centres[] = $applicationOperatingCentre;
        }

        $sut->shouldReceive('getOperatingCentres')->with()->andReturn($centres);

        $this->assertSame($expected, $sut->hasNewOperatingCentre());
    }

    public function setupOperatingCentres()
    {
        $operatingCentreValues = [
            [2, 6, 9],
            [3, 4, 4],
            [433, 5, 2],
        ];

        foreach ($operatingCentreValues as $values) {
            [$id, $noOfTrailersRequired, $noOfVehiclesRequired] = $values;
            $oc = new \Dvsa\Olcs\Api\Entity\OperatingCentre\OperatingCentre();
            $oc->setId($id);
            $aoc = new ApplicationOperatingCentre(m::mock(Entity::class)->makePartial(), $oc);
            $aoc->setOperatingCentre($oc);
            $aoc->setNoOfTrailersRequired($noOfTrailersRequired);
            $aoc->setNoOfVehiclesRequired($noOfVehiclesRequired);
            $centres[] = $aoc;
        }

        return $centres;
    }

    public function testHasIncreaseInOperatingCentreNoChanges()
    {
        $sut = m::mock(Entity::class)->makePartial();

        $applicationCentres = $this->setupOperatingCentres();
        $sut->shouldReceive('getOperatingCentres')->with()->andReturn($applicationCentres);
        $licenceCentres = $this->setupOperatingCentres();
        $sut->shouldReceive('getLicence->getOperatingCentres')->with()->andReturn($licenceCentres);

        $this->assertSame(false, $sut->hasIncreaseInOperatingCentre());
    }

    public function testHasIncreaseInOperatingCentreVehiclesIncreased()
    {
        $sut = m::mock(Entity::class)->makePartial();

        $applicationCentres = $this->setupOperatingCentres();
        $applicationCentres[1]->setNoOfVehiclesRequired(123);
        $sut->shouldReceive('getOperatingCentres')->with()->andReturn($applicationCentres);
        $licenceCentres = $this->setupOperatingCentres();
        $sut->shouldReceive('getLicence->getOperatingCentres')->with()->andReturn($licenceCentres);

        $this->assertSame(true, $sut->hasIncreaseInOperatingCentre());
    }

    public function testHasIncreaseInOperatingCentreTrailersIncreased()
    {
        $sut = m::mock(Entity::class)->makePartial();

        $applicationCentres = $this->setupOperatingCentres();
        $applicationCentres[1]->setNoOfTrailersRequired(123);
        $sut->shouldReceive('getOperatingCentres')->with()->andReturn($applicationCentres);
        $licenceCentres = $this->setupOperatingCentres();
        $sut->shouldReceive('getLicence->getOperatingCentres')->with()->andReturn($licenceCentres);

        $this->assertSame(true, $sut->hasIncreaseInOperatingCentre());
    }

    public function testHasIncreaseInOperatingCentreCentreDeleted()
    {
        $sut = m::mock(Entity::class)->makePartial();

        $applicationCentres = $this->setupOperatingCentres();
        $applicationCentres[1]->setAction('D');
        $applicationCentres[1]->setNoOfTrailersRequired(12121);
        $sut->shouldReceive('getOperatingCentres')->with()->andReturn($applicationCentres);
        $licenceCentres = $this->setupOperatingCentres();
        $sut->shouldReceive('getLicence->getOperatingCentres')->with()->andReturn($licenceCentres);

        $this->assertSame(false, $sut->hasIncreaseInOperatingCentre());
    }

    public function testHasIncreaseInOperatingCentreNoOperatingCentreChanges()
    {
        $sut = m::mock(Entity::class)->makePartial();

        $applicationCentres = [];
        $sut->shouldReceive('getOperatingCentres')->with()->andReturn($applicationCentres);
        $licenceCentres = $this->setupOperatingCentres();
        $sut->shouldReceive('getLicence->getOperatingCentres')->with()->andReturn($licenceCentres);

        $this->assertSame(false, $sut->hasIncreaseInOperatingCentre());
    }

    public function testCanHaveInterimLicencePsv()
    {
        $sut = m::mock(Entity::class)->makePartial();

        $sut->shouldReceive('getGoodsOrPsv->getId')->with()->once()->andReturn(Licence::LICENCE_CATEGORY_PSV);

        $this->assertFalse($sut->canHaveInterimLicence());
    }

    public function testCanHaveInterimLicenceApplication()
    {
        $sut = m::mock(Entity::class)->makePartial();

        $sut->shouldReceive('getGoodsOrPsv->getId')->with()->once()->andReturn(Licence::LICENCE_CATEGORY_GOODS_VEHICLE);
        $sut->shouldReceive('getIsVariation')->with()->once()->andReturn(false);

        $this->assertTrue($sut->canHaveInterimLicence());
    }

    public function testCanHaveInterimLicenceWithHgvAuthorisationIncreased()
    {
        $sut = m::mock(Entity::class)->makePartial()->shouldAllowMockingProtectedMethods();

        $sut->shouldReceive('getGoodsOrPsv->getId')->with()->once()->andReturn(Licence::LICENCE_CATEGORY_GOODS_VEHICLE);
        $sut->shouldReceive('getIsVariation')->with()->once()->andReturn(true);

        $sut->shouldReceive('hasHgvAuthorisationIncreased')->withNoArgs()->once()->andReturn(true);

        $this->assertSame(true, $sut->canHaveInterimLicence());
    }

    public function testCanHaveInterimLicenceWithLgvAuthorisationIncreased()
    {
        $sut = m::mock(Entity::class)->makePartial()->shouldAllowMockingProtectedMethods();

        $sut->shouldReceive('getGoodsOrPsv->getId')->with()->once()->andReturn(Licence::LICENCE_CATEGORY_GOODS_VEHICLE);
        $sut->shouldReceive('getIsVariation')->with()->once()->andReturn(true);

        $sut->shouldReceive('hasHgvAuthorisationIncreased')->withNoArgs()->once()->andReturn(false);
        $sut->shouldReceive('hasLgvAuthorisationIncreased')->withNoArgs()->once()->andReturn(true);

        $this->assertSame(true, $sut->canHaveInterimLicence());
    }

    public function testCanHaveInterimLicenceWithAuthTrailersIncreased()
    {
        $sut = m::mock(Entity::class)->makePartial()->shouldAllowMockingProtectedMethods();

        $sut->shouldReceive('getGoodsOrPsv->getId')->with()->once()->andReturn(Licence::LICENCE_CATEGORY_GOODS_VEHICLE);
        $sut->shouldReceive('getIsVariation')->with()->once()->andReturn(true);

        $sut->shouldReceive('hasHgvAuthorisationIncreased')->withNoArgs()->once()->andReturn(false);
        $sut->shouldReceive('hasLgvAuthorisationIncreased')->withNoArgs()->once()->andReturn(false);
        $sut->shouldReceive('hasAuthTrailersIncrease')->with()->once()->andReturn(true);

        $this->assertSame(true, $sut->canHaveInterimLicence());
    }

    public function testCanHaveInterimLicenceWithUpgrade()
    {
        $sut = m::mock(Entity::class)->makePartial()->shouldAllowMockingProtectedMethods();

        $sut->shouldReceive('getGoodsOrPsv->getId')->with()->once()->andReturn(Licence::LICENCE_CATEGORY_GOODS_VEHICLE);
        $sut->shouldReceive('getIsVariation')->with()->once()->andReturn(true);

        $sut->shouldReceive('hasHgvAuthorisationIncreased')->withNoArgs()->once()->andReturn(false);
        $sut->shouldReceive('hasLgvAuthorisationIncreased')->withNoArgs()->once()->andReturn(false);
        $sut->shouldReceive('hasAuthTrailersIncrease')->with()->once()->andReturn(false);
        $sut->shouldReceive('hasUpgrade')->with()->once()->andReturn(true);

        $this->assertSame(true, $sut->canHaveInterimLicence());
    }

    public function testCanHaveInterimLicenceWithNewOperatingCentre()
    {
        $sut = m::mock(Entity::class)->makePartial()->shouldAllowMockingProtectedMethods();

        $sut->shouldReceive('getGoodsOrPsv->getId')->with()->once()->andReturn(Licence::LICENCE_CATEGORY_GOODS_VEHICLE);
        $sut->shouldReceive('getIsVariation')->with()->once()->andReturn(true);

        $sut->shouldReceive('hasHgvAuthorisationIncreased')->withNoArgs()->once()->andReturn(false);
        $sut->shouldReceive('hasLgvAuthorisationIncreased')->withNoArgs()->once()->andReturn(false);
        $sut->shouldReceive('hasAuthTrailersIncrease')->with()->once()->andReturn(false);
        $sut->shouldReceive('hasUpgrade')->with()->once()->andReturn(false);
        $sut->shouldReceive('hasNewOperatingCentre')->with()->once()->andReturn(true);

        $this->assertSame(true, $sut->canHaveInterimLicence());
    }

    public function testCanHaveInterimLicenceWithIncreaseInOperatingCentre()
    {
        $sut = m::mock(Entity::class)->makePartial()->shouldAllowMockingProtectedMethods();

        $sut->shouldReceive('getGoodsOrPsv->getId')->with()->once()->andReturn(Licence::LICENCE_CATEGORY_GOODS_VEHICLE);
        $sut->shouldReceive('getIsVariation')->with()->once()->andReturn(true);

        $sut->shouldReceive('hasHgvAuthorisationIncreased')->withNoArgs()->once()->andReturn(false);
        $sut->shouldReceive('hasLgvAuthorisationIncreased')->withNoArgs()->once()->andReturn(false);
        $sut->shouldReceive('hasAuthTrailersIncrease')->with()->once()->andReturn(false);
        $sut->shouldReceive('hasUpgrade')->with()->once()->andReturn(false);
        $sut->shouldReceive('hasNewOperatingCentre')->with()->once()->andReturn(false);
        $sut->shouldReceive('hasIncreaseInOperatingCentre')->with()->once()->andReturn(true);

        $this->assertSame(true, $sut->canHaveInterimLicence());
    }

    public function testCanHaveInterimLicenceWhenItCannot()
    {
        $sut = m::mock(Entity::class)->makePartial()->shouldAllowMockingProtectedMethods();

        $sut->shouldReceive('getGoodsOrPsv->getId')->with()->once()->andReturn(Licence::LICENCE_CATEGORY_GOODS_VEHICLE);
        $sut->shouldReceive('getIsVariation')->with()->once()->andReturn(true);

        $sut->shouldReceive('hasHgvAuthorisationIncreased')->withNoArgs()->once()->andReturn(false);
        $sut->shouldReceive('hasLgvAuthorisationIncreased')->withNoArgs()->once()->andReturn(false);
        $sut->shouldReceive('hasAuthTrailersIncrease')->with()->once()->andReturn(false);
        $sut->shouldReceive('hasUpgrade')->with()->once()->andReturn(false);
        $sut->shouldReceive('hasNewOperatingCentre')->with()->once()->andReturn(false);
        $sut->shouldReceive('hasIncreaseInOperatingCentre')->with()->once()->andReturn(false);

        $this->assertSame(false, $sut->canHaveInterimLicence());
    }

    public function testIsLicenceUpgradeApplication()
    {
        $sut = m::mock(Entity::class)->makePartial()->shouldAllowMockingProtectedMethods();

        $sut->shouldReceive('getIsVariation')->with()->once()->andReturn(false);

        $this->assertSame(false, $sut->isLicenceUpgrade());
    }

    public function dataProviderTestIsLicenceUpgrade()
    {
        return [
            [false, Licence::LICENCE_TYPE_RESTRICTED, Licence::LICENCE_TYPE_RESTRICTED],
            [false, Licence::LICENCE_TYPE_RESTRICTED, Licence::LICENCE_TYPE_SPECIAL_RESTRICTED],
            [true, Licence::LICENCE_TYPE_RESTRICTED, Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL],
            [true, Licence::LICENCE_TYPE_RESTRICTED, Licence::LICENCE_TYPE_STANDARD_NATIONAL],
        ];
    }

    /**
     * @dataProvider dataProviderTestIsLicenceUpgrade
     *
     * @param bool   $expected
     * @param string $licenceType
     * @param string $applicationLicenceType
     */
    public function testIsLicenceUpgrade($expected, $licenceType, $applicationLicenceType)
    {
        $sut = m::mock(Entity::class)->makePartial()->shouldAllowMockingProtectedMethods();

        $sut->shouldReceive('getIsVariation')->with()->once()->andReturn(true);

        $sut->shouldReceive('getLicence->getLicenceType->getId')->with()->once()->andReturn($licenceType);
        $sut->shouldReceive('getLicenceType->getId')->with()->once()->andReturn($applicationLicenceType);

        $this->assertSame($expected, $sut->isLicenceUpgrade());
    }

    /**
     * @group applicationEntity
     */
    public function testGetOtherLicencesByType()
    {
        $mockOtherLicence1 = m::mock()
            ->shouldReceive('getpreviousLicenceType')
            ->andReturn('type')
            ->once()
            ->getMock();

        $mockOtherLicence2 = m::mock()
            ->shouldReceive('getpreviousLicenceType')
            ->andReturn('type1')
            ->once()
            ->getMock();

        $otherLicencesCollection = new ArrayCollection([$mockOtherLicence1, $mockOtherLicence2]);
        $expected = new ArrayCollection([$mockOtherLicence1]);

        $this->entity->setOtherLicences($otherLicencesCollection);
        $this->assertEquals($expected, $this->entity->getOtherLicencesByType('type'));
    }

    public function testUpdateLicenceHistory()
    {
        $this->entity->updateLicenceHistory('Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y');
        $this->assertEquals($this->entity->getPrevHasLicence(), 'Y');
        $this->assertEquals($this->entity->getPrevHadLicence(), 'Y');
        $this->assertEquals($this->entity->getPrevBeenRefused(), 'Y');
        $this->assertEquals($this->entity->getPrevBeenRevoked(), 'Y');
        $this->assertEquals($this->entity->getPrevBeenAtPi(), 'Y');
        $this->assertEquals($this->entity->getPrevBeenDisqualifiedTc(), 'Y');
        $this->assertEquals($this->entity->getPrevPurchasedAssets(), 'Y');
    }

    /**
     * @dataProvider codeProvider
     */
    public function testGetCode($isVariation, $isUpgrade, $goodsOrPsv, $licenceType, $expected)
    {
        $sut = m::mock(Entity::class)->makePartial();

        $sut->shouldReceive('getIsVariation')->andReturn($isVariation);
        $sut->shouldReceive('getGoodsOrPsv->getId')->andReturn($goodsOrPsv);
        $sut->shouldReceive('getLicenceType->getId')->andReturn($licenceType);
        $sut->shouldReceive('isRealUpgrade')->andReturn($isUpgrade);

        $this->assertEquals($expected, $sut->getCode());
    }

    public function codeProvider()
    {
        return [
            'gv new app' => [
                false,
                null,
                Licence::LICENCE_CATEGORY_GOODS_VEHICLE,
                Licence::LICENCE_TYPE_STANDARD_NATIONAL,
                'GV79'
            ],
            'psv new app' => [
                false,
                null,
                Licence::LICENCE_CATEGORY_PSV,
                Licence::LICENCE_TYPE_STANDARD_NATIONAL,
                'PSV421'
            ],
            'psv sr new app' => [
                false,
                null,
                Licence::LICENCE_CATEGORY_PSV,
                Licence::LICENCE_TYPE_SPECIAL_RESTRICTED,
                'PSV356'
            ],
            'gv variation upgrade' => [
                true,
                true,
                Licence::LICENCE_CATEGORY_GOODS_VEHICLE,
                Licence::LICENCE_TYPE_STANDARD_NATIONAL,
                'GV80A'
            ],
            'gv variation no upgrade' => [
                true,
                false,
                Licence::LICENCE_CATEGORY_GOODS_VEHICLE,
                Licence::LICENCE_TYPE_STANDARD_NATIONAL,
                'GV81'
            ],
            'psv variation upgrade' => [
                true,
                true,
                Licence::LICENCE_CATEGORY_PSV,
                Licence::LICENCE_TYPE_STANDARD_NATIONAL,
                'PSV431A'
            ],
            'psv variation no upgrade' => [
                true,
                false,
                Licence::LICENCE_CATEGORY_PSV,
                Licence::LICENCE_TYPE_STANDARD_NATIONAL,
                'PSV431'
            ],
        ];
    }

    public function testGetApplicationType()
    {
        $sut = m::mock(Entity::class)->makePartial();

        $sut->setIsVariation(true);
        $this->assertEquals(Entity::APPLICATION_TYPE_VARIATION, $sut->getApplicationType());

        $sut->setIsVariation(false);
        $this->assertEquals(Entity::APPLICATION_TYPE_NEW, $sut->getApplicationType());
    }

    /** @dataProvider dpTestGetApplicationTypeDescription */
    public function testGetApplicationTypeDescription($appType, $expect)
    {
        /** @var Entity $sut */
        $sut = m::mock(Entity::class)->makePartial()
            ->shouldReceive('getApplicationType')
            ->once()
            ->andReturn($appType)
            ->getMock();

        static::assertEquals($expect, $sut->getApplicationTypeDescription());
    }

    public function dpTestGetApplicationTypeDescription()
    {
        return [
            [
                'appType' => Entity::APPLICATION_TYPE_VARIATION,
                'expect' => Entity::APPLICATION_TYPE_VARIATION_DESCRIPTION,
            ],
            [
                'appType' => Entity::APPLICATION_TYPE_NEW,
                'expect' => Entity::APPLICATION_TYPE_NEW_DESCRIPTION,
            ],
        ];
    }

    /**
     * @dataProvider canSubmitProvider
     */
    public function testCanSubmit($status, $expected)
    {
        $sut = m::mock(Entity::class)->makePartial();

        $sut->shouldReceive('getStatus->getId')->once()->andReturn($status);
        $this->assertEquals($expected, $sut->canSubmit());
    }

    public function canSubmitProvider()
    {
        return [
            [Entity::APPLICATION_STATUS_NOT_SUBMITTED, true],
            [Entity::APPLICATION_STATUS_GRANTED, false],
            [Entity::APPLICATION_STATUS_UNDER_CONSIDERATION, false],
            [Entity::APPLICATION_STATUS_VALID, false],
            [Entity::APPLICATION_STATUS_WITHDRAWN, false],
            [Entity::APPLICATION_STATUS_REFUSED, false],
            [Entity::APPLICATION_STATUS_NOT_TAKEN_UP, false],
        ];
    }

    /**
     * @dataProvider canCreateCaseProvider
     */
    public function testCanCreateCase($status, $licNo, $expected)
    {
        $sut = m::mock(Entity::class)->makePartial();

        $sut->shouldReceive('getStatus->getId')->once()->andReturn($status);
        $sut->shouldReceive('getLicence->getLicNo')->andReturn($licNo);
        $this->assertEquals($expected, $sut->canCreateCase());
    }

    public function canCreateCaseProvider()
    {
        $licNo = 12345;

        return [
            [Entity::APPLICATION_STATUS_NOT_SUBMITTED, null, false],
            [Entity::APPLICATION_STATUS_GRANTED, null, false],
            [Entity::APPLICATION_STATUS_UNDER_CONSIDERATION, null, false],
            [Entity::APPLICATION_STATUS_VALID, null, false],
            [Entity::APPLICATION_STATUS_WITHDRAWN, null, false],
            [Entity::APPLICATION_STATUS_REFUSED, null, false],
            [Entity::APPLICATION_STATUS_NOT_TAKEN_UP, null, false],
            [Entity::APPLICATION_STATUS_NOT_SUBMITTED, $licNo, false],
            [Entity::APPLICATION_STATUS_GRANTED, $licNo, true],
            [Entity::APPLICATION_STATUS_UNDER_CONSIDERATION, $licNo, true],
            [Entity::APPLICATION_STATUS_VALID, $licNo, true],
            [Entity::APPLICATION_STATUS_WITHDRAWN, $licNo, true],
            [Entity::APPLICATION_STATUS_REFUSED, $licNo, true],
            [Entity::APPLICATION_STATUS_NOT_TAKEN_UP, $licNo, true],
        ];
    }

    /**
     * @dataProvider goodsOrPsvHelperProvider
     */
    public function testIsGoodsAndIsPsvHelperMethods($goodsOrPsv, $isGoods, $isPsv)
    {
        $sut = m::mock(Entity::class)->makePartial();

        $sut->shouldReceive('getGoodsOrPsv->getId')->andReturn($goodsOrPsv);

        $this->assertEquals($isGoods, $sut->isGoods());
        $this->assertEquals($isPsv, $sut->isPsv());
    }

    public function goodsOrPsvHelperProvider()
    {
        return [
            [Licence::LICENCE_CATEGORY_PSV, false, true],
            [Licence::LICENCE_CATEGORY_GOODS_VEHICLE, true, false],
        ];
    }

    public function testIsGoodsAndIsPsvHelperNull()
    {
        $sut = m::mock(Entity::class)->makePartial();
        $this->assertNull($sut->isGoods());
        $this->assertNull($sut->isPsv());
    }

    public function testIsSpecialRestrictedLicenceTypeNull()
    {
        $sut = m::mock(Entity::class)->makePartial();
        $this->assertNull($sut->isSpecialRestricted());
    }

    /**
     * @dataProvider applicationDateProvider
     */
    public function testGetApplicationDate($createdOn, $receivedDate, $expected)
    {
        $sut = m::mock(Entity::class)->makePartial();

        $sut->shouldReceive('getCreatedOn')->andReturn($createdOn);
        $sut->shouldReceive('getReceivedDate')->andReturn($receivedDate);

        $this->assertEquals($expected, $sut->getApplicationDate());
    }

    public function applicationDateProvider()
    {
        return [
            ['2015-06-19', '2015-06-22', '2015-06-22'],
            ['2015-06-19', null, '2015-06-19'],
            [null, '2015-06-22', '2015-06-22'],
            [null, null, null],
        ];
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
           ->times(6)
           ->andReturnNull();

        $lv->shouldReceive('getSpecifiedDate')
           ->times(6)
           ->andReturn(new DateTime());

        $activeCollection->shouldReceive('toArray')
                         ->once()
                         ->andReturn([$lv, $lv, $lv, $lv, $lv, $lv]);

        $licence = m::mock(Licence::class)->makePartial();
        $licence->setLicenceVehicles($lvCollection);

        $application = $this->instantiate(Entity::class);
        $application->setTotAuthVehicles(10);
        $application->setLicence($licence);

        $this->assertEquals(4, $application->getRemainingSpaces());
    }

    public function testIsRealUpgradeNewApp()
    {
        /** @var Entity $application */
        $application = $this->instantiate(Entity::class);

        $application->setIsVariation(false);

        $this->assertFalse($application->isRealUpgrade());
    }

    public function testIsRealUpgradeIsLicenceUpgrade()
    {
        /** @var RefData $licenceType */
        $licenceType = m::mock(RefData::class)->makePartial();
        $licenceType->setId(Licence::LICENCE_TYPE_STANDARD_NATIONAL);

        /** @var Licence $licence */
        $licence = m::mock(Licence::class);
        $licence->shouldReceive('getLicenceType->getId')
            ->andReturn(Licence::LICENCE_TYPE_RESTRICTED);

        /** @var Entity $application */
        $application = $this->instantiate(Entity::class);
        $application->setIsVariation(true);
        $application->setLicence($licence);
        $application->setLicenceType($licenceType);

        $this->assertTrue($application->isRealUpgrade());
    }

    public function testIsRealUpgrade()
    {
        /** @var RefData $licenceType */
        $licenceType = m::mock(RefData::class)->makePartial();
        $licenceType->setId(Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL);

        /** @var Licence $licence */
        $licence = m::mock(Licence::class);
        $licence->shouldReceive('getLicenceType->getId')
            ->andReturn(Licence::LICENCE_TYPE_STANDARD_NATIONAL);

        /** @var Entity $application */
        $application = $this->instantiate(Entity::class);
        $application->setIsVariation(true);
        $application->setLicence($licence);
        $application->setLicenceType($licenceType);

        $this->assertTrue($application->isRealUpgrade());
    }

    public function testIsRealUpgradeFalse()
    {
        /** @var RefData $licenceType */
        $licenceType = m::mock(RefData::class)->makePartial();
        $licenceType->setId(Licence::LICENCE_TYPE_STANDARD_NATIONAL);

        /** @var Licence $licence */
        $licence = m::mock(Licence::class);
        $licence->shouldReceive('getLicenceType->getId')
            ->andReturn(Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL);

        /** @var Entity $application */
        $application = $this->instantiate(Entity::class);
        $application->setIsVariation(true);
        $application->setLicence($licence);
        $application->setLicenceType($licenceType);

        $this->assertFalse($application->isRealUpgrade());
    }

    public function testGetOcForInspectionRequest()
    {
        $oc1 = m::mock()
            ->shouldReceive('getAction')
            ->once()
            ->andReturn('A')
            ->shouldReceive('getOperatingCentre')
            ->andReturn(
                m::mock()
                ->shouldReceive('getId')
                ->andReturn(1)
                ->twice()
                ->getMock()
            )
            ->getMock();

        $oc2 = m::mock()
            ->shouldReceive('getOperatingCentre')
            ->andReturn(
                m::mock()
                ->shouldReceive('getId')
                ->andReturn(2)
                ->once()
                ->getMock()
            )
            ->shouldReceive('getAction')
            ->andReturn('D')
            ->once()
            ->getMock();

        $mockApplicationOperatingCentres = [$oc1, $oc2];

        $oc3 = m::mock()
            ->shouldReceive('getOperatingCentre')
            ->andReturn(
                m::mock()
                ->shouldReceive('getId')
                ->andReturn(3)
                ->twice()
                ->getMock()
            )
            ->getMock();

        $mockLicenceOperatingCentres = [$oc3];

        $sut = m::mock(Entity::class)->makePartial();
        $sut->shouldReceive('getOperatingCentres')
            ->andReturn($mockApplicationOperatingCentres)
            ->once()
            ->shouldReceive('getLicence')
            ->andReturn(
                m::mock()
                ->shouldReceive('getOperatingCentres')
                ->andReturn($mockLicenceOperatingCentres)
                ->once()
                ->getMock()
            )
            ->once()
            ->getMock();

        $result = $sut->getOcForInspectionRequest();
        $this->assertEquals(count($result), 2);
        $this->assertEquals($result[0]->getId(), 1);
        $this->assertEquals($result[1]->getId(), 3);
    }

    public function testGetVariationCompletionNotVariation()
    {
        $this->entity->setIsVariation(false);
        $this->assertNull($this->entity->getVariationCompletion());
    }

    public function testGetVariationCompletion()
    {
        $completion = m::mock(ApplicationCompletion::class)->makePartial();
        $completion
            ->setAddressesStatus(2)
            ->setOperatingCentresStatus(1);

        $this->entity
            ->setApplicationCompletion($completion)
            ->setIsVariation(true);

        $result = $this->entity->getVariationCompletion();

        $this->assertIsArray($result);

        $this->assertEquals(1, $result['operating_centres']);
        $this->assertEquals(2, $result['addresses']);
    }

    /**
     * @dataProvider niFlagProvider
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function testGetFeeTrafficAreaIdWithLicence($niFlag, $unused)
    {
        $trafficArea = m::mock(TrafficArea::class)
            ->makePartial()
            ->setId('Foo');
        $licence = m::mock(Licence::class)
            ->makePartial()
            ->setTrafficArea($trafficArea);

        $this->entity->setLicence($licence);

        $this->entity->setNiFlag($niFlag);

        $this->assertEquals('Foo', $this->entity->getFeeTrafficAreaId());
    }

    /**
     * @dataProvider niFlagProvider
     */
    public function testGetFeeTrafficAreaIdNoLicence($niFlag, $expected)
    {
        $licence = m::mock(Licence::class)->makePartial();

        $this->entity->setLicence($licence);

        $this->entity->setNiFlag($niFlag);

        $this->assertEquals($expected, $this->entity->getFeeTrafficAreaId());
    }

    public function niFlagProvider()
    {
        return [
            ['Y', 'N'],
            ['N', null],
        ];
    }

    public function testGetActiveVehicles()
    {
        /** @var Entity $application */
        $application = m::mock(Entity::class)->makePartial();

        $application->shouldReceive('getLicenceVehicles->matching')
            ->andReturn('foo');

        $this->assertEquals('foo', $application->getActiveVehicles());
    }

    /**
     * @dataProvider dpTestGetSectionsRequiringAttention
     */
    public function testGetSectionsRequiringAttention(Entity $entity, array $statuses, array $expect)
    {
        /** @var ApplicationCompletion | m\MockInterface $ac */
        $ac = m::mock(ApplicationCompletion::class)->makePartial();
        $ac->shouldReceive('serialize')->once()->withNoArgs()->andReturn($statuses);

        $entity->setApplicationCompletion($ac);

        static::assertEquals($expect, $entity->getSectionsRequiringAttention());
    }

    public function dpTestGetSectionsRequiringAttention()
    {
        return [
            [
                'entity' =>  $this->instantiate(Entity::class)
                    ->setStatus(new RefData(Entity::APPLICATION_STATUS_NOT_SUBMITTED))
                    ->setAuthSignature(true),
                'statuses' => [
                    'businessTypeStatus' => Entity::VARIATION_STATUS_REQUIRES_ATTENTION,
                    'businessDetailsStatus' => Entity::VARIATION_STATUS_UNCHANGED,
                    'undertakingsStatus' => Entity::VARIATION_STATUS_REQUIRES_ATTENTION,
                ],
                'expect' => [
                    'business_type',
                    'undertakings',
                ],
            ],
            [
                'entity' =>  $this->instantiate(Entity::class)
                    ->setStatus(new RefData(Entity::APPLICATION_STATUS_UNDER_CONSIDERATION))
                    ->setAuthSignature(false),
                'statuses' => [
                    'businessTypeStatus' => Entity::VARIATION_STATUS_UPDATED,
                    'undertakingsStatus' => Entity::VARIATION_STATUS_REQUIRES_ATTENTION,
                ],
                'expect' => [
                    'undertakings',
                ],
            ],
            [
                'entity' =>  $this->instantiate(Entity::class)
                    ->setStatus(new RefData('OTHER THAN APPLICATION_STATUS_NOT_SUBMITTED STATUS'))
                    ->setAuthSignature(true),
                'statuses' => [
                    'undertakingsStatus' => Entity::VARIATION_STATUS_REQUIRES_ATTENTION,
                ],
                'expect' => [],
            ],
        ];
    }

    public function testHasVariationChanges()
    {
        /** @var Entity $application */
        $application = $this->instantiate(Entity::class);

        $statuses = [
            'businessTypeStatus' => Entity::VARIATION_STATUS_REQUIRES_ATTENTION,
            'businessDetailsStatus' => Entity::VARIATION_STATUS_UNCHANGED
        ];

        /** @var ApplicationCompletion $ac */
        $ac = m::mock(ApplicationCompletion::class)->makePartial();
        $ac->shouldReceive('serialize')
            ->with([])
            ->andReturn($statuses);

        $application->setApplicationCompletion($ac);

        $this->assertTrue($application->hasVariationChanges());
    }

    public function testHasVariationChangesFalse()
    {
        /** @var Entity $application */
        $application = $this->instantiate(Entity::class);

        $statuses = [
            'businessTypeStatus' => Entity::VARIATION_STATUS_UNCHANGED,
            'businessDetailsStatus' => Entity::VARIATION_STATUS_UNCHANGED
        ];

        /** @var ApplicationCompletion $ac */
        $ac = m::mock(ApplicationCompletion::class)->makePartial();
        $ac->shouldReceive('serialize')
            ->with([])
            ->andReturn($statuses);

        $application->setApplicationCompletion($ac);

        $this->assertFalse($application->hasVariationChanges());
    }

    /**
     * @test
     */
    public function copyInformationFromLicenceIsCallable()
    {
        // Setup
        $this->setUpSut();

        // Assert
        $this->assertIsCallable([$this->sut, 'copyInformationFromLicence']);
    }

    /**
     * @depends copyInformationFromLicenceIsCallable
     */
    public function testCopyInformationFromLicence()
    {
        /** @var Licence $licence */
        $licence = m::mock(Licence::class)->makePartial();

        /** @var RefData $licenceType */
        $licenceType = m::mock(RefData::class);
        /** @var RefData $goodsOrPsv */
        $goodsOrPsv = m::mock(RefData::class);
        /** @var RefData $vehicleType */
        $vehicleType = m::mock(RefData::class);
        /** @var TrafficArea $trafficArea */
        $trafficArea = m::mock(TrafficArea::class)
            ->shouldReceive('getIsNi')
            ->andReturn(true)
            ->getMock();

        $licence->setLicenceType($licenceType);
        $licence->setGoodsOrPsv($goodsOrPsv);
        $licence->setVehicleType($vehicleType);
        $licence->setLgvDeclarationConfirmation(true);
        $licence->setTotAuthTrailers(5);
        $licence->updateTotAuthHgvVehicles(6);
        $licence->setTrafficArea($trafficArea);

        $this->entity->copyInformationFromLicence($licence);

        $this->assertSame($licenceType, $this->entity->getLicenceType());
        $this->assertSame($goodsOrPsv, $this->entity->getGoodsOrPsv());
        $this->assertSame($vehicleType, $this->entity->getVehicleType());
        $this->assertTrue($this->entity->getLgvDeclarationConfirmation());
        $this->assertEquals(5, $this->entity->getTotAuthTrailers());
        $this->assertEquals(6, $this->entity->getTotAuthVehicles());
        $this->assertEquals('Y', $this->entity->getNiFlag());
    }

    /**
     * @test
     * @depends copyInformationFromLicenceIsCallable
     */
    public function copyInformationFromLicenceSetsTotAuthHgvVehicles()
    {
        // Setup
        $this->setUpSut();
        $licence = $this->licence();
        $licence->setTotAuthHgvVehicles($expectedNumber = static::A_NUMBER_OF_VEHICLES);

        // Execute
        $this->sut->copyInformationFromLicence($licence);

        // Assert
        $this->assertSame($expectedNumber, $this->sut->getTotAuthHgvVehicles());
    }

    /**
     * @test
     * @depends copyInformationFromLicenceIsCallable
     */
    public function copyInformationFromLicenceSetsTotAuthLgvVehicles()
    {
        // Setup
        $this->setUpSut();
        $licence = $this->licence();
        $licence->setTotAuthLgvVehicles($expectedNumber = static::A_NUMBER_OF_VEHICLES);

        // Execute
        $this->sut->copyInformationFromLicence($licence);

        // Assert
        $this->assertSame($expectedNumber, $this->sut->getTotAuthLgvVehicles());
    }

    public function testUseDeltasInPeopleSectionSole()
    {
        $type = new RefData();
        $type->setId('org_t_st');
        $organisation = new \Dvsa\Olcs\Api\Entity\Organisation\Organisation();
        $organisation->setType($type);
        $licence = new Licence($organisation, new RefData());
        $application = new Entity($licence, new RefData(), 1);

        $this->assertFalse($application->useDeltasInPeopleSection());
    }

    public function testUseDeltasInPeopleSectionPartnership()
    {
        $type = new RefData();
        $type->setId('org_t_p');
        $organisation = new \Dvsa\Olcs\Api\Entity\Organisation\Organisation();
        $organisation->setType($type);
        $licence = new Licence($organisation, new RefData());
        $application = new Entity($licence, new RefData(), 1);

        $this->assertFalse($application->useDeltasInPeopleSection());
    }

    public function testUseDeltasInPeopleSectionVariationLlp()
    {
        $type = new RefData();
        $type->setId('org_t_llp');
        $organisation = new \Dvsa\Olcs\Api\Entity\Organisation\Organisation();
        $organisation->setType($type);
        $licence = new Licence($organisation, new RefData());
        $application = new Entity($licence, new RefData(), 1);

        $this->assertTrue($application->useDeltasInPeopleSection());
    }

    public function testUseDeltasInPeopleSectionApplicationRc()
    {
        $type = new RefData();
        $type->setId('org_t_rc');
        $organisation = new \Dvsa\Olcs\Api\Entity\Organisation\Organisation();
        $organisation->setType($type);
        $licence = new Licence($organisation, new RefData());
        $application = new Entity($licence, new RefData(), 0);

        $this->assertFalse($application->useDeltasInPeopleSection());
    }

    public function testGetCurrentInterimStatusNull()
    {
        /** @var Entity $application */
        $application = $this->instantiate(Entity::class);

        $this->assertNull($application->getCurrentInterimStatus());
    }

    public function testGetCurrentInterimStatus()
    {
        /** @var RefData $status */
        $status = m::mock(RefData::class)->makePartial();
        $status->setId(123);

        /** @var Entity $application */
        $application = $this->instantiate(Entity::class);
        $application->setInterimStatus($status);

        $this->assertEquals(123, $application->getCurrentInterimStatus());
    }

    /**
     * A new goods application with all dates provided
     * @see https://jira.i-env.net/browse/OLCS-8520
     */
    public function testGetOutOfRepresentationDateScenario1()
    {
        $this->entity->setVehicleType(new RefData(RefData::APP_VEHICLE_TYPE_HGV));

        $aoc1 = new ApplicationOperatingCentre($this->entity, new OperatingCentre());
        $aoc1->setAction('A')
            ->setAdPlaced(ApplicationOperatingCentre::AD_UPLOAD_NOW)
            ->setAdPlacedDate('2015-04-21')
            ->setNoOfVehiclesRequired(4);
        $this->entity->addOperatingCentres($aoc1);

        $aoc2 = new ApplicationOperatingCentre($this->entity, new OperatingCentre());
        $aoc2->setAction('A')
            ->setAdPlaced(ApplicationOperatingCentre::AD_UPLOAD_NOW)
            ->setAdPlacedDate('2015-04-23')
            ->setNoOfVehiclesRequired(4);
        $this->entity->addOperatingCentres($aoc2);

        $oorDate = $this->entity->getOutOfRepresentationDate();

        $this->assertEquals(new DateTime('2015-05-14'), $oorDate);
    }

    /**
     * A new goods application with a date missing
     * @see https://jira.i-env.net/browse/OLCS-8520
     */
    public function testGetOutOfRepresentationDateScenario2()
    {
        $this->entity->setVehicleType(new RefData(RefData::APP_VEHICLE_TYPE_HGV));

        $aoc1 = new ApplicationOperatingCentre($this->entity, new OperatingCentre());
        $aoc1->setAction('A')
            ->setAdPlaced('Y')
            ->setAdPlacedDate('2015-04-21')
            ->setNoOfVehiclesRequired(4);
        $this->entity->addOperatingCentres($aoc1);

        $aoc2 = new ApplicationOperatingCentre($this->entity, new OperatingCentre());
        $aoc2->setAction('A')
            ->setAdPlaced('N')
            ->setAdPlacedDate('2015-04-21')
            ->setNoOfVehiclesRequired(4);
        $this->entity->addOperatingCentres($aoc2);

        $oorDate = $this->entity->getOutOfRepresentationDate();

        $this->assertEquals('Unknown', $oorDate);
    }

    /**
     * A new goods application with two schedule 4 operating centres
     * @see https://jira.i-env.net/browse/OLCS-8520
     */
    public function testGetOutOfRepresentationDateScenario3()
    {
        $this->entity->setVehicleType(new RefData(RefData::APP_VEHICLE_TYPE_HGV));

        /* @var $licence Licence */
        $licence = $this->instantiate(Licence::class);
        $oc1 = new OperatingCentre();
        $oc2 = new OperatingCentre();

        $loc1 = new \Dvsa\Olcs\Api\Entity\Licence\LicenceOperatingCentre($licence, $oc1);
        $loc1->setNoOfVehiclesRequired(4);
        $loc2 = new \Dvsa\Olcs\Api\Entity\Licence\LicenceOperatingCentre($licence, $oc2);
        $loc2->setNoOfVehiclesRequired(4);

        $licence->addOperatingCentres($loc1)
            ->addOperatingCentres($loc2);

        $s4 = new \Dvsa\Olcs\Api\Entity\Application\S4($this->entity, $licence);

        $aoc1 = new ApplicationOperatingCentre($this->entity, $oc1);
        $aoc1->setAction('A')
            ->setS4($s4)
            ->setNoOfVehiclesRequired(4);
        $this->entity->addOperatingCentres($aoc1);

        $aoc2 = new ApplicationOperatingCentre($this->entity, $oc2);
        $aoc2->setAction('A')
            ->setS4($s4)
            ->setNoOfVehiclesRequired(4);
        $this->entity->addOperatingCentres($aoc2);

        $oorDate = $this->entity->getOutOfRepresentationDate();

        $this->assertEquals('Not applicable', $oorDate);
    }

    /**
     * A new goods application with two schedule 4 operating centres
     * @see https://jira.i-env.net/browse/OLCS-8520
     */
    public function testGetOutOfRepresentationDateScenario3WithDates()
    {
        $this->entity->setVehicleType(new RefData(RefData::APP_VEHICLE_TYPE_HGV));

        /* @var $licence Licence */
        $licence = $this->instantiate(Licence::class);
        $oc1 = new OperatingCentre();
        $oc2 = new OperatingCentre();

        $loc1 = new \Dvsa\Olcs\Api\Entity\Licence\LicenceOperatingCentre($licence, $oc1);
        $loc1->setNoOfVehiclesRequired(4);
        $loc2 = new \Dvsa\Olcs\Api\Entity\Licence\LicenceOperatingCentre($licence, $oc2);
        $loc2->setNoOfVehiclesRequired(4);

        $licence->addOperatingCentres($loc1)
            ->addOperatingCentres($loc2);

        $s4 = new \Dvsa\Olcs\Api\Entity\Application\S4($this->entity, $licence);

        $aoc1 = new ApplicationOperatingCentre($this->entity, $oc1);
        $aoc1->setAction('A')
            ->setS4($s4)
            ->setAdPlaced('Y')
            ->setAdPlacedDate('2015-04-20')
            ->setNoOfVehiclesRequired(4);
        $this->entity->addOperatingCentres($aoc1);

        $aoc2 = new ApplicationOperatingCentre($this->entity, $oc2);
        $aoc2->setAction('A')
            ->setS4($s4)
            ->setAdPlaced('Y')
            ->setAdPlacedDate('2015-04-20')
            ->setNoOfVehiclesRequired(4);
        $this->entity->addOperatingCentres($aoc2);

        $oorDate = $this->entity->getOutOfRepresentationDate();

        $this->assertEquals('Not applicable', $oorDate);
    }

    /**
     * A new goods application with two schedule 4 operating centres
     * @see https://jira.i-env.net/browse/OLCS-8520
     */
    public function testGetOutOfRepresentationDateScenario3WithDatesAndIncrease()
    {
        $this->entity->setVehicleType(new RefData(RefData::APP_VEHICLE_TYPE_HGV));

        /* @var $licence Licence */
        $licence = $this->instantiate(Licence::class);
        $oc1 = new OperatingCentre();
        $oc2 = new OperatingCentre();

        $loc1 = new \Dvsa\Olcs\Api\Entity\Licence\LicenceOperatingCentre($licence, $oc1);
        $loc1->setNoOfVehiclesRequired(4);
        $loc2 = new \Dvsa\Olcs\Api\Entity\Licence\LicenceOperatingCentre($licence, $oc2);
        $loc2->setNoOfVehiclesRequired(4);

        $licence->addOperatingCentres($loc1)
            ->addOperatingCentres($loc2);

        $s4 = new \Dvsa\Olcs\Api\Entity\Application\S4($this->entity, $licence);

        $aoc1 = new ApplicationOperatingCentre($this->entity, $oc1);
        $aoc1->setAction('A')
            ->setS4($s4)
            ->setAdPlaced(ApplicationOperatingCentre::AD_UPLOAD_NOW)
            ->setAdPlacedDate('2015-04-20')
            ->setNoOfVehiclesRequired(5);
        $this->entity->addOperatingCentres($aoc1);

        $aoc2 = new ApplicationOperatingCentre($this->entity, $oc2);
        $aoc2->setAction('A')
            ->setS4($s4)
            ->setAdPlaced(ApplicationOperatingCentre::AD_UPLOAD_NOW)
            ->setAdPlacedDate('2015-04-22')
            ->setNoOfVehiclesRequired(4);
        $this->entity->addOperatingCentres($aoc2);

        $oorDate = $this->entity->getOutOfRepresentationDate();

        $this->assertEquals(new DateTime('2015-05-11'), $oorDate);
    }

    /**
     * A new goods application with one S4 operating centre and two other operating centres
     * @see https://jira.i-env.net/browse/OLCS-8520
     */
    public function testGetOutOfRepresentationDateScenario4()
    {
        $this->entity->setVehicleType(new RefData(RefData::APP_VEHICLE_TYPE_HGV));

        /* @var $licence Licence */
        $licence = $this->instantiate(Licence::class);
        $oc1 = new OperatingCentre();
        $oc2 = new OperatingCentre();

        $loc1 = new \Dvsa\Olcs\Api\Entity\Licence\LicenceOperatingCentre($licence, $oc1);
        $loc1->setNoOfVehiclesRequired(4);
        $loc2 = new \Dvsa\Olcs\Api\Entity\Licence\LicenceOperatingCentre($licence, $oc2);
        $loc2->setNoOfVehiclesRequired(4);

        $licence->addOperatingCentres($loc1)
            ->addOperatingCentres($loc2);

        $s4 = new \Dvsa\Olcs\Api\Entity\Application\S4($this->entity, $licence);

        $aoc1 = new ApplicationOperatingCentre($this->entity, $oc1);
        $aoc1->setAction('A')
            ->setS4($s4)
            ->setNoOfVehiclesRequired(4);
        $this->entity->addOperatingCentres($aoc1);

        $aoc2 = new ApplicationOperatingCentre($this->entity, $oc2);
        $aoc2->setAction('A')
            ->setAdPlaced(ApplicationOperatingCentre::AD_UPLOAD_NOW)
            ->setAdPlacedDate('2015-04-21')
            ->setNoOfVehiclesRequired(4);
        $this->entity->addOperatingCentres($aoc2);

        $aoc3 = new ApplicationOperatingCentre($this->entity, $oc2);
        $aoc3->setAction('A')
            ->setAdPlaced(ApplicationOperatingCentre::AD_UPLOAD_NOW)
            ->setAdPlacedDate('2015-04-20')
            ->setNoOfVehiclesRequired(4);
        $this->entity->addOperatingCentres($aoc3);

        $oorDate = $this->entity->getOutOfRepresentationDate();

        $this->assertEquals(new DateTime('2015-05-12'), $oorDate);
    }

    /**
     * A new goods application with one S4 operating centre and two other operating centres; one with a missing date
     * @see https://jira.i-env.net/browse/OLCS-8520
     */
    public function testGetOutOfRepresentationDateScenario5()
    {
        $this->entity->setVehicleType(new RefData(RefData::APP_VEHICLE_TYPE_HGV));

        /* @var $licence Licence */
        $licence = $this->instantiate(Licence::class);
        $oc1 = new OperatingCentre();
        $oc2 = new OperatingCentre();

        $loc1 = new \Dvsa\Olcs\Api\Entity\Licence\LicenceOperatingCentre($licence, $oc1);
        $loc1->setNoOfVehiclesRequired(4);
        $loc2 = new \Dvsa\Olcs\Api\Entity\Licence\LicenceOperatingCentre($licence, $oc2);
        $loc2->setNoOfVehiclesRequired(4);

        $licence->addOperatingCentres($loc1)
            ->addOperatingCentres($loc2);

        $s4 = new \Dvsa\Olcs\Api\Entity\Application\S4($this->entity, $licence);

        $aoc1 = new ApplicationOperatingCentre($this->entity, $oc1);
        $aoc1->setAction('A')
            ->setS4($s4)
            ->setNoOfVehiclesRequired(4);
        $this->entity->addOperatingCentres($aoc1);

        $aoc2 = new ApplicationOperatingCentre($this->entity, $oc2);
        $aoc2->setAction('A')
            ->setAdPlacedDate('2015-04-21')
            ->setNoOfVehiclesRequired(4);
        $this->entity->addOperatingCentres($aoc2);

        $aoc3 = new ApplicationOperatingCentre($this->entity, $oc2);
        $aoc3->setAction('A')
            ->setNoOfVehiclesRequired(4);
        $this->entity->addOperatingCentres($aoc3);

        $oorDate = $this->entity->getOutOfRepresentationDate();

        $this->assertEquals('Unknown', $oorDate);
    }

    /**
     * A goods variation application with one S4 operating centre and one other operating centre with no increase
     * @see https://jira.i-env.net/browse/OLCS-8520
     */
    public function testGetOutOfRepresentationDateScenario6()
    {
        $this->entity->setVehicleType(new RefData(RefData::APP_VEHICLE_TYPE_HGV));

        /* @var $s4DonorLicence Licence */
        $s4DonorLicence = $this->instantiate(Licence::class);
        $oc1 = new OperatingCentre();
        $oc2 = new OperatingCentre();
        $loc1 = new \Dvsa\Olcs\Api\Entity\Licence\LicenceOperatingCentre($s4DonorLicence, $oc1);
        $loc1->setNoOfVehiclesRequired(4);
        $loc2 = new \Dvsa\Olcs\Api\Entity\Licence\LicenceOperatingCentre($s4DonorLicence, $oc2);
        $loc2->setNoOfVehiclesRequired(4);

        $s4DonorLicence->addOperatingCentres($loc1)
            ->addOperatingCentres($loc2);

        $s4 = new \Dvsa\Olcs\Api\Entity\Application\S4($this->entity, $s4DonorLicence);

        $appLicence = $this->instantiate(Licence::class);
        $oc11 = new OperatingCentre();
        $oc12 = new OperatingCentre();
        $loc11 = new \Dvsa\Olcs\Api\Entity\Licence\LicenceOperatingCentre($s4DonorLicence, $oc11);
        $loc11->setNoOfVehiclesRequired(4);
        $loc12 = new \Dvsa\Olcs\Api\Entity\Licence\LicenceOperatingCentre($s4DonorLicence, $oc12);
        $loc12->setNoOfVehiclesRequired(4);

        $appLicence->addOperatingCentres($loc11)
            ->addOperatingCentres($loc12);

        $this->entity->setLicence($appLicence);

        $aoc1 = new ApplicationOperatingCentre($this->entity, $oc1);
        $aoc1->setAction('A')
            ->setS4($s4)
            ->setNoOfVehiclesRequired(4);
        $this->entity->addOperatingCentres($aoc1);

        $aoc2 = new ApplicationOperatingCentre($this->entity, $oc12);
        $aoc2->setAction('U')
            ->setNoOfVehiclesRequired(2);
        $this->entity->addOperatingCentres($aoc2);

        $oorDate = $this->entity->getOutOfRepresentationDate();

        $this->assertEquals('Not applicable', $oorDate);
    }

    /**
     * A goods variation application with one S4 operating centre and two other operating centres; one
     * with vehicle increase
     * @see https://jira.i-env.net/browse/OLCS-8520
     */
    public function testGetOutOfRepresentationDateScenario7()
    {
        $this->entity->setVehicleType(new RefData(RefData::APP_VEHICLE_TYPE_HGV));

        /* @var $s4DonorLicence Licence */
        $s4DonorLicence = $this->instantiate(Licence::class);
        $oc1 = new OperatingCentre();
        $oc2 = new OperatingCentre();
        $loc1 = new \Dvsa\Olcs\Api\Entity\Licence\LicenceOperatingCentre($s4DonorLicence, $oc1);
        $loc1->setNoOfVehiclesRequired(4);
        $loc2 = new \Dvsa\Olcs\Api\Entity\Licence\LicenceOperatingCentre($s4DonorLicence, $oc2);
        $loc2->setNoOfVehiclesRequired(4);

        $s4DonorLicence->addOperatingCentres($loc1)
            ->addOperatingCentres($loc2);

        $s4 = new \Dvsa\Olcs\Api\Entity\Application\S4($this->entity, $s4DonorLicence);

        $appLicence = $this->instantiate(Licence::class);
        $oc11 = new OperatingCentre();
        $oc12 = new OperatingCentre();
        $loc11 = new \Dvsa\Olcs\Api\Entity\Licence\LicenceOperatingCentre($s4DonorLicence, $oc11);
        $loc11->setNoOfVehiclesRequired(4);
        $loc12 = new \Dvsa\Olcs\Api\Entity\Licence\LicenceOperatingCentre($s4DonorLicence, $oc12);
        $loc12->setNoOfVehiclesRequired(3);

        $appLicence->addOperatingCentres($loc11)
            ->addOperatingCentres($loc12);

        $this->entity->setLicence($appLicence);

        $aoc1 = new ApplicationOperatingCentre($this->entity, $oc1);
        $aoc1->setAction('A')
            ->setS4($s4)
            ->setNoOfVehiclesRequired(4);
        $this->entity->addOperatingCentres($aoc1);

        $aoc2 = new ApplicationOperatingCentre($this->entity, $oc11);
        $aoc2->setAction('U')
            ->setAdPlaced(ApplicationOperatingCentre::AD_UPLOAD_NOW)
            ->setAdPlacedDate('2015-04-21')
            ->setNoOfVehiclesRequired(6);
        $this->entity->addOperatingCentres($aoc2);

        $aoc3 = new ApplicationOperatingCentre($this->entity, $oc12);
        $aoc3->setAction('U')
            ->setNoOfVehiclesRequired(1);
        $this->entity->addOperatingCentres($aoc3);

        $oorDate = $this->entity->getOutOfRepresentationDate();

        $this->assertEquals(new DateTime('2015-05-12'), $oorDate);
    }

    /**
     * A goods variation application with one S4 operating centre and two other operating centres with vehicle
     * increases but with a missing advertisement date
     * @see https://jira.i-env.net/browse/OLCS-8520
     */
    public function testGetOutOfRepresentationDateScenario8()
    {
        $this->entity->setVehicleType(new RefData(RefData::APP_VEHICLE_TYPE_HGV));

        /* @var $s4DonorLicence Licence */
        $s4DonorLicence = $this->instantiate(Licence::class);
        $oc1 = new OperatingCentre();
        $oc2 = new OperatingCentre();
        $loc1 = new \Dvsa\Olcs\Api\Entity\Licence\LicenceOperatingCentre($s4DonorLicence, $oc1);
        $loc1->setNoOfVehiclesRequired(4);
        $loc2 = new \Dvsa\Olcs\Api\Entity\Licence\LicenceOperatingCentre($s4DonorLicence, $oc2);
        $loc2->setNoOfVehiclesRequired(4);

        $s4DonorLicence->addOperatingCentres($loc1)
            ->addOperatingCentres($loc2);

        $s4 = new \Dvsa\Olcs\Api\Entity\Application\S4($this->entity, $s4DonorLicence);

        $appLicence = $this->instantiate(Licence::class);
        $oc11 = new OperatingCentre();
        $oc12 = new OperatingCentre();
        $loc11 = new \Dvsa\Olcs\Api\Entity\Licence\LicenceOperatingCentre($s4DonorLicence, $oc11);
        $loc11->setNoOfVehiclesRequired(4);
        $loc12 = new \Dvsa\Olcs\Api\Entity\Licence\LicenceOperatingCentre($s4DonorLicence, $oc12);
        $loc12->setNoOfVehiclesRequired(1);

        $appLicence->addOperatingCentres($loc11)
            ->addOperatingCentres($loc12);

        $this->entity->setLicence($appLicence);

        $aoc1 = new ApplicationOperatingCentre($this->entity, $oc1);
        $aoc1->setAction('A')
            ->setS4($s4)
            ->setNoOfVehiclesRequired(4);
        $this->entity->addOperatingCentres($aoc1);

        $aoc2 = new ApplicationOperatingCentre($this->entity, $oc11);
        $aoc2->setAction('U')
            ->setNoOfVehiclesRequired(6);
        $this->entity->addOperatingCentres($aoc2);

        $aoc3 = new ApplicationOperatingCentre($this->entity, $oc12);
        $aoc3->setAction('U')
            ->setAdPlacedDate('2015-04-20')
            ->setNoOfVehiclesRequired(3);
        $this->entity->addOperatingCentres($aoc3);

        $oorDate = $this->entity->getOutOfRepresentationDate();

        $this->assertEquals('Unknown', $oorDate);
    }

    /**
     * A goods variation application with one S4 operating centre and two other operating centres with
     * vehicle increases including the S4 but with missing advertisement date
     * @see https://jira.i-env.net/browse/OLCS-8520
     */
    public function testGetOutOfRepresentationDateScenario9()
    {
        $this->entity->setVehicleType(new RefData(RefData::APP_VEHICLE_TYPE_HGV));

        /* @var $s4DonorLicence Licence */
        $s4DonorLicence = $this->instantiate(Licence::class);
        $oc1 = new OperatingCentre();
        $loc1 = new \Dvsa\Olcs\Api\Entity\Licence\LicenceOperatingCentre($s4DonorLicence, $oc1);
        $loc1->setNoOfVehiclesRequired(2);

        $s4DonorLicence->addOperatingCentres($loc1);

        $s4 = new \Dvsa\Olcs\Api\Entity\Application\S4($this->entity, $s4DonorLicence);

        $appLicence = $this->instantiate(Licence::class);
        $oc11 = new OperatingCentre();
        $oc12 = new OperatingCentre();
        $loc11 = new \Dvsa\Olcs\Api\Entity\Licence\LicenceOperatingCentre($s4DonorLicence, $oc11);
        $loc11->setNoOfVehiclesRequired(4);
        $loc12 = new \Dvsa\Olcs\Api\Entity\Licence\LicenceOperatingCentre($s4DonorLicence, $oc12);
        $loc12->setNoOfVehiclesRequired(1);

        $appLicence->addOperatingCentres($loc11)
            ->addOperatingCentres($loc12);

        $this->entity->setLicence($appLicence);

        $aoc1 = new ApplicationOperatingCentre($this->entity, $oc1);
        $aoc1->setAction('A')
            ->setS4($s4)
            ->setNoOfVehiclesRequired(6);
        $this->entity->addOperatingCentres($aoc1);

        $aoc2 = new ApplicationOperatingCentre($this->entity, $oc11);
        $aoc2->setAction('U')
            ->setAdPlacedDate('2015-04-21')
            ->setNoOfVehiclesRequired(6);
        $this->entity->addOperatingCentres($aoc2);

        $aoc3 = new ApplicationOperatingCentre($this->entity, $oc12);
        $aoc3->setAction('U')
            ->setAdPlacedDate('2015-04-20')
            ->setNoOfVehiclesRequired(3);
        $this->entity->addOperatingCentres($aoc3);

        $oorDate = $this->entity->getOutOfRepresentationDate();

        $this->assertEquals('Unknown', $oorDate);
    }

    /**
     * A goods variation application with one S4 operating centre and two other operating centres with advertising dates
     * @see https://jira.i-env.net/browse/OLCS-8520
     */
    public function testGetOutOfRepresentationDateScenario10()
    {
        $this->entity->setVehicleType(new RefData(RefData::APP_VEHICLE_TYPE_HGV));

        /* @var $s4DonorLicence Licence */
        $s4DonorLicence = $this->instantiate(Licence::class);
        $oc1 = new OperatingCentre();
        $loc1 = new \Dvsa\Olcs\Api\Entity\Licence\LicenceOperatingCentre($s4DonorLicence, $oc1);
        $loc1->setNoOfVehiclesRequired(2);

        $s4DonorLicence->addOperatingCentres($loc1);

        $s4 = new \Dvsa\Olcs\Api\Entity\Application\S4($this->entity, $s4DonorLicence);

        $appLicence = $this->instantiate(Licence::class);
        $oc11 = new OperatingCentre();
        $oc12 = new OperatingCentre();
        $loc11 = new \Dvsa\Olcs\Api\Entity\Licence\LicenceOperatingCentre($s4DonorLicence, $oc11);
        $loc11->setNoOfVehiclesRequired(4);
        $loc12 = new \Dvsa\Olcs\Api\Entity\Licence\LicenceOperatingCentre($s4DonorLicence, $oc12);
        $loc12->setNoOfVehiclesRequired(1);

        $appLicence->addOperatingCentres($loc11)
            ->addOperatingCentres($loc12);

        $this->entity->setLicence($appLicence);

        $aoc1 = new ApplicationOperatingCentre($this->entity, $oc1);
        $aoc1->setAction('A')
            ->setS4($s4)
            ->setAdPlaced(ApplicationOperatingCentre::AD_UPLOAD_NOW)
            ->setAdPlacedDate('2015-04-21')
            ->setNoOfVehiclesRequired(6);
        $this->entity->addOperatingCentres($aoc1);

        $aoc2 = new ApplicationOperatingCentre($this->entity, $oc11);
        $aoc2->setAction('U')
            ->setAdPlaced(ApplicationOperatingCentre::AD_UPLOAD_NOW)
            ->setAdPlacedDate('2015-04-19')
            ->setNoOfVehiclesRequired(6);
        $this->entity->addOperatingCentres($aoc2);

        $aoc3 = new ApplicationOperatingCentre($this->entity, $oc12);
        $aoc3->setAction('U')
            ->setAdPlaced(ApplicationOperatingCentre::AD_UPLOAD_NOW)
            ->setAdPlacedDate('2015-04-20')
            ->setNoOfVehiclesRequired(3);
        $this->entity->addOperatingCentres($aoc3);

        $oorDate = $this->entity->getOutOfRepresentationDate();

        $this->assertEquals(new DateTime('2015-05-12'), $oorDate);
    }

    public function testGetOutOfRepresentationDatePsv()
    {
        $this->entity->setVehicleType(new RefData(RefData::APP_VEHICLE_TYPE_PSV));

        $oorDate = $this->entity->getOutOfRepresentationDate();

        $this->assertEquals(Entity::NOT_APPLICABLE, $oorDate);
    }

    public function testGetOutOfRepresentationDateLgOnly()
    {
        $this->entity->setVehicleType(new RefData(RefData::APP_VEHICLE_TYPE_LGV));

        $oorDate = $this->entity->getOutOfRepresentationDate();

        $this->assertEquals(Entity::NOT_APPLICABLE, $oorDate);
    }

    public function testGetOutOfRepresentationDateApplicationNoOcs()
    {
        $this->entity->setVehicleType(new RefData(RefData::APP_VEHICLE_TYPE_HGV));

        $aoc = m::mock(ApplicationOperatingCentre::class)->makePartial();
        $aoc->setAction('D');
        $this->entity->addOperatingCentres($aoc);

        $oorDate = $this->entity->getOutOfRepresentationDate();

        $this->assertEquals('Unknown', $oorDate);
    }

    public function testGetOutOfRepresentationDateVariationNoOcs()
    {
        $this->entity->setVehicleType(new RefData(RefData::APP_VEHICLE_TYPE_HGV));
        $this->entity->setIsVariation(1);

        $aoc = m::mock(ApplicationOperatingCentre::class)->makePartial();
        $aoc->setAction('D');
        $this->entity->addOperatingCentres($aoc);

        $this->assertEquals(Entity::NOT_APPLICABLE, $this->entity->getOutOfRepresentationDate());
    }

    /**
     * https://jira.i-env.net/browse/OLCS-10588 Scenario 1
     */
    public function testGetOutOfOppositionDateOlcs10588Scenario1()
    {
        $this->entity->setIsVariation(false);
        $this->entity->setGoodsOrPsv(new RefData(Licence::LICENCE_CATEGORY_GOODS_VEHICLE));

        $this->assertEquals(Entity::UNKNOWN, $this->entity->getOutOfOppositionDate());
    }

    /**
     * https://jira.i-env.net/browse/OLCS-10588 Scenario 2
     */
    public function testGetOutOfOppositionDateOlcs10588Scenario2()
    {
        $this->entity->setIsVariation(false);
        $this->entity->setGoodsOrPsv(new RefData(Licence::LICENCE_CATEGORY_PSV));

        $this->assertEquals(Entity::UNKNOWN, $this->entity->getOutOfOppositionDate());
    }

    /**
     * https://jira.i-env.net/browse/OLCS-10588 Scenario 3
     */
    public function testGetOutOfOppositionDateOlcs10588Scenario3()
    {
        $this->entity->setIsVariation(false);
        $this->entity->setGoodsOrPsv(new RefData(Licence::LICENCE_CATEGORY_GOODS_VEHICLE));

        $publicationSection1 = new \Dvsa\Olcs\Api\Entity\Publication\PublicationSection();
        $publicationSection1->setId(1);

        /** @var Entities\Publication\Publication $publication1 */
        $publication1 = m::mock(Entities\Publication\Publication::class)->makePartial();
        $publication1->setPubDate('2015-10-05');

        /** @var Entities\Publication\PublicationLink $publicationLink1 */
        $publicationLink1 = new Entities\Publication\PublicationLink();
        $publicationLink1->setPublicationSection($publicationSection1)
            ->setPublication($publication1);

        $this->entity->addPublicationLinks($publicationLink1);

        $this->assertEquals(new \DateTime('2015-10-27'), $this->entity->getOutOfOppositionDate());
    }

    /**
     * https://jira.i-env.net/browse/OLCS-10588 Scenario 4
     */
    public function testGetOutOfOppositionDateOlcs10588Scenario4()
    {
        $this->entity->setIsVariation(false);
        $this->entity->setGoodsOrPsv(new RefData(Licence::LICENCE_CATEGORY_GOODS_VEHICLE));

        $s4 = new S4($this->entity, $this->entity->getLicence());
        $s4->setOutcome(new RefData(S4::STATUS_APPROVED));
        $this->entity->addS4s($s4);

        $publicationSection1 = new \Dvsa\Olcs\Api\Entity\Publication\PublicationSection();
        $publicationSection1->setId(16);

        /** @var Entities\Publication\Publication $publication1 */
        $publication1 = m::mock(Entities\Publication\Publication::class)->makePartial();
        $publication1->setPubDate('2015-10-05');

        $publicationLink1 = new Entities\Publication\PublicationLink();
        $publicationLink1->setPublicationSection($publicationSection1)
            ->setPublication($publication1);

        $this->entity->addPublicationLinks($publicationLink1);

        $this->assertEquals(new \DateTime('2015-10-27'), $this->entity->getOutOfOppositionDate());
    }

    /**
     * https://jira.i-env.net/browse/OLCS-10588 Scenario 5
     */
    public function testGetOutOfOppositionDateOlcs10588Scenario5()
    {
        $this->entity->setIsVariation(true);
        $this->entity->setGoodsOrPsv(new RefData(Licence::LICENCE_CATEGORY_PSV));

        $this->assertEquals(Entity::NOT_APPLICABLE, $this->entity->getOutOfOppositionDate());
    }

    /**
     * https://jira.i-env.net/browse/OLCS-10588 Scenario 6
     */
    public function testGetOutOfOppositionDateOlcs10588Scenario6()
    {
        $this->entity->setIsVariation(true);
        $this->entity->setGoodsOrPsv(new RefData(Licence::LICENCE_CATEGORY_GOODS_VEHICLE));

        $tma = new \Dvsa\Olcs\Api\Entity\Tm\TransportManagerApplication();
        $tma->setAction('A');

        $this->entity->addTransportManagers($tma);

        $this->assertEquals(Entity::NOT_APPLICABLE, $this->entity->getOutOfOppositionDate());
    }

    /**
     * https://jira.i-env.net/browse/OLCS-10588 Scenario 7
     */
    public function testGetOutOfOppositionDateOlcs10588Scenario7()
    {
        $this->entity->setIsVariation(true);
        $this->entity->setGoodsOrPsv(new RefData(Licence::LICENCE_CATEGORY_GOODS_VEHICLE));

        $aoc = new ApplicationOperatingCentre($this->entity, new OperatingCentre());
        $aoc->setAction('A');
        $aoc->setNoOfVehiclesRequired(2);
        $this->entity->addOperatingCentres($aoc);

        $publicationSection1 = new \Dvsa\Olcs\Api\Entity\Publication\PublicationSection();
        $publicationSection1->setId(3);

        /** @var  Entities\Publication\Publication $publication1 */
        $publication1 = m::mock(Entities\Publication\Publication::class)->makePartial();
        $publication1->setPubDate('2015-10-05');

        $publicationLink1 = new Entities\Publication\PublicationLink();
        $publicationLink1->setPublicationSection($publicationSection1)
            ->setPublication($publication1);

        $this->entity->addPublicationLinks($publicationLink1);

        $this->assertEquals(new \DateTime('2015-10-27'), $this->entity->getOutOfOppositionDate());
    }

    /**
     * https://jira.i-env.net/browse/OLCS-10588 Scenario 8
     */
    public function testGetOutOfOppositionDateOlcs10588Scenario8()
    {
        $this->entity->setIsVariation(true);
        $this->entity->setGoodsOrPsv(new RefData(Licence::LICENCE_CATEGORY_GOODS_VEHICLE));

        $oc = new OperatingCentre();
        $oc->setId(610);

        $loc = new \Dvsa\Olcs\Api\Entity\Licence\LicenceOperatingCentre($this->entity->getLicence(), $oc);
        $loc->setNoOfVehiclesRequired(1);
        $this->entity->getLicence()->addOperatingCentres($loc);

        $aoc = new ApplicationOperatingCentre($this->entity, $oc);
        $aoc->setAction('U');
        $aoc->setNoOfVehiclesRequired(2);
        $this->entity->addOperatingCentres($aoc);

        $this->assertEquals(Entity::UNKNOWN, $this->entity->getOutOfOppositionDate());
    }

    /**
     * https://jira.i-env.net/browse/OLCS-10588 Scenario 9
     */
    public function testGetOutOfOppositionDateOlcs10588Scenario9()
    {
        $this->entity->setIsVariation(true);
        $this->entity->setGoodsOrPsv(new RefData(Licence::LICENCE_CATEGORY_GOODS_VEHICLE));

        $s4 = new S4($this->entity, $this->entity->getLicence());
        $s4->setOutcome(new RefData(S4::STATUS_APPROVED));
        $s4->setIsTrueS4('N');
        $this->entity->addS4s($s4);

        $oc = new OperatingCentre();
        $oc->setId(610);

        $aoc = new ApplicationOperatingCentre($this->entity, $oc);
        $aoc->setAction('A');
        $aoc->setS4($s4);
        $aoc->setNoOfVehiclesRequired(2);
        $this->entity->addOperatingCentres($aoc);

        $publicationSection1 = new \Dvsa\Olcs\Api\Entity\Publication\PublicationSection();
        $publicationSection1->setId(17);

        /** @var Entities\Publication\Publication $publication1 */
        $publication1 = m::mock(Entities\Publication\Publication::class)->makePartial();
        $publication1->setPubDate('2015-10-05');

        $publicationLink1 = new Entities\Publication\PublicationLink();
        $publicationLink1->setPublicationSection($publicationSection1)
            ->setPublication($publication1);

        $this->entity->addPublicationLinks($publicationLink1);

        $this->assertEquals(new \DateTime('2015-10-27'), $this->entity->getOutOfOppositionDate());
    }

    /**
     * https://jira.i-env.net/browse/OLCS-10588 Scenario 10
     */
    public function testGetOutOfOppositionDateOlcs10588Scenario10()
    {
        $this->entity->setIsVariation(true);
        $this->entity->setGoodsOrPsv(new RefData(Licence::LICENCE_CATEGORY_GOODS_VEHICLE));

        $s4 = new S4($this->entity, $this->entity->getLicence());
        $s4->setIsTrueS4('Y');
        $s4->setOutcome(new RefData(S4::STATUS_APPROVED));
        $this->entity->addS4s($s4);

        $oc = new OperatingCentre();
        $oc->setId(610);

        $aoc = new ApplicationOperatingCentre($this->entity, $oc);
        $aoc->setAction('A');
        $aoc->setS4($s4);
        $aoc->setNoOfVehiclesRequired(2);
        $this->entity->addOperatingCentres($aoc);

        $this->assertEquals(Entity::NOT_APPLICABLE, $this->entity->getOutOfOppositionDate());
    }

    /**
     * HGV authorisation increased
     */
    public function testGetOutOfOppositionDateWhenHgvAuthorisationIncreased()
    {
        $this->entity->setIsVariation(true);
        $this->entity->setGoodsOrPsv(new RefData(Licence::LICENCE_CATEGORY_GOODS_VEHICLE));
        $this->entity->updateTotAuthHgvVehicles(10);

        $publicationSection1 = new \Dvsa\Olcs\Api\Entity\Publication\PublicationSection();
        $publicationSection1->setId(17);

        /** @var Entities\Publication\Publication $publication1 */
        $publication1 = m::mock(Entities\Publication\Publication::class)->makePartial();
        $publication1->setPubDate('2015-10-05');

        $publicationLink1 = new Entities\Publication\PublicationLink();
        $publicationLink1->setPublicationSection($publicationSection1)
            ->setPublication($publication1);

        $this->entity->addPublicationLinks($publicationLink1);

        $this->assertEquals(new \DateTime('2015-10-27'), $this->entity->getOutOfOppositionDate());
    }

    /**
     * LGV authorisation increased
     */
    public function testGetOutOfOppositionDateWhenLgvAuthorisationIncreased()
    {
        $this->entity->setIsVariation(true);
        $this->entity->setGoodsOrPsv(new RefData(Licence::LICENCE_CATEGORY_GOODS_VEHICLE));
        $this->entity->updateTotAuthLgvVehicles(10);

        $publicationSection1 = new \Dvsa\Olcs\Api\Entity\Publication\PublicationSection();
        $publicationSection1->setId(17);

        /** @var Entities\Publication\Publication $publication1 */
        $publication1 = m::mock(Entities\Publication\Publication::class)->makePartial();
        $publication1->setPubDate('2015-10-05');

        $publicationLink1 = new Entities\Publication\PublicationLink();
        $publicationLink1->setPublicationSection($publicationSection1)
            ->setPublication($publication1);

        $this->entity->addPublicationLinks($publicationLink1);

        $this->assertEquals(new \DateTime('2015-10-27'), $this->entity->getOutOfOppositionDate());
    }

    /**
     * Trailers authorisation increased
     */
    public function testGetOutOfOppositionDateWhenTrailersAuthorisationIncreased()
    {
        $this->entity->setIsVariation(true);
        $this->entity->setGoodsOrPsv(new RefData(Licence::LICENCE_CATEGORY_GOODS_VEHICLE));
        $this->entity->setTotAuthTrailers(10);

        $publicationSection1 = new \Dvsa\Olcs\Api\Entity\Publication\PublicationSection();
        $publicationSection1->setId(17);

        /** @var Entities\Publication\Publication $publication1 */
        $publication1 = m::mock(Entities\Publication\Publication::class)->makePartial();
        $publication1->setPubDate('2015-10-05');

        $publicationLink1 = new Entities\Publication\PublicationLink();
        $publicationLink1->setPublicationSection($publicationSection1)
            ->setPublication($publication1);

        $this->entity->addPublicationLinks($publicationLink1);

        $this->assertEquals(new \DateTime('2015-10-27'), $this->entity->getOutOfOppositionDate());
    }

    /**
     * It test NOT REACHABLE condition, added for coverage
     */
    public function testGetOutOfOppositionDateGoodsVarNotAppl()
    {
        /** @var Entity $sut */
        $sut = m::mock(Entity::class)->makePartial()
            ->shouldAllowMockingProtectedMethods()
            ->shouldReceive('isGoods')->once()->andReturn(true)
            ->shouldReceive('isVariation')->once()->andReturn(true)
            ->shouldReceive('isPublishable')->once()->andReturn(true)
            ->shouldReceive('hasApprovedTrueS4')->once()->andReturn(false)
            ->shouldReceive('hasIncreaseInOperatingCentre')->once()->andReturn(false)
            ->shouldReceive('isRealUpgrade')->once()->andReturn(false)
            ->shouldReceive('hasHgvAuthorisationIncreased')->once()->andReturn(false)
            ->shouldReceive('hasLgvAuthorisationIncreased')->once()->andReturn(false)
            ->shouldReceive('hasAuthTrailersIncrease')->once()->andReturn(false)
            ->getMock();
        $sut->shouldReceive('getOperatingCentresAdded->count')->andReturn(0);

        static::assertEquals(Entity::NOT_APPLICABLE, $sut->getOutOfOppositionDate());
    }

    /**
     * @dataProvider providerDatesAsString
     */
    public function testGetOutOfOppositionDateAsString($input, $expected)
    {
        /** @var Entity $application */
        $application = m::mock(Entity::class)->makePartial();

        $application->shouldReceive('getOutOfOppositionDate')
            ->andReturn($input);

        $oooDate = $application->getOutOfOppositionDateAsString();
        $this->assertEquals($expected, $oooDate);
    }

    /**
     * @dataProvider providerDatesAsString
     */
    public function testGetOutOfRepresentationDateAsString($input, $expected)
    {
        /** @var Entity $application */
        $application = m::mock(Entity::class)->makePartial();

        $application->shouldReceive('getOutOfRepresentationDate')
            ->andReturn($input);

        $oorDate = $application->getOutOfRepresentationDateAsString();
        $this->assertEquals($expected, $oorDate);
    }

    public function providerDatesAsString()
    {
        return [
            [Entity::NOT_APPLICABLE, Entity::NOT_APPLICABLE],
            [new DateTime('2015-01-01'), '01 Jan 2015'],
            [Entity::UNKNOWN, Entity::UNKNOWN],
            ['', ''],
            [null, ''],
        ];
    }

    public function testHasActiveS4()
    {
        $mockS4 = m::mock(Entities\Application\S4::class);

        /** @var Entity $sut */
        $sut = m::mock(Entity::class)->makePartial()
            ->shouldReceive('getActiveS4s')->once()->andReturn([$mockS4, clone $mockS4])
            ->getMock();

        static::assertTrue($sut->hasActiveS4());
    }

    /**
     * @dataProvider canHaveCommunityLicencesProvider
     */
    public function testCanHaveCommunityLicences($isStandardInternational, $isPsv, $isRestricted, $expected)
    {
        /** @var Entity $application */
        $application = m::mock(Entity::class)->makePartial();
        $application->shouldReceive('isPsv')
            ->andReturn($isPsv)
            ->shouldReceive('isStandardInternational')
            ->andReturn($isStandardInternational)
            ->shouldReceive('isRestricted')
            ->andReturn($isRestricted);

        $this->assertEquals($expected, $application->canHaveCommunityLicences());
    }

    public function testGetDeltaAocByOc()
    {
        /** @var OperatingCentre $oc */
        $oc = m::mock(OperatingCentre::class)->makePartial();
        /** @var OperatingCentre $oc2 */
        $oc2 = m::mock(OperatingCentre::class)->makePartial();

        /** @var ApplicationOperatingCentre $aoc */
        $aoc = m::mock(ApplicationOperatingCentre::class)->makePartial();
        $aoc->setOperatingCentre($oc);

        /** @var ApplicationOperatingCentre $aoc2 */
        $aoc2 = m::mock(ApplicationOperatingCentre::class)->makePartial();
        $aoc2->setOperatingCentre($oc2);

        $ocs = new ArrayCollection();
        $ocs->add($aoc);
        $ocs->add($aoc2);

        /** @var Entity $application */
        $application = $this->instantiate(Entity::class);
        $application->setOperatingCentres($ocs);

        /** @var ArrayCollection $collection */
        $collection = $application->getDeltaAocByOc($oc);

        $this->assertEquals(1, $collection->count());
        $this->assertSame($aoc, $collection->first());
    }

    public function testGetActiveS4s()
    {
        /** @var RefData $approved */
        $approved = m::mock(RefData::class)->makePartial();
        $approved->setId(S4::STATUS_APPROVED);

        $refused = m::mock(RefData::class)->makePartial();
        $refused->setId(S4::STATUS_REFUSED);

        /** @var S4 $s41 */
        $s41 = m::mock(S4::class)->makePartial();
        $s41->setOutcome(null);
        /** @var S4 $s42 */
        $s42 = m::mock(S4::class)->makePartial();
        $s41->setOutcome($approved);
        /** @var S4 $s43 */
        $s43 = m::mock(S4::class)->makePartial();
        $s43->setOutcome($refused);

        $s4s = new ArrayCollection();
        $s4s->add($s41);
        $s4s->add($s42);
        $s4s->add($s43);

        /** @var Entity $application */
        $application = $this->instantiate(Entity::class);

        $application->setS4s($s4s);

        $active = $application->getActiveS4s();

        $this->assertCount(2, $active);
        static::assertTrue(in_array($s42, $active, true));
        static::assertFalse(in_array($s43, $active, true));
        static::assertTrue(in_array($s41, $active, true));
    }

    public function testIsNew()
    {
        /** @var Entity $application */
        $application = $this->instantiate(Entity::class);

        $application->setIsVariation(true);
        $this->assertFalse($application->isNew());

        $application->setIsVariation(false);
        $this->assertTrue($application->isNew());
    }

    /**
     * @dataProvider dpIsNi
     */
    public function testIsNi($niFlag, $expected)
    {
        $sut = m::mock(Entity::class)->makePartial();

        $sut->setNiFlag($niFlag);

        $this->assertEquals($expected, $sut->isNi());
    }

    public function dpIsNi()
    {
        return [
            ['Y', true],
            ['N', false],
            [null, false],
        ];
    }

    public function testIsRestricted()
    {
        /** @var RefData $sr */
        $sr = m::mock(RefData::class)->makePartial();
        $sr->setId(Licence::LICENCE_TYPE_SPECIAL_RESTRICTED);

        /** @var RefData $r */
        $r = m::mock(RefData::class)->makePartial();
        $r->setId(Licence::LICENCE_TYPE_RESTRICTED);

        /** @var Entity $application */
        $application = $this->instantiate(Entity::class);

        $this->assertFalse($application->isRestricted());

        $application->setLicenceType($sr);
        $this->assertFalse($application->isRestricted());

        $application->setLicenceType($r);
        $this->assertTrue($application->isRestricted());
    }

    public function testIsStandardNational()
    {
        /** @var RefData $sn */
        $sn = m::mock(RefData::class)->makePartial();
        $sn->setId(Licence::LICENCE_TYPE_STANDARD_NATIONAL);

        /** @var Entity $application */
        $application = $this->instantiate(Entity::class);

        static::assertFalse($application->isStandardNational());

        $application->setLicenceType($sn);
        static::assertTrue($application->isStandardNational());
    }

    public function testIsStandardInternational()
    {
        /** @var RefData $sn */
        $sn = m::mock(RefData::class)->makePartial();
        $sn->setId(Licence::LICENCE_TYPE_STANDARD_NATIONAL);

        /** @var RefData $si */
        $si = m::mock(RefData::class)->makePartial();
        $si->setId(Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL);

        /** @var Entity $application */
        $application = $this->instantiate(Entity::class);

        $this->assertFalse($application->isStandardInternational());

        $application->setLicenceType($sn);
        $this->assertFalse($application->isStandardInternational());

        $application->setLicenceType($si);
        $this->assertTrue($application->isStandardInternational());
    }

    /**
     * @dataProvider dpZeroCoalesced
     */
    public function testGetTotAuthHgvVehiclesZeroCoalesced($totAuthHgvVehicles, $expected)
    {
        $application = m::mock(Entity::class)->makePartial();
        $application->shouldReceive('getTotAuthHgvVehicles')
            ->withNoArgs()
            ->andReturn($totAuthHgvVehicles);

        $this->assertEquals(
            $expected,
            $application->getTotAuthHgvVehiclesZeroCoalesced()
        );
    }

    /**
     * @dataProvider dpZeroCoalesced
     */
    public function testGetTotAuthLgvVehiclesZeroCoalesced($totAuthLgvVehicles, $expected)
    {
        $application = m::mock(Entity::class)->makePartial();
        $application->shouldReceive('getTotAuthLgvVehicles')
            ->withNoArgs()
            ->andReturn($totAuthLgvVehicles);

        $this->assertEquals(
            $expected,
            $application->getTotAuthLgvVehiclesZeroCoalesced()
        );
    }

    public function dpZeroCoalesced()
    {
        return [
            [9, 9],
            [null, 0],
        ];
    }

    /**
     * @dataProvider dpCanHaveLgv
     */
    public function testCanHaveLgv($vehicleType, $expected)
    {
        $application = m::mock(Entity::class)->makePartial();
        $application->setVehicleType(new RefData($vehicleType));

        $this->assertEquals(
            $expected,
            $application->canHaveLgv()
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
        $application = m::mock(Entity::class)->makePartial();
        $application->setVehicleType(new RefData($vehicleType));

        $this->assertEquals(
            $expected,
            $application->mustHaveLgv()
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
        $application = m::mock(Entity::class)->makePartial();
        $application->setVehicleType(new RefData($vehicleType));

        $this->assertEquals(
            $expected,
            $application->isLgv()
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
        $application = m::mock(Entity::class)->makePartial();
        $application->setVehicleType(new RefData($vehicleType));

        $this->assertEquals(
            $expected,
            $application->canHaveHgv()
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
        $application = m::mock(Entity::class)->makePartial();
        $application->setVehicleType(new RefData($vehicleType));

        $this->assertEquals(
            $expected,
            $application->canHaveOperatingCentre()
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
        $application = m::mock(Entity::class)->makePartial();
        $application->setVehicleType(new RefData($vehicleType));

        $this->assertEquals(
            $expected,
            $application->mustHaveOperatingCentre()
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
        $application = m::mock(Entity::class)->makePartial();
        $application->setVehicleType(new RefData($vehicleType));

        $this->assertEquals(
            $expected,
            $application->canHaveTrailer()
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

    /**
     * @dataProvider dpHasHgvLgvAuthorisationIncreased
     */
    public function testHasHgvAuthorisationIncreased(
        $isVariation,
        $variationAuthorisation,
        $licenceAuthorisation,
        $expected
    ) {
        $application = m::mock(Entity::class)->makePartial();
        $application->shouldReceive('isVariation')
            ->withNoArgs()
            ->andReturn($isVariation);
        $application->shouldReceive('getTotAuthHgvVehiclesZeroCoalesced')
            ->withNoArgs()
            ->andReturn($variationAuthorisation);
        $application->shouldReceive('getLicence->getTotAuthHgvVehiclesZeroCoalesced')
            ->withNoArgs()
            ->andReturn($licenceAuthorisation);

        $this->assertEquals(
            $expected,
            $application->hasHgvAuthorisationIncreased()
        );
    }

    /**
     * @dataProvider dpHasHgvLgvAuthorisationIncreased
     */
    public function testHasLgvAuthorisationIncreased(
        $isVariation,
        $variationAuthorisation,
        $licenceAuthorisation,
        $expected
    ) {
        $application = m::mock(Entity::class)->makePartial();
        $application->shouldReceive('isVariation')
            ->withNoArgs()
            ->andReturn($isVariation);
        $application->shouldReceive('getTotAuthLgvVehiclesZeroCoalesced')
            ->withNoArgs()
            ->andReturn($variationAuthorisation);
        $application->shouldReceive('getLicence->getTotAuthLgvVehiclesZeroCoalesced')
            ->withNoArgs()
            ->andReturn($licenceAuthorisation);

        $this->assertEquals(
            $expected,
            $application->hasLgvAuthorisationIncreased()
        );
    }

    public function dpHasHgvLgvAuthorisationIncreased()
    {
        return [
            [true, 4, 6, false],
            [true, 5, 6, false],
            [true, 8, 8, false],
            [true, 9, 8, true],
            [true, 11, 9, true],
            [false, 4, 6, false],
            [false, 5, 6, false],
            [false, 8, 8, false],
            [false, 9, 8, false],
            [false, 11, 9, false],
        ];
    }

    /**
     * @dataProvider dpHasLgvAuthorisationChangedFromNullToNumeric
     */
    public function testHasLgvAuthorisationChangedFromNullToNumeric($licenceLgvAuth, $variationLgvAuth, $expected)
    {
        $licence = m::mock(Licence::class)->makePartial();
        $licence->setTotAuthLgvVehicles($licenceLgvAuth);

        $application = m::mock(Entity::class)->makePartial();
        $application->shouldReceive('isVariation')
            ->withNoArgs()
            ->andReturnTrue();
        $application->setLicence($licence);
        $application->setTotAuthLgvVehicles($variationLgvAuth);

        $this->assertEquals(
            $expected,
            $application->hasLgvAuthorisationChangedFromNullToNumeric()
        );
    }

    public function dpHasLgvAuthorisationChangedFromNullToNumeric()
    {
        return [
            [null, null, false],
            [0, 0, false],
            [0, 1, false],
            [null, 0, true],
            [null, 1, true],
        ];
    }

    public function testHasLgvAuthorisationChangedFromNullToNumericException()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'hasLgvAuthorisationChangedFromNullToNumeric is only applicable in a variation context'
        );

        $application = m::mock(Entity::class)->makePartial();
        $application->shouldReceive('isVariation')
            ->withNoArgs()
            ->andReturnFalse();

        $application->hasLgvAuthorisationChangedFromNullToNumeric();
    }

    public function testGetLatestPublication()
    {
        $publDate1 = '2010-11-12';
        $publDate2 = '2011-12-13';

        //  publication with invalid section
        /** @var Entities\Publication\Publication $mockPub */
        $mockPub = m::mock(Entities\Publication\Publication::class);
        $pubLinkInvalicSection = (new Entities\Publication\PublicationLink())
            ->setPublicationSection(
                (new Entities\Publication\PublicationSection())
                    ->setId('INVALID_SECTION')
            )
            ->setPublication($mockPub);

        //  publication with lower date
        /** @var Entities\Publication\Publication $mockPub1 */
        $mockPub1 = m::mock(Entities\Publication\Publication::class)
            ->shouldReceive('getPubDate')->once()->andReturn($publDate1)
            ->getMock();

        $pubLinkLose = new Entities\Publication\PublicationLink();
        $pubLinkLose
            ->setPublicationSection(
                (new Entities\Publication\PublicationSection())
                    ->setId(Entities\Publication\PublicationSection::APP_NEW_SECTION)
            )
            ->setPublication($mockPub1);

        //  publication with higher date
        /** @var Entities\Publication\Publication $mockWin */
        $mockWin = m::mock(Entities\Publication\Publication::class)
            ->shouldReceive('getPubDate')->times(2)->andReturn($publDate2)
            ->getMock();

        $pubLinkWin = (new Entities\Publication\PublicationLink())
            ->setPublicationSection(
                (new Entities\Publication\PublicationSection())
                    ->setId(Entities\Publication\PublicationSection::APP_NEW_SECTION)
            )
            ->setPublication($mockWin);

        //  check return value
        /** @var Entity $sut */
        $sut = $this->instantiate(Entity::class);
        $sut->setPublicationLinks(new ArrayCollection([$pubLinkInvalicSection, $pubLinkLose, $pubLinkWin]));

        static::assertEquals($publDate2, $sut->determinePublishedDate());
    }

    public function testGetActiveVehiclesCount()
    {
        /** @var Entity $application */
        $application = m::mock(Entity::class)->makePartial();

        $application->shouldReceive('getActiveLicenceVehicles->count')
            ->andReturn(10);

        $this->assertEquals(10, $application->getActiveLicenceVehiclesCount());
    }

    public function canHaveCommunityLicencesProvider()
    {
        return [
            'Goods SI' => [
                true,
                false,
                false,
                true
            ],
            'PSV SI' => [
                true,
                true,
                false,
                true
            ],
            'PSV R' => [
                false,
                true,
                true,
                true
            ],
            'PSV Non R' => [
                false,
                true,
                false,
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

        $application = $this->instantiate(Entity::class);
        $application->setGoodsOrPsv($category);

        $this->assertEquals($expected, $application->getCategoryPrefix());
    }

    public function categoryPrefixDp()
    {
        return [
            [Licence::LICENCE_CATEGORY_PSV, 'P'],
            [Licence::LICENCE_CATEGORY_GOODS_VEHICLE, 'O'],
        ];
    }

    public function testDeterminePublicationNrDateOk()
    {
        $publNo = 8888;
        $publDate = '2010-11-12';

        /** @var Entities\Publication\Publication $mockPub */
        $mockPub = m::mock(Entities\Publication\Publication::class)
            ->shouldReceive('getPublicationNo')->once()->andReturn($publNo)
            ->shouldReceive('getPubDate')->once()->andReturn($publDate)
            ->getMock();

        $pubLink = new Entities\Publication\PublicationLink();
        $pubLink
            ->setPublicationSection(
                (new Entities\Publication\PublicationSection())
                    ->setId(Entities\Publication\PublicationSection::APP_NEW_SECTION)
            )
            ->setPublication($mockPub);

        //  check return value
        /** @var Entity $sut */
        $sut = $this->instantiate(Entity::class);
        $sut->setPublicationLinks(new ArrayCollection([$pubLink]));

        static::assertEquals($publNo, $sut->determinePublicationNo());
        static::assertEquals($publDate, $sut->determinePublishedDate());

        //  check return null
        $sut->setPublicationLinks(new ArrayCollection([]));

        static::assertNull($sut->determinePublicationNo());
        static::assertNull($sut->determinePublishedDate());
    }

    public function testHasOpposition()
    {
        $mockCase = m::mock(Entities\Cases\Cases::class)
            ->shouldReceive('getOppositions')->once()->andReturn([1, 2])
            ->getMock();

        //  check true
        /** @var Entity $sut */
        $sut = $this->instantiate(Entity::class);
        $sut->setCases(new ArrayCollection([$mockCase]));

        static::assertTrue($sut->hasOpposition());

        //  check False
        $mockCase = m::mock(Entities\Cases\Cases::class)
            ->shouldReceive('getOppositions')->once()->andReturn([])
            ->getMock();

        $sut->setCases(new ArrayCollection([$mockCase]));

        static::assertFalse($sut->hasOpposition());

        //  check False
        $sut->setCases(new ArrayCollection([]));

        static::assertFalse($sut->hasOpposition());
    }

    public function testGetOtherActiveLicencesForOrganisation()
    {
        $licence1 = m::mock(Licence::class)->makePartial()->setId(7);
        $licence2 = m::mock(Licence::class)->makePartial()->setId(8);
        $organisationLicences = m::mock(ArrayCollection::class)
            ->shouldReceive('toArray')
            ->once()
            ->andReturn([$licence1, $licence2])
            ->getMock();
        $licence1
            ->shouldReceive('getOrganisation->getActiveLicences')
            ->once()
            ->andReturn($organisationLicences);

        /** @var Entity $application */
        $application = $this->instantiate(Entity::class);
        $application->setLicence($licence1);

        $this->assertEquals([$licence2], $application->getOtherActiveLicencesForOrganisation());
    }

    public function testGetOtherActiveLicencesForOrganisationNoActiveLics()
    {
        /** @var Entities\Licence\Licence $licence */
        $licence = m::mock(Entities\Licence\Licence::class);
        $licence->shouldReceive('getOrganisation->getActiveLicences')->once()->andReturn([]);

        /** @var Entity $application */
        $application = $this->instantiate(Entity::class);
        $application->setLicence($licence);

        static::assertEquals([], $application->getOtherActiveLicencesForOrganisation());
    }

    public function testGetOtherActiveLicencesForOrganisationNull()
    {
        /** @var Entity $sut */
        $sut = $this->instantiate(Entity::class);
        $sut->setLicence(null);

        static::assertNull($sut->getOtherActiveLicencesForOrganisation());
    }

    public function testGetActiveLicencesForOrganisation()
    {
        $activeLicences = m::mock(ArrayCollection::class);

        $sut = m::mock(Entity::class)->makePartial();
        $sut->shouldReceive('getLicence->getOrganisation->getActiveLicences')
            ->withNoArgs()
            ->andReturn($activeLicences);

        $this->assertSame(
            $activeLicences,
            $sut->getActiveLicencesForOrganisation()
        );
    }

    public function testGetTrafficArea()
    {
        /** @var Entities\Licence\Licence $mockLic */
        $mockLic = m::mock(Entities\Licence\Licence::class)
            ->shouldReceive('getTrafficArea')->once()->andReturn('EXPECTED')
            ->getMock();

        $sut = new Entity($mockLic, new RefData(), false);

        static::assertEquals('EXPECTED', $sut->getTrafficArea());
    }

    public function testGetOperatingCentresNetDelta()
    {
        /** @var Entity $application */
        $application = $this->instantiate(Entity::class);

        $aocs = new ArrayCollection();
        $application->setOperatingCentres($aocs);

        $this->assertEquals(0, $application->getOperatingCentresNetDelta());

        $application->getOperatingCentres()->add(
            m::mock(ApplicationOperatingCentre::class)->makePartial()->setAction('A')
        );
        $this->assertEquals(1, $application->getOperatingCentresNetDelta());

        $application->getOperatingCentres()->add(
            m::mock(ApplicationOperatingCentre::class)->makePartial()->setAction('A')
        );
        $this->assertEquals(2, $application->getOperatingCentresNetDelta());

        $application->getOperatingCentres()->add(
            m::mock(ApplicationOperatingCentre::class)->makePartial()->setAction('D')
        );
        $this->assertEquals(1, $application->getOperatingCentresNetDelta());
    }

    public function testSetTargetCompletionDateFromReceivedDate()
    {
        /** @var Entity $sut */
        $sut = $this->instantiate(Entity::class);

        $date = new \DateTime('2001-02-03T04:05:06');
        $sut->setReceivedDate($date);

        $sut->setTargetCompletionDateFromReceivedDate();

        static::assertEquals(
            $date->modify('+8 week')->getTimestamp(),
            $sut->getTargetCompletionDate()->getTimestamp()
        );
    }

    /**
     * @dataProvider allowFeePaymentsProvider
     */
    public function testAllowFeePayments($statusId, $licenceStatusId, $expected)
    {
        /** @var Entity $application */
        $application = $this->instantiate(Entity::class);

        /** @var Organisation $organisation */
        $organisation = m::mock(Organisation::class);

        /** @var RefData $status */
        $status = m::mock(RefData::class)
            ->shouldReceive('getId')
            ->andReturn($statusId)
            ->getMock();
        /** @var RefData $licenceStatus */
        $licenceStatus = m::mock(RefData::class)
            ->shouldReceive('getId')
            ->andReturn($licenceStatusId)
            ->getMock();

        $licence = new Licence($organisation, $licenceStatus);

        $application->setStatus($status);
        $application->setLicence($licence);

        $this->assertEquals($expected, $application->allowFeePayments());
    }

    public function dpIsLicenceChangeWhichRequiresOperatingCentre()
    {
        return [
            'application' => [
                'isVariation' => false,
                'licenceVehicleType' => RefData::APP_VEHICLE_TYPE_LGV,
                'applicationVehicleType' => RefData::APP_VEHICLE_TYPE_HGV,
                'expected' => false,
            ],
            'lgv to hgv' => [
                'isVariation' => true,
                'licenceVehicleType' => RefData::APP_VEHICLE_TYPE_LGV,
                'applicationVehicleType' => RefData::APP_VEHICLE_TYPE_HGV,
                'expected' => true,
            ],
            'lgv to mixed' => [
                'isVariation' => true,
                'licenceVehicleType' => RefData::APP_VEHICLE_TYPE_LGV,
                'applicationVehicleType' => RefData::APP_VEHICLE_TYPE_MIXED,
                'expected' => true,
            ],
            'lgv to psv' => [
                'isVariation' => true,
                'licenceVehicleType' => RefData::APP_VEHICLE_TYPE_LGV,
                'applicationVehicleType' => RefData::APP_VEHICLE_TYPE_PSV,
                'expected' => true,
            ],
            'hgv to lgv' => [
                'isVariation' => true,
                'licenceVehicleType' => RefData::APP_VEHICLE_TYPE_HGV,
                'applicationVehicleType' => RefData::APP_VEHICLE_TYPE_LGV,
                'expected' => false,
            ],
            'hgv to mixed' => [
                'isVariation' => true,
                'licenceVehicleType' => RefData::APP_VEHICLE_TYPE_HGV,
                'applicationVehicleType' => RefData::APP_VEHICLE_TYPE_MIXED,
                'expected' => false,
            ],
            'hgv to psv' => [
                'isVariation' => true,
                'licenceVehicleType' => RefData::APP_VEHICLE_TYPE_HGV,
                'applicationVehicleType' => RefData::APP_VEHICLE_TYPE_PSV,
                'expected' => false,
            ],
            'mixed to lgv' => [
                'isVariation' => true,
                'licenceVehicleType' => RefData::APP_VEHICLE_TYPE_MIXED,
                'applicationVehicleType' => RefData::APP_VEHICLE_TYPE_LGV,
                'expected' => false,
            ],
            'mixed to hgv' => [
                'isVariation' => true,
                'licenceVehicleType' => RefData::APP_VEHICLE_TYPE_MIXED,
                'applicationVehicleType' => RefData::APP_VEHICLE_TYPE_HGV,
                'expected' => false,
            ],
            'mixed to psv' => [
                'isVariation' => true,
                'licenceVehicleType' => RefData::APP_VEHICLE_TYPE_MIXED,
                'applicationVehicleType' => RefData::APP_VEHICLE_TYPE_PSV,
                'expected' => false,
            ],
            'psv to lgv' => [
                'isVariation' => true,
                'licenceVehicleType' => RefData::APP_VEHICLE_TYPE_PSV,
                'applicationVehicleType' => RefData::APP_VEHICLE_TYPE_LGV,
                'expected' => false,
            ],
            'psv to hgv' => [
                'isVariation' => true,
                'licenceVehicleType' => RefData::APP_VEHICLE_TYPE_PSV,
                'applicationVehicleType' => RefData::APP_VEHICLE_TYPE_HGV,
                'expected' => false,
            ],
            'psv to mixed' => [
                'isVariation' => true,
                'licenceVehicleType' => RefData::APP_VEHICLE_TYPE_PSV,
                'applicationVehicleType' => RefData::APP_VEHICLE_TYPE_MIXED,
                'expected' => false,
            ],
        ];
    }

    /**
     * @dataProvider dpIsLicenceChangeWhichRequiresOperatingCentre
     */
    public function testIsLicenceChangeWhichRequiresOperatingCentre($isVariation, $licenceVehicleType, $applicationVehicleType, $expected)
    {
        /** @var Licence $licence */
        $licence = m::mock(Licence::class)->makePartial();
        $licence->setVehicleType(new RefData($licenceVehicleType));

        /** @var Entity $application */
        $application = m::mock(Entity::class)->makePartial();
        $application->setIsVariation($isVariation);
        $application->setVehicleType(new RefData($applicationVehicleType));
        $application->setLicence($licence);

        $this->assertEquals($expected, $application->isLicenceChangeWhichRequiresOperatingCentre());
    }

    public function testIsPsvDowngradeGoods()
    {
        /** @var Entity $application */
        $application = m::mock(Entity::class)->makePartial();
        $application->shouldReceive('isGoods')
            ->andReturn(true);

        $this->assertFalse($application->isPsvDowngrade());
    }

    public function testIsPsvDowngradeNotRestricted()
    {
        /** @var Entity $application */
        $application = m::mock(Entity::class)->makePartial();
        $application->shouldReceive('isGoods')->andReturn(false);
        $application->shouldReceive('isRestricted')->andReturn(false);

        $this->assertFalse($application->isPsvDowngrade());
    }

    public function testIsPsvDowngrade()
    {
        /** @var Entity $application */
        $application = m::mock(Entity::class)->makePartial();
        $application->shouldReceive('isGoods')->andReturn(false);
        $application->shouldReceive('isRestricted')->andReturn(true);
        $application->shouldReceive('getLicence->isRestricted')->andReturn(false);

        $this->assertTrue($application->isPsvDowngrade());
    }

    public function testHasAuthChangedNew()
    {
        /** @var Entity $application */
        $application = m::mock(Entity::class)->makePartial();
        $application->shouldReceive('isNew')->andReturn(true);

        $this->assertFalse($application->hasAuthChanged());
    }

    public function testHasAuthChanged()
    {
        /** @var Licence $licence */
        $licence = m::mock(Licence::class)->makePartial();
        $licence->updateTotAuthHgvVehicles(10);
        $licence->updateTotAuthLgvVehicles(10);

        /** @var Entity $application */
        $application = m::mock(Entity::class)->makePartial();
        $application->shouldReceive('isNew')->andReturn(false);
        $application->updateTotAuthHgvVehicles(9);
        $application->updateTotAuthLgvVehicles(11);
        $application->setLicence($licence);

        $this->assertTrue($application->hasAuthChanged());
    }

    public function testHasAuthChangedWithoutChange()
    {
        /** @var Licence $licence */
        $licence = m::mock(Licence::class)->makePartial();
        $licence->updateTotAuthHgvVehicles(10);

        /** @var Entity $application */
        $application = m::mock(Entity::class)->makePartial();
        $application->shouldReceive('isNew')->andReturn(false);
        $application->updateTotAuthHgvVehicles(10);
        $application->setLicence($licence);

        $this->assertFalse($application->hasAuthChanged());
    }

    public function allowFeePaymentsProvider()
    {
        return [
            'refused' => [
                Entity::APPLICATION_STATUS_REFUSED,
                Licence::LICENCE_STATUS_REFUSED,
                false,
            ],
            'withdrawn' => [
                Entity::APPLICATION_STATUS_WITHDRAWN,
                Licence::LICENCE_STATUS_WITHDRAWN,
                false,
            ],
            'ntu' => [
                Entity::APPLICATION_STATUS_NOT_TAKEN_UP,
                Licence::LICENCE_STATUS_NOT_TAKEN_UP,
                false,
            ],
            'licence surrendered' => [
                Entity::APPLICATION_STATUS_UNDER_CONSIDERATION,
                Licence::LICENCE_STATUS_SURRENDERED,
                false,
            ],
            'licence terminated' => [
                Entity::APPLICATION_STATUS_UNDER_CONSIDERATION,
                Licence::LICENCE_STATUS_TERMINATED,
                false,
            ],
            'licence revoked' => [
                Entity::APPLICATION_STATUS_UNDER_CONSIDERATION,
                Licence::LICENCE_STATUS_REVOKED,
                false,
            ],
            'licence cns' => [
                Entity::APPLICATION_STATUS_UNDER_CONSIDERATION,
                Licence::LICENCE_STATUS_CONTINUATION_NOT_SOUGHT,
                false,
            ],
            'under consideration' => [
                Entity::APPLICATION_STATUS_UNDER_CONSIDERATION,
                Licence::LICENCE_STATUS_UNDER_CONSIDERATION,
                true,
            ],
        ];
    }

    /**
     * @dataProvider isUnderConsiderationProvider
     */
    public function testIsUnderConsideration($status, $expected)
    {
        $sut = m::mock(Entity::class)->makePartial();

        $sut->shouldReceive('getStatus->getId')->once()->andReturn($status);
        $this->assertEquals($expected, $sut->isUnderConsideration());
    }

    public function isUnderConsiderationProvider()
    {
        return [
            [Entity::APPLICATION_STATUS_NOT_SUBMITTED, false],
            [Entity::APPLICATION_STATUS_GRANTED, false],
            [Entity::APPLICATION_STATUS_UNDER_CONSIDERATION, true],
            [Entity::APPLICATION_STATUS_VALID, false],
            [Entity::APPLICATION_STATUS_WITHDRAWN, false],
            [Entity::APPLICATION_STATUS_REFUSED, false],
            [Entity::APPLICATION_STATUS_NOT_TAKEN_UP, false],
        ];
    }

    /**
     * @dataProvider dpTestIsNotSubmitted
     */
    public function testIsNotSubmitted($status, $expected)
    {
        $sut = m::mock(Entity::class)->makePartial();
        $sut->shouldReceive('getStatus')->once()->andReturn(new RefData($status));
        $this->assertEquals($expected, $sut->isNotSubmitted());
    }

    public function dpTestIsNotSubmitted()
    {
        return [
            [Entity::APPLICATION_STATUS_NOT_SUBMITTED, true],
            [Entity::APPLICATION_STATUS_GRANTED, false],
            [Entity::APPLICATION_STATUS_UNDER_CONSIDERATION, false],
            [Entity::APPLICATION_STATUS_VALID, false],
            [Entity::APPLICATION_STATUS_WITHDRAWN, false],
            [Entity::APPLICATION_STATUS_REFUSED, false],
            [Entity::APPLICATION_STATUS_NOT_TAKEN_UP, false],
        ];
    }

    /**
     * @dataProvider dpTestGetLicenceTypeShortCode
     * @param string $licenceType
     * @param string $shortCode
     */
    public function testGetLicenceTypeShortCode($licenceType, $shortCode)
    {
        $licence = new Licence(new Organisation(), new RefData());
        $application = new Entity($licence, new RefData(), false);
        $application->setLicenceType((new RefData())->setId($licenceType));

        $this->assertSame($shortCode, $application->getLicenceTypeShortCode());
    }

    public function dpTestGetLicenceTypeShortCode()
    {
        return [
            ['ltyp_r', 'R'],
            ['ltyp_si', 'SI'],
            ['ltyp_sn', 'SN'],
            ['ltyp_sr', 'SR'],
            ['XXXX', null],
        ];
    }

    public function testGetContextValue()
    {
        /** @var Licence $licence */
        $licence = m::mock(Licence::class)->makePartial();
        $licence->setLicNo(111);

        $entity = $this->instantiate(Entity::class);

        $entity->setLicence($licence);

        $this->assertEquals(111, $entity->getContextValue());
    }

    public function testGetOpenTasksForCategory()
    {
        $catId = 9999;
        $subCatId = 8888;

        /** @var Entities\Task\Task $mockTaskNoCat */
        $mockTaskNoCat = m::mock(Entities\Task\Task::class)->makePartial();
        $mockTaskNoCat->setIsClosed('N');
        $mockTaskNoCat->shouldReceive('getCategory->getId')->once()->andReturn('INVALID_CAT');

        /** @var Entities\Task\Task $mockTaskNoSubCat */
        $mockTaskNoSubCat = m::mock(Entities\Task\Task::class)->makePartial();
        $mockTaskNoSubCat->setIsClosed('N');
        $mockTaskNoSubCat->shouldReceive('getCategory->getId')->once()->andReturn($catId);
        $mockTaskNoSubCat->shouldReceive('getSubCategory->getId')->once()->andReturn('INVALID_SUB_CAT');

        /** @var Entities\Task\Task $mockTask */
        $mockTask = m::mock(Entities\Task\Task::class)->makePartial();
        $mockTask->setIsClosed('N');
        $mockTask->shouldReceive('getCategory->getId')->once()->andReturn($catId);
        $mockTask->shouldReceive('getSubCategory->getId')->once()->andReturn($subCatId);

        $mockTasks = new ArrayCollection([$mockTaskNoCat, $mockTaskNoSubCat, $mockTask]);

        /** @var Entity $sut */
        $sut = m::mock(Entity::class)->makePartial()
            ->shouldReceive('getTasks')->once()->andReturn($mockTasks)
            ->getMock();

        static::assertEquals([$mockTask], $sut->getOpenTasksForCategory($catId, $subCatId)->toArray());
    }

    public function testIsPublishableNewApplication()
    {
        $licence = new Licence(new Organisation(), new RefData());
        $application = new Entity($licence, new RefData(), false);
        /* @var $application Entity */

        $this->assertTrue($application->isPublishable());
    }

    public function testIsPublishableFalse()
    {
        $licence = new Licence(new Organisation(), new RefData());
        $licence->setLicenceType(new RefData('Foo'));
        $application = new Entity($licence, new RefData(), true);
        /* @var $application Entity */
        $application->setLicenceType(new RefData('Bar'));

        $this->assertFalse($application->isPublishable());
    }

    public function testIsPublishableNewOc()
    {
        $licence = new Licence(new Organisation(), new RefData());
        $application = new Entity($licence, new RefData(), true);
        /* @var $application Entity */

        $aoc = $this->instantiate(ApplicationOperatingCentre::class);
        /* @var $aoc ApplicationOperatingCentre */
        $aoc->setAction('A');

        $application->addOperatingCentres($aoc);

        $this->assertTrue($application->isPublishable());
    }

    public function testIsPublishableOcIncrease()
    {
        $licence = new Licence(new Organisation(), new RefData());
        $application = new Entity($licence, new RefData(), true);
        /* @var $application Entity */

        $oc = new OperatingCentre();
        $oc->setId(1066);

        $loc = $this->instantiate(\Dvsa\Olcs\Api\Entity\Licence\LicenceOperatingCentre::class);
        $loc->setNoOfVehiclesRequired(9);
        $loc->setOperatingCentre($oc);
        $licence->addOperatingCentres($loc);

        $aoc = $this->instantiate(ApplicationOperatingCentre::class);
        /* @var $aoc ApplicationOperatingCentre */
        $aoc->setAction('U');
        $aoc->setOperatingCentre($oc);
        $aoc->setNoOfVehiclesRequired(10);
        $application->addOperatingCentres($aoc);

        $this->assertTrue($application->isPublishable());
    }

    public function testIsPublishableUpgrade()
    {
        $licence = new Licence(new Organisation(), new RefData());
        $licence->setLicenceType(new RefData(Licence::LICENCE_TYPE_STANDARD_NATIONAL));
        $application = new Entity($licence, new RefData(), true);
        /* @var $application Entity */
        $application->setLicenceType(new RefData(Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL));

        $this->assertTrue($application->isPublishable());
    }

    public function testIsPublishableIsNewPsvSpecRestrict()
    {
        /** @var Entity $sut */
        $sut = m::mock(Entity::class)->makePartial()
            ->shouldReceive('isNew')->once()->andReturn(true)
            ->shouldReceive('isPsv')->once()->andReturn(true)
            ->shouldReceive('isSpecialRestricted')->once()->andReturn(true)
            ->getMock();

        static::assertFalse($sut->isPublishable());
    }

    public function testIsPublishableVariationFalse()
    {
        /** @var Entity $sut */
        $sut = m::mock(Entity::class)->makePartial()->shouldAllowMockingProtectedMethods()
            ->shouldReceive('isNew')->with()->once()->andReturn(false)
            ->shouldReceive('hasLgvAuthorisationIncreased')->with()->once()->andReturn(false)
            ->shouldReceive('hasHgvAuthorisationIncreased')->with()->once()->andReturn(false)
            ->shouldReceive('hasAuthTrailersIncrease')->with()->once()->andReturn(false)
            ->shouldReceive('hasNewOperatingCentre')->with()->once()->andReturn(false)
            ->shouldReceive('hasIncreaseInOperatingCentre')->once()->andReturn(false)
            ->shouldReceive('isRealUpgrade')->once()->andReturn(false)
            ->shouldReceive('getConditionUndertakings')->once()->andReturn(new ArrayCollection())
            ->getMock();

        static::assertFalse($sut->isPublishable());
    }

    public function testIsPublishableVariationConditionUndertakingChanged()
    {
        /** @var Entity $sut */
        $sut = m::mock(Entity::class)->makePartial()->shouldAllowMockingProtectedMethods()
            ->shouldReceive('isNew')->with()->once()->andReturn(false)
            ->shouldReceive('hasLgvAuthorisationIncreased')->with()->once()->andReturn(false)
            ->shouldReceive('hasHgvAuthorisationIncreased')->with()->once()->andReturn(false)
            ->shouldReceive('hasAuthTrailersIncrease')->with()->once()->andReturn(false)
            ->shouldReceive('hasNewOperatingCentre')->with()->once()->andReturn(false)
            ->shouldReceive('hasIncreaseInOperatingCentre')->once()->andReturn(false)
            ->shouldReceive('isRealUpgrade')->once()->andReturn(false)
            ->shouldReceive('getConditionUndertakings')->once()->andReturn(new ArrayCollection(['FOO']))
            ->getMock();

        static::assertTrue($sut->isPublishable());
    }

    public function testIsPublishableLgvAuthorisationIncreased()
    {
        /** @var Entity $sut */
        $sut = m::mock(Entity::class)->makePartial()->shouldAllowMockingProtectedMethods()
            ->shouldReceive('isNew')->with()->once()->andReturn(false)
            ->shouldReceive('hasLgvAuthorisationIncreased')->with()->once()->andReturn(true)
            ->getMock();

        static::assertTrue($sut->isPublishable());
    }

    public function testIsPublishableGoodsHgvAuthorisationIncreased()
    {
        /** @var Entity $sut */
        $sut = m::mock(Entity::class)->makePartial()->shouldAllowMockingProtectedMethods()
            ->shouldReceive('isNew')->with()->once()->andReturn(false)
            ->shouldReceive('hasLgvAuthorisationIncreased')->with()->once()->andReturn(false)
            ->shouldReceive('hasHgvAuthorisationIncreased')->with()->once()->andReturn(true)
            ->shouldReceive('isGoods')->with()->once()->andReturn(true)
            ->getMock();

        static::assertTrue($sut->isPublishable());
    }

    public function testIsPublishablePsvHgvAuthorisationIncreased()
    {
        /** @var Entity $sut */
        $sut = m::mock(Entity::class)->makePartial()->shouldAllowMockingProtectedMethods()
            ->shouldReceive('isNew')->with()->once()->andReturn(false)
            ->shouldReceive('hasLgvAuthorisationIncreased')->with()->once()->andReturn(false)
            ->shouldReceive('hasHgvAuthorisationIncreased')->with()->once()->andReturn(true)
            ->shouldReceive('isGoods')->with()->once()->andReturn(false)
            ->shouldReceive('hasAuthTrailersIncrease')->with()->once()->andReturn(false)
            ->shouldReceive('hasNewOperatingCentre')->with()->once()->andReturn(false)
            ->shouldReceive('hasIncreaseInOperatingCentre')->once()->andReturn(false)
            ->shouldReceive('isRealUpgrade')->once()->andReturn(false)
            ->shouldReceive('getConditionUndertakings')->once()->andReturn(new ArrayCollection())
            ->getMock();

        static::assertFalse($sut->isPublishable());
    }

    public function testIsPublishableTrailerAuthorisationIncreased()
    {
        /** @var Entity $sut */
        $sut = m::mock(Entity::class)->makePartial()->shouldAllowMockingProtectedMethods()
            ->shouldReceive('isNew')->with()->once()->andReturn(false)
            ->shouldReceive('hasLgvAuthorisationIncreased')->with()->once()->andReturn(false)
            ->shouldReceive('hasHgvAuthorisationIncreased')->with()->once()->andReturn(false)
            ->shouldReceive('hasAuthTrailersIncrease')->with()->once()->andReturn(true)
            ->getMock();

        static::assertTrue($sut->isPublishable());
    }

    /**
     * @dataProvider dpTestIsPsvVehicleSizeSmall
     */
    public function testIsPsvVehicleSizeSmall($type, $expect)
    {
        $mockRefData = (new RefData())->setId($type);

        /** @var Entity $sut */
        $sut = m::mock(Entity::class)->makePartial()
            ->shouldReceive('getPsvWhichVehicleSizes')->times(2)->andReturn($mockRefData)
            ->getMock();

        static::assertEquals($expect, $sut->isPsvVehicleSizeSmall());
    }

    public function dpTestIsPsvVehicleSizeSmall()
    {
        return [
            [
                'type' => Entity::PSV_VEHICLE_SIZE_SMALL,
                'expect' => true,
            ],
            [
                'type' => 'INVALID_TYPE',
                'expect' => false,
            ],
        ];
    }

    /**
     * @dataProvider dpTestIsPsvVehicleSizeMediumLarge
     */
    public function testIsPsvVehicleSizeMediumLarge($type, $expect)
    {
        $mockRefData = (new RefData())->setId($type);

        /** @var Entity $sut */
        $sut = m::mock(Entity::class)->makePartial()
            ->shouldReceive('getPsvWhichVehicleSizes')->times(2)->andReturn($mockRefData)
            ->getMock();

        static::assertEquals($expect, $sut->isPsvVehicleSizeMediumLarge());
    }

    public function dpTestIsPsvVehicleSizeMediumLarge()
    {
        return [
            [
                'type' => Entity::PSV_VEHICLE_SIZE_MEDIUM_LARGE,
                'expect' => true,
            ],
            [
                'type' => 'INVALID_TYPE',
                'expect' => false,
            ],
        ];
    }

    /**
     * @dataProvider dpTestIsPsvVehicleSizeBoth
     */
    public function testIsPsvVehicleSizeBoth($type, $expect)
    {
        $refData = (new RefData())->setId($type);

        /** @var Entity $sut */
        $sut = m::mock(Entity::class)->makePartial()
            ->shouldReceive('getPsvWhichVehicleSizes')->times(2)->andReturn($refData)
            ->getMock();

        static::assertEquals($expect, $sut->isPsvVehicleSizeBoth());
    }

    public function dpTestIsPsvVehicleSizeBoth()
    {
        return [
            [
                'type' => Entity::PSV_VEHICLE_SIZE_BOTH,
                'expect' => true,
            ],
            [
                'type' => 'INVALID_TYPE',
                'expect' => false,
            ],
        ];
    }

    /**
     * @dataProvider dpTestGetLatestOutstandingApplicationFeeOk
     */
    public function testGetLatestOutstandingApplicationFeeOk($isOutstanding, $isVariation, $expectFeeType, $expect)
    {
        $mockFee = m::mock(Entities\Fee\Fee::class);
        $mockFee
            ->shouldReceive('isOutstanding')->once()->andReturn($isOutstanding)
            ->shouldReceive('getFeeType->getFeeType->getId')
            ->times($isOutstanding ? 1 : 0)
            ->andReturn($expectFeeType);

        $mockFees = m::mock(ArrayCollection::class)
            ->shouldReceive('matching')->once()->andReturnUsing(
                function (Criteria $item) use ($mockFee) {
                    static::assertEquals(['invoicedDate' => 'DESC'], $item->getOrderings());

                    return new \ArrayIterator([$mockFee]);
                }
            )
            ->getMock();

        /** @var Entity $sut */
        $sut = m::mock(Entity::class)->makePartial()
            ->shouldReceive('isVariation')->once()->andReturn($isVariation)
            ->shouldReceive('getFees')->once()->andReturn($mockFees)
            ->getMock();

        static::assertEquals(
            ($expect === null ? null : $mockFee),
            $sut->getLatestOutstandingApplicationFee()
        );
    }

    public function dpTestGetLatestOutstandingApplicationFeeOk()
    {
        return [
            [
                'isOutstanding' => true,
                'isVariation' => true,
                'expectFeeType' => Entities\Fee\FeeType::FEE_TYPE_VAR,
                'expect' => 'MOCK',
            ],
            [
                'isOutstanding' => true,
                'isVariation' => false,
                'expectFeeType' => Entities\Fee\FeeType::FEE_TYPE_APP,
                'expect' => 'MOCK',
            ],
            [
                'isOutstanding' => false,
                'isVariation' => false,
                'expectFeeType' => Entities\Fee\FeeType::FEE_TYPE_APP,
                'expect' => null,
            ],
            [
                'isOutstanding' => true,
                'isVariation' => false,
                'expectFeeType' => 'INVALID_TYPE',
                'expect' => null,
            ],
        ];
    }

    public function testGetLatestOutstandingInterimFeeOk()
    {
        $mockFee = m::mock(Entities\Fee\Fee::class);
        $mockFee
            ->shouldReceive('isOutstanding')->once()->andReturn(true)
            ->shouldReceive('getFeeType->getFeeType->getId')
            ->once()
            ->andReturn(Entities\Fee\FeeType::FEE_TYPE_GRANTINT);

        $mockFees = m::mock(ArrayCollection::class)
            ->shouldReceive('matching')->once()->andReturnUsing(
                function (Criteria $item) use ($mockFee) {
                    static::assertEquals(['invoicedDate' => 'DESC'], $item->getOrderings());

                    return new \ArrayIterator([$mockFee]);
                }
            )
            ->getMock();

        /** @var Entity $sut */
        $sut = m::mock(Entity::class)->makePartial()
            ->shouldReceive('getFees')->once()->andReturn($mockFees)
            ->getMock();

        static::assertEquals($mockFee, $sut->getLatestOutstandingInterimFee());
    }

    public function testGetLatestOutstandingInterimFeeNull()
    {
        /** @var Entity $sut */
        $sut = m::mock(Entity::class)->makePartial();
        $sut->shouldReceive('getFees->matching')->once()->andReturn([]);

        static::assertNull($sut->getLatestOutstandingInterimFee());
    }

    public function testHasOutstandingGrantFee()
    {
        $application = $this->instantiate(Entity::class);

        $this->assertFalse($application->hasOutstandingGrantFee());

        $fee = m::mock()
            ->shouldReceive('isGrantFee')
            ->andReturn(true)
            ->shouldReceive('isOutstanding')
            ->andReturn(true)
            ->shouldReceive('getId')
            ->andReturn(99)
            ->getMock();

        $application->setFees(new ArrayCollection([$fee]));

        $this->assertTrue($application->hasOutstandingGrantFee());
    }

    /**
     * @param RefData $appliedVia
     * @dataProvider createdInternallyProvider
     */
    public function testCreatedInternally($appliedVia, mixed $expected)
    {
        $application = $this->instantiate(Entity::class);

        $application->setAppliedVia($appliedVia);

        $this->assertSame($expected, $application->createdInternally());
    }

    public function createdInternallyProvider()
    {
        return [
            [
                null,
                null,
            ],
            [
                new RefData(Entity::APPLIED_VIA_POST),
                true,
            ],
            [
                new RefData(Entity::APPLIED_VIA_PHONE),
                true,
            ],
            [
                new RefData(Entity::APPLIED_VIA_SELFSERVE),
                false,
            ],
        ];
    }

    /**
     * @param FeeEntity $fee
     * @param bool $expected
     * @dataProvider hasApplicationFeeProvider
     */
    public function testHasApplicationFee($fee, $expected)
    {
        $application = $this->instantiate(Entity::class);

        $this->assertFalse($application->hasApplicationFee());

        if ($fee) {
            $application->setFees(new ArrayCollection([$fee]));
        }

        $this->assertSame($expected, $application->hasApplicationFee());
    }

    public function hasApplicationFeeProvider()
    {
        return [
            'no fee' => [
                null,
                false,
            ],
            'cancelled fee' => [
                m::mock()
                    ->shouldReceive('isNewApplicationFee')
                    ->andReturn(true)
                    ->shouldReceive('isVariationFee')
                    ->andReturn(false)
                    ->shouldReceive('isCancelled')
                    ->andReturn(true)
                    ->getMock(),
                false,
            ],
            'new app fee' => [
                m::mock()
                    ->shouldReceive('isNewApplicationFee')
                    ->andReturn(true)
                    ->shouldReceive('isVariationFee')
                    ->andReturn(false)
                    ->shouldReceive('isCancelled')
                    ->andReturn(false)
                    ->getMock(),
                true,
            ],
            'variation fee' => [
                m::mock()
                    ->shouldReceive('isNewApplicationFee')
                    ->andReturn(false)
                    ->shouldReceive('isVariationFee')
                    ->andReturn(true)
                    ->shouldReceive('isCancelled')
                    ->andReturn(false)
                    ->getMock(),
                true,
            ],
        ];
    }

    public function testGetApplicationReferenceNoLicence()
    {
        $this->entity->setLicence(null);
        $this->entity->setId(34);

        $this->assertSame(34, $this->entity->getApplicationReference());
    }

    public function testGetApplicationReferenceNoLicNo()
    {
        $this->entity->setId(34);

        $this->assertSame(34, $this->entity->getApplicationReference());
    }

    public function testGetApplicationReference()
    {
        $this->entity->setId(34);
        $this->entity->getLicence()->setLicNo('AB12345');

        $this->assertSame('AB12345/34', $this->entity->getApplicationReference());
    }

    /**
     * @dataProvider dataProviderValidateTol
     */
    public function testValidateTolNotValid(
        $niFlag,
        $goodsOrPsv,
        $licenceType,
        $vehicleType,
        $lgvDeclarationConfirmation,
        $isVariation,
        $currentGoodsOrPsv,
        $currentNiFlag,
        $expectedErrors
    ) {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\ValidationException::class);
        $this->expectExceptionMessage(var_export($expectedErrors, true));

        /** @var Entity $sut */
        $sut = m::mock(Entity::class)->makePartial();
        $sut->setIsVariation($isVariation)
            ->setGoodsOrPsv($currentGoodsOrPsv)
            ->setNiFlag($currentNiFlag);

        $mockGoodsOrPsv = $goodsOrPsv ? m::mock()->shouldReceive('getId')->andReturn($goodsOrPsv)->getMock() : null;
        $mockLicenceType = $licenceType ? m::mock()->shouldReceive('getId')->andReturn($licenceType)->getMock() : null;
        $mockVehicleType = $vehicleType ? m::mock()->shouldReceive('getId')->andReturn($vehicleType)->getMock() : null;

        $sut->validateTol($niFlag, $mockGoodsOrPsv, $mockLicenceType, $mockVehicleType, $lgvDeclarationConfirmation);
    }

    public function dataProviderValidateTol()
    {
        return [
            [
                'niFlag' => 'Y',
                'goodsOrPsv' => null,
                'licenceType' => null,
                'vehicleType' => null,
                'lgvDeclarationConfirmation' => null,
                'isVariation' => false,
                'currentGoodsOrPsv' => null,
                'currentNiFlag' => null,
                'expectedErrors' => [
                    'goodsOrPsv' => [
                        [Entity::ERROR_OT_REQUIRED => 'Operator type is required'],
                    ],
                ],
            ],
            [
                'niFlag' => 'Y',
                'goodsOrPsv' => Licence::LICENCE_CATEGORY_PSV,
                'licenceType' => Licence::LICENCE_TYPE_STANDARD_NATIONAL,
                'vehicleType' => null,
                'lgvDeclarationConfirmation' => null,
                'isVariation' => false,
                'currentGoodsOrPsv' => null,
                'currentNiFlag' => null,
                'expectedErrors' => [
                    'goodsOrPsv' => [
                        [Entity::ERROR_NI_NON_GOODS => 'NI can only apply for goods licences'],
                    ],
                    'licenceType' => [
                        ['Vehicle type must be specified for all licences'],
                    ],
                ],
            ],
            [
                'niFlag' => 'N',
                'goodsOrPsv' => Licence::LICENCE_CATEGORY_GOODS_VEHICLE,
                'licenceType' => Licence::LICENCE_TYPE_SPECIAL_RESTRICTED,
                'vehicleType' => null,
                'lgvDeclarationConfirmation' => null,
                'isVariation' => false,
                'currentGoodsOrPsv' => null,
                'currentNiFlag' => null,
                'expectedErrors' => [
                    'licenceType' => [
                        [Entity::ERROR_GV_NON_SR => 'GV operators cannot apply for special restricted licences'],
                        ['Vehicle type must be specified for all licences'],
                    ],
                ],
            ],
            [
                'niFlag' => 'Y',
                'goodsOrPsv' => Licence::LICENCE_CATEGORY_GOODS_VEHICLE,
                'licenceType' => Licence::LICENCE_TYPE_SPECIAL_RESTRICTED,
                'vehicleType' => null,
                'lgvDeclarationConfirmation' => null,
                'isVariation' => true,
                'currentGoodsOrPsv' => Licence::LICENCE_TYPE_STANDARD_NATIONAL,
                'currentNiFlag' => null,
                'expectedErrors' => [
                    'licenceType' => [
                        [Entity::ERROR_GV_NON_SR => 'GV operators cannot apply for special restricted licences'],
                        ['Vehicle type must be specified for all licences'],
                    ],
                    'goodsOrPsv' => [
                        [Entity::ERROR_GV_NON_SR => 'GV operators cannot apply for special restricted licences'],
                    ],
                    'niFlag' => [
                        [Entity::ERROR_GV_NON_SR => 'GV operators cannot apply for special restricted licences'],
                    ],
                ],
            ],
            [
                'niFlag' => 'Y',
                'goodsOrPsv' => Licence::LICENCE_CATEGORY_GOODS_VEHICLE,
                'licenceType' => Licence::LICENCE_TYPE_STANDARD_NATIONAL,
                'vehicleType' => null,
                'lgvDeclarationConfirmation' => null,
                'isVariation' => true,
                'currentGoodsOrPsv' => Licence::LICENCE_TYPE_STANDARD_NATIONAL,
                'currentNiFlag' => 'N',
                'expectedErrors' => [
                    'goodsOrPsv' => [
                        [Entity::ERROR_GV_NON_SR => 'GV operators cannot apply for special restricted licences'],
                    ],
                    'niFlag' => [
                        [Entity::ERROR_GV_NON_SR => 'GV operators cannot apply for special restricted licences'],
                    ],
                    'licenceType' => [
                        ['Vehicle type must be specified for all licences'],
                    ],
                ],
            ],
            [
                'niFlag' => 'N',
                'goodsOrPsv' => Licence::LICENCE_CATEGORY_GOODS_VEHICLE,
                'licenceType' => Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                'vehicleType' => RefData::APP_VEHICLE_TYPE_HGV,
                'lgvDeclarationConfirmation' => null,
                'isVariation' => false,
                'currentGoodsOrPsv' => Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                'currentNiFlag' => 'N',
                'expectedErrors' => [
                    'licenceType' => [
                        ['Vehicle type must be either HGV or LGV when application is for Goods/Standard International'],
                    ],
                ],
            ],
            [
                'niFlag' => 'N',
                'goodsOrPsv' => Licence::LICENCE_CATEGORY_GOODS_VEHICLE,
                'licenceType' => Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                'vehicleType' => RefData::APP_VEHICLE_TYPE_LGV,
                'lgvDeclarationConfirmation' => null,
                'isVariation' => false,
                'currentGoodsOrPsv' => Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                'currentNiFlag' => 'N',
                'expectedErrors' => [
                    'licenceType' => [
                        ['LGV declaration confirmation must be ticked for Goods/Standard International/LGV'],
                    ],
                ],
            ],
            [
                'niFlag' => 'N',
                'goodsOrPsv' => Licence::LICENCE_CATEGORY_GOODS_VEHICLE,
                'licenceType' => Licence::LICENCE_TYPE_STANDARD_NATIONAL,
                'vehicleType' => RefData::APP_VEHICLE_TYPE_LGV,
                'lgvDeclarationConfirmation' => 1,
                'isVariation' => false,
                'currentGoodsOrPsv' => Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                'currentNiFlag' => 'N',
                'expectedErrors' => [
                    'licenceType' => [
                        ['Provided vehicle type must match the corresponding licence category value'],
                        ['LGV declaration confirmation must only be specified for Goods/Standard International licences'],
                    ],
                ],
            ],
            [
                'niFlag' => 'N',
                'goodsOrPsv' => Licence::LICENCE_CATEGORY_PSV,
                'licenceType' => Licence::LICENCE_TYPE_STANDARD_NATIONAL,
                'vehicleType' => RefData::APP_VEHICLE_TYPE_HGV,
                'lgvDeclarationConfirmation' => null,
                'isVariation' => false,
                'currentGoodsOrPsv' => Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                'currentNiFlag' => 'N',
                'expectedErrors' => [
                    'licenceType' => [
                        ['Provided vehicle type must match the corresponding licence category value'],
                    ],
                ],
            ],
        ];
    }

    public function testValidateTolValid()
    {
        /** @var Entity $sut */
        $sut = m::mock(Entity::class)->makePartial();

        $mockGoodsOrPsv = m::mock()
            ->shouldReceive('getId')
            ->andReturn(Licence::LICENCE_CATEGORY_GOODS_VEHICLE)
            ->getMock();
        $mockLicenceType = m::mock()
            ->shouldReceive('getId')
            ->andReturn(Licence::LICENCE_TYPE_STANDARD_NATIONAL)
            ->getMock();
        $mockVehicleType = m::mock()
            ->shouldReceive('getId')
            ->andReturn(RefData::APP_VEHICLE_TYPE_HGV)
            ->getMock();

        $this->assertTrue($sut->validateTol('Y', $mockGoodsOrPsv, $mockLicenceType, $mockVehicleType, 0));
    }

    public function testGetCalculatedBundleValues()
    {
        /** @var Entity $sut */
        $sut = m::mock(Entity::class)->makePartial()
            ->shouldReceive('getApplicationReference')->once()->andReturn('EXPECTED')
            ->shouldReceive('getOutstandingGrantFees')->once()->andReturn(null)
            ->getMock();

        static::assertEquals(
            [
                'applicationReference' => 'EXPECTED',
                'awaitingGrantFeeId' => null
            ],
            $sut->getCalculatedBundleValues()
        );
    }

    public function testGetRelatedOrganisation()
    {
        /** @var Entity $sut */
        $sut = m::mock(Entity::class)->makePartial();
        $sut->shouldReceive('getLicence->getOrganisation')->once()->andReturn('EXPECTED');

        static::assertEquals('EXPECTED', $sut->getRelatedOrganisation());
    }

    public function testGetOutOfRepresentationDateWithDeletedOc()
    {
        $this->entity->setVehicleType(new RefData(RefData::APP_VEHICLE_TYPE_HGV));

        $aoc1 = new ApplicationOperatingCentre($this->entity, new OperatingCentre());
        $aoc1->setAction('D')
            ->setAdPlaced(ApplicationOperatingCentre::AD_UPLOAD_NOW)
            ->setAdPlacedDate('2015-04-21')
            ->setNoOfVehiclesRequired(4);
        $this->entity->addOperatingCentres($aoc1);

        $aoc2 = new ApplicationOperatingCentre($this->entity, new OperatingCentre());
        $aoc2->setAction('A')
            ->setAdPlaced(ApplicationOperatingCentre::AD_UPLOAD_NOW)
            ->setAdPlacedDate('2015-04-23')
            ->setNoOfVehiclesRequired(4);
        $this->entity->addOperatingCentres($aoc2);

        $oorDate = $this->entity->getOutOfRepresentationDate();

        $this->assertEquals(new DateTime('2015-05-14'), $oorDate);
    }

    /**
     * @dataProvider dpTestIsDigitallySigned
     *
     * @param $expected         Expected true or false
     * @param $signatureType    Eg Application::SIG_PHYSICAL_SIGNATURE
     * @param $digitalSignature DigitalSignature entity
     */
    public function testIsDigitallySigned($expected, $signatureType, $digitalSignature)
    {
        /** @var Entity $sut */
        $sut = m::mock(Entity::class)->makePartial();
        $sut->setSignatureType(new RefData($signatureType));
        $sut->setDigitalSignature($digitalSignature);
        $this->assertSame($expected, $sut->isDigitallySigned());
    }

    public function dpTestIsDigitallySigned()
    {
        return [
            [false, null, null],
            [false, Entity::SIG_PHYSICAL_SIGNATURE, null],
            [false, Entity::SIG_DIGITAL_SIGNATURE, null],
            [false, Entity::SIG_SIGNATURE_NOT_REQUIRED, null],
            [false, Entity::SIG_PHYSICAL_SIGNATURE, new Entities\DigitalSignature()],
            [false, Entity::SIG_SIGNATURE_NOT_REQUIRED, new Entities\DigitalSignature()],
            [false, '', new Entities\DigitalSignature()],
            [true, Entity::SIG_DIGITAL_SIGNATURE, new Entities\DigitalSignature()],
        ];
    }

    /**
     * @dataProvider dpIsPreviouslyPublished
     */
    public function testIsPreviouslyPublished($publicationSectionId, $expected)
    {
        $application = $this->instantiate(Entity::class);

        $this->assertFalse($application->isPreviouslyPublished());

        $publicationSection = new PublicationSectionEntity();
        $publicationSection->setId($publicationSectionId);

        $publicationLink = new Entities\Publication\PublicationLink();
        $publicationLink->setPublicationSection($publicationSection);

        $publicationLinks = new ArrayCollection();
        $publicationLinks->add($publicationLink);
        $application->setPublicationLinks($publicationLinks);

        $this->assertSame($expected, $application->isPreviouslyPublished());
    }

    public function dpIsPreviouslyPublished()
    {
        return [
            [PublicationSectionEntity::APP_NEW_SECTION, true],
            [PublicationSectionEntity::APP_GRANTED_SECTION, true],
            [PublicationSectionEntity::APP_REFUSED_SECTION, false],
            [PublicationSectionEntity::APP_WITHDRAWN_SECTION, false],
            [PublicationSectionEntity::APP_GRANT_NOT_TAKEN_SECTION, false],
            [PublicationSectionEntity::VAR_NEW_SECTION, true],
            [PublicationSectionEntity::VAR_GRANTED_SECTION, true],
            [PublicationSectionEntity::VAR_REFUSED_SECTION, false],
            [PublicationSectionEntity::SCHEDULE_1_NI_NEW, true],
            [PublicationSectionEntity::SCHEDULE_4_NEW, true],
            [PublicationSectionEntity::SCHEDULE_1_NI_UNTRUE, true],
            [PublicationSectionEntity::SCHEDULE_4_UNTRUE, true],
            [PublicationSectionEntity::SCHEDULE_1_NI_TRUE, true],
            [PublicationSectionEntity::SCHEDULE_4_TRUE, true],
            [PublicationSectionEntity::LIC_SURRENDERED_SECTION, false],
            [PublicationSectionEntity::LIC_TERMINATED_SECTION, false],
            [PublicationSectionEntity::LIC_REVOKED_SECTION, false],
            [PublicationSectionEntity::LIC_CNS_SECTION, false],
            [PublicationSectionEntity::HEARING_SECTION, false],
            [PublicationSectionEntity::DECISION_SECTION, false],
            [PublicationSectionEntity::TM_HEARING_SECTION, false],
            [PublicationSectionEntity::TM_DECISION_SECTION, false],
            [PublicationSectionEntity::BUS_NEW_SECTION, false],
            [PublicationSectionEntity::BUS_NEW_SHORT_SECTION, false],
            [PublicationSectionEntity::BUS_VAR_SECTION, false],
            [PublicationSectionEntity::BUS_VAR_SHORT_SECTION, false],
            [PublicationSectionEntity::BUS_CANCEL_SECTION, false],
            [PublicationSectionEntity::BUS_CANCEL_SHORT_SECTION, false],
        ];
    }

    /**
     * @dataProvider dataProviderTestCanAddFinancialEvidence
     */
    public function testCanAddFinancialEvidence(
        $expected,
        $isVariation,
        $trackingStatus,
        $financialEvidenceUploaded,
        $licenceType,
        $completionStatus
    ) {
        /** @var Entity $sut */
        $sut = $this->instantiate(Entity::class);
        $sut->setIsVariation($isVariation);

        $tracking = new Entities\Application\ApplicationTracking($sut);
        $tracking->setFinancialEvidenceStatus($trackingStatus);
        $sut->setApplicationTracking($tracking);
        $sut->setFinancialEvidenceUploaded($financialEvidenceUploaded);
        $sut->setLicenceType($licenceType);
        $completion = new ApplicationCompletion($sut);
        $completion->setFinancialEvidenceStatus($completionStatus);
        $sut->setApplicationCompletion($completion);

        $this->assertSame($expected, $sut->canAddFinancialEvidence());
    }

    public function dataProviderTestCanAddFinancialEvidence()
    {
        return [
            [
                true,
                true,
                Entities\Application\ApplicationTracking::STATUS_NOT_SET,
                Entity::FINANCIAL_EVIDENCE_SEND_IN_POST,
                Licence::LICENCE_TYPE_RESTRICTED,
                ApplicationCompletion::STATUS_VARIATION_UPDATED
            ],
            [
                true,
                true,
                Entities\Application\ApplicationTracking::STATUS_NOT_ACCEPTED,
                Entity::FINANCIAL_EVIDENCE_SEND_IN_POST,
                Licence::LICENCE_TYPE_RESTRICTED,
                ApplicationCompletion::STATUS_VARIATION_UPDATED
            ],
            [
                true,
                true,
                Entities\Application\ApplicationTracking::STATUS_NOT_ACCEPTED,
                Entity::FINANCIAL_EVIDENCE_UPLOAD_LATER,
                Licence::LICENCE_TYPE_STANDARD_NATIONAL,
                ApplicationCompletion::STATUS_VARIATION_UPDATED
            ],
            [
                true,
                true,
                Entities\Application\ApplicationTracking::STATUS_NOT_ACCEPTED,
                Entity::FINANCIAL_EVIDENCE_UPLOAD_LATER,
                Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                ApplicationCompletion::STATUS_VARIATION_UPDATED
            ],
            [
                true,
                false,
                Entities\Application\ApplicationTracking::STATUS_NOT_ACCEPTED,
                Entity::FINANCIAL_EVIDENCE_UPLOAD_LATER,
                Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                ApplicationCompletion::STATUS_NOT_STARTED
            ],
            [
                true,
                false,
                Entities\Application\ApplicationTracking::STATUS_NOT_ACCEPTED,
                Entity::FINANCIAL_EVIDENCE_UPLOAD_LATER,
                Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                ApplicationCompletion::STATUS_INCOMPLETE
            ],
            [
                false,
                false,
                Entities\Application\ApplicationTracking::STATUS_ACCEPTED,
                Entity::FINANCIAL_EVIDENCE_SEND_IN_POST,
                Licence::LICENCE_TYPE_RESTRICTED,
                ApplicationCompletion::STATUS_VARIATION_UPDATED
            ],
            [
                false,
                false,
                Entities\Application\ApplicationTracking::STATUS_NOT_APPLICABLE,
                Entity::FINANCIAL_EVIDENCE_SEND_IN_POST,
                Licence::LICENCE_TYPE_RESTRICTED,
                ApplicationCompletion::STATUS_VARIATION_UPDATED
            ],
            [
                false,
                false,
                Entities\Application\ApplicationTracking::STATUS_NOT_SET,
                Entity::FINANCIAL_EVIDENCE_UPLOADED,
                Licence::LICENCE_TYPE_RESTRICTED,
                ApplicationCompletion::STATUS_VARIATION_UPDATED
            ],
            [
                false,
                false,
                Entities\Application\ApplicationTracking::STATUS_NOT_SET,
                Entity::FINANCIAL_EVIDENCE_UPLOAD_LATER,
                Licence::LICENCE_TYPE_SPECIAL_RESTRICTED,
                ApplicationCompletion::STATUS_VARIATION_UPDATED
            ],
            [
                false,
                true,
                Entities\Application\ApplicationTracking::STATUS_NOT_SET,
                Entity::FINANCIAL_EVIDENCE_UPLOAD_LATER,
                Licence::LICENCE_TYPE_SPECIAL_RESTRICTED,
                ApplicationCompletion::STATUS_VARIATION_REQUIRES_ATTENTION
            ],
        ];
    }

    /**
     * @dataProvider dataProviderTestGetApplicationOrganisationPersonsAdded
     */
    public function testGetApplicationOrganisationPersonsAdded(
        $applicationOrganisationPersonsActions,
        $expectedApplicationOrganisationPersonsActions
    ) {

        $applicationOrganisationPersons = $this->createMockApplicationOrganisationPersons(
            $applicationOrganisationPersonsActions
        );

        $expectedApplicationOrganisationPersons = $this->createMockApplicationOrganisationPersons(
            $expectedApplicationOrganisationPersonsActions
        );

        /* @var Entity $sut */
        $sut = m::mock(Entity::class)->makePartial();
        $sut->setApplicationOrganisationPersons($applicationOrganisationPersons);

        $applicationOrganisationPersonsAdded = $sut->getApplicationOrganisationPersonsAdded();

        $this->assertEquals(
            count($expectedApplicationOrganisationPersons),
            count($applicationOrganisationPersonsAdded)
        );

        /* @var \Dvsa\Olcs\Api\Entity\Application\ApplicationOrganisationPerson $applicationOrganisationPersonAdded */
        foreach ($applicationOrganisationPersonsAdded as $applicationOrganisationPersonAdded) {
            $this->assertEquals(
                'A',
                $applicationOrganisationPersonAdded->getAction()
            );
        }
    }

    public function dataProviderTestGetApplicationOrganisationPersonsAdded()
    {
        $dataProvider = [
            [
                ['A','A'],
                ['A','A']
            ],
            [
                ['A','D','U'],
                ['A']
            ],
            [
                ['D','U'],
                []
            ]
        ];

        return $dataProvider;
    }

    /**
     * @group applicationEntity
     */
    public function testGetPostSubmissionApplicationDocuments()
    {
        $mockDocument1 = m::mock()
            ->shouldReceive('getcategory')
            ->andReturn('category')
            ->once()
            ->shouldReceive('getsubCategory')
            ->andReturn('subCategory')
            ->once()
            ->shouldReceive('getoperatingCentre')
            ->andReturn('operatingCentre')
            ->once()
            ->shouldReceive('getisPostSubmissionUpload')
            ->andReturn(true)
            ->once()
            ->getMock();

        $mockDocument2 = m::mock()
            ->shouldReceive('getcategory')
            ->andReturn('category1')
            ->once()
            ->shouldReceive('getsubCategory')
            ->andReturn('subCategory1')
            ->never()
            ->shouldReceive('getoperatingCentre')
            ->andReturn('operatingCentre1')
            ->never()
            ->shouldReceive('getisPostSubmissionUpload')
            ->andReturn(false)
            ->never()
            ->getMock();

        $mockDocument3 = m::mock()
            ->shouldReceive('getcategory')
            ->andReturn('category')
            ->once()
            ->shouldReceive('getsubCategory')
            ->andReturn('subCategory')
            ->once()
            ->shouldReceive('getoperatingCentre')
            ->andReturn('operatingCentre')
            ->once()
            ->shouldReceive('getisPostSubmissionUpload')
            ->andReturn(false)
            ->once()
            ->getMock();

        $documentsCollection = new ArrayCollection([$mockDocument1, $mockDocument2, $mockDocument3]);
        $expected = new ArrayCollection([$mockDocument1]);

        $this->entity->setDocuments($documentsCollection);
        $this->assertEquals(
            $expected,
            $this->entity->getPostSubmissionApplicationDocuments('category', 'subCategory', 'operatingCentre')
        );
    }

    public function setUp(): void
    {
        $organisation = new Organisation();

        $this->licence = new Licence($organisation, new RefData(Licence::LICENCE_STATUS_NOT_SUBMITTED));
        $this->licence->setLicenceType(new RefData(Licence::LICENCE_TYPE_STANDARD_NATIONAL));

        $this->entity = $this->instantiate($this->entityClass);
        $this->entity->setLicence($this->licence);
        $this->entity->setLicenceType($this->licence->getLicenceType());
    }

    protected function setUpSut()
    {
        $this->sut = new Entity($this->licence(), new RefData(Entity::APPLICATION_STATUS_GRANTED), false);
    }

    /**
     * @return Licence
     */
    protected function licence(): Licence
    {
        return new Licence($this->organisation(), new RefData(Licence::LICENCE_STATUS_VALID));
    }

    /**
     * @return Organisation
     */
    protected function organisation(): Organisation
    {
        return new Organisation();
    }

    private function createMockApplicationOrganisationPersons($actions = [])
    {
        $applicationOrganisationPersons = [];

        foreach ($actions as $action) {
            /* @var ApplicationOrganisationPerson $aop */
            $aop = m::mock(ApplicationOrganisationPerson::class)->makePartial();
            $aop->setAction($action);
            $applicationOrganisationPersons[] = $aop;
        }

        return new ArrayCollection($applicationOrganisationPersons);
    }

    /**
     * @dataProvider dpUpdateInterimAuthVehicles
     */
    public function testUpdateInterimAuthVehicles($interimAuthHgvVehicles, $interimAuthLgvVehicles, $expected)
    {
        /** @var Entity $application */
        $application = $this->instantiate(Entity::class);
        $application->updateInterimAuthHgvVehicles($interimAuthHgvVehicles);
        $application->updateInterimAuthLgvVehicles($interimAuthLgvVehicles);

        $this->assertEquals($interimAuthHgvVehicles, $application->getInterimAuthHgvVehicles());
        $this->assertEquals($interimAuthLgvVehicles, $application->getInterimAuthLgvVehicles());
        $this->assertEquals($expected, $application->getInterimAuthVehicles());
    }

    public function dpUpdateInterimAuthVehicles()
    {
        return [
            [null, null, 0],
            [0, 0, 0],
            [1, 0, 1],
            [0, 1, 1],
            [1, 1, 2],
        ];
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
        $application = m::mock(Entity::class)->makePartial();

        $vehicleTypeRefdata = new RefData($vehicleTypeId);
        $application->setVehicleType($vehicleTypeRefdata);

        $licenceTypeRefdata = new RefData($licenceTypeId);
        $application->setLicenceType($licenceTypeRefdata);

        $application->setTotAuthLgvVehicles($totAuthLgvVehicles);

        $this->assertEquals(
            $expectedAuthProperties,
            $application->getApplicableAuthProperties()
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

        $application = m::mock(Entity::class)->makePartial();

        $vehicleTypeRefdata = new RefData('foo');
        $application->setVehicleType($vehicleTypeRefdata);

        $application->getApplicableAuthProperties();
    }

    /**
     * @dataProvider dpIsVehicleTypeMixedWithLgv
     */
    public function testIsVehicleTypeMixedWithLgv($vehicleTypeId, $totAuthLgvVehicles, $expected)
    {
        $application = $this->instantiate(Entity::class);

        $application->setVehicleType(
            new RefData($vehicleTypeId)
        );

        $application->setTotAuthLgvVehicles($totAuthLgvVehicles);

        $this->assertEquals(
            $expected,
            $application->isVehicleTypeMixedWithLgv()
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

    public function testGetOutstandingGrantFees()
    {
        $application = $this->instantiate(Entity::class);

        $this->assertEmpty($application->getOutstandingGrantFees());

        $fee = m::mock()
            ->shouldReceive('isGrantFee')
            ->andReturn(true)
            ->shouldReceive('isOutstanding')
            ->andReturn(true)
            ->shouldReceive('getId')
            ->andReturn(99)
            ->getMock();

        $application->setFees(new ArrayCollection([$fee]));

        $this->assertSame(array_key_first($application->getOutstandingGrantFees() ?? []), 99);
    }
}
