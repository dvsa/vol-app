<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Service;

use Dvsa\Olcs\Api\Service\AddressHelper\AddressHelperService;
use Psr\Container\ContainerInterface;
use Mockery as m;
use Doctrine\Common\Collections\ArrayCollection;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Domain\Service\TrafficAreaValidator;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea;
use Dvsa\Olcs\Api\Entity\Licence\Licence;

class TrafficAreaValidatorTest extends MockeryTestCase
{
    protected TrafficAreaValidator $sut;

    protected m\MockInterface|AddressHelperService $addressService;

    public function setUp(): void
    {
        $this->addressService = m::mock(AddressHelperService::class);

        $container = m::mock(ContainerInterface::class);
        $container->shouldReceive('get')
            ->with(AddressHelperService::class)
            ->once()
            ->andReturn($this->addressService);

        $this->sut = new TrafficAreaValidator();
        $this->sut->__invoke($container, TrafficAreaValidator::class);
    }

    public function testCalidateForSameTrafficAreasWithPostcodeWithNullPostcode(): void
    {
        $this->addressService->shouldReceive('fetchTrafficAreaByPostcodeOrUprn')
            ->with('POSTCODE')
            ->once()
            ->andReturn(null);

        $application = m::mock(Application::class);

        $this->assertTrue($this->sut->validateForSameTrafficAreasWithPostcode($application, 'POSTCODE'));
    }

    public function testCalidateForSameTrafficAreasWithPostcode(): void
    {
        $trafficArea = m::mock(TrafficArea::class)->shouldReceive('getId')->with()->once()->andReturn('X')->getMock();

        $this->addressService->shouldReceive('fetchTrafficAreaByPostcodeOrUprn')
            ->with('POSTCODE')
            ->once()
            ->andReturn($trafficArea);

        $application = m::mock(Application::class);
        $application->shouldReceive('getLicence->getOrganisation->getLicences')->with()->once()->andReturn([]);

        $this->assertTrue($this->sut->validateForSameTrafficAreasWithPostcode($application, 'POSTCODE'));
    }

    public function testValidateErrorWithGoodsLicence(): void
    {
        $organisation = new \Dvsa\Olcs\Api\Entity\Organisation\Organisation();
        $licence = new Licence($organisation, new RefData());
        $application = new Application($licence, new RefData(), false);
        $application->setGoodsOrPsv(new RefData(Licence::LICENCE_CATEGORY_GOODS_VEHICLE));

        $licence->addApplications($application);
        $organisation->addLicences($licence);

        $lic1 = m::mock(Licence::class);
        $lic1->shouldReceive('getStatus->getId')->andReturn(Licence::LICENCE_STATUS_VALID);
        $lic1->shouldReceive('getTrafficArea->getId')->andReturn('TA');
        $lic1->shouldReceive('hasQueuedRevocation')->andReturn(false);
        $lic1->shouldReceive('getApplications')->andReturn(new ArrayCollection());
        $lic1->shouldReceive('isGoods')->andReturn(false);
        $organisation->addLicences($lic1);

        $ta = new TrafficArea();
        $ta->setId('TA')->setName('TA_NAME');
        $lic2 = m::mock(Licence::class);
        $lic2->shouldReceive('getStatus->getId')->andReturn(Licence::LICENCE_STATUS_VALID);
        $lic2->shouldReceive('getTrafficArea')->andReturn($ta);
        $lic2->shouldReceive('hasQueuedRevocation')->andReturn(false);
        $lic2->shouldReceive('getApplications')->andReturn(new ArrayCollection());
        $lic2->shouldReceive('isGoods')->andReturn(true);
        $organisation->addLicences($lic2);

        $result = $this->sut->validateForSameTrafficAreas($application, 'TA');

        $this->assertSame(['ERR_TA_GOODS' => 'TA_NAME'], $result);
    }

    public function testValidateErrorWithPsvLicence(): void
    {
        $organisation = new \Dvsa\Olcs\Api\Entity\Organisation\Organisation();
        $licence = new Licence($organisation, new RefData());
        $application = new Application($licence, new RefData(), false);
        $application->setGoodsOrPsv(new RefData(Licence::LICENCE_CATEGORY_PSV));

        $licence->addApplications($application);
        $organisation->addLicences($licence);

        $ta = new TrafficArea();
        $ta->setId('TA')->setName('TA_NAME');
        $lic2 = m::mock(Licence::class);
        $lic2->shouldReceive('getStatus->getId')->andReturn(Licence::LICENCE_STATUS_VALID);
        $lic2->shouldReceive('getTrafficArea')->andReturn($ta);
        $lic2->shouldReceive('hasQueuedRevocation')->andReturn(false);
        $lic2->shouldReceive('getApplications')->andReturn(new ArrayCollection());
        $lic2->shouldReceive('isGoods')->andReturn(false);
        $lic2->shouldReceive('isPsv')->andReturn(true);
        $lic2->shouldReceive('isSpecialRestricted')->andReturn(false);
        $lic2->shouldReceive('isRestricted')->andReturn(false);
        $organisation->addLicences($lic2);

        $result = $this->sut->validateForSameTrafficAreas($application, 'TA');

        $this->assertSame(['ERR_TA_PSV' => 'TA_NAME'], $result);
    }

    public function testValidateErrorWithPsvRestrictedLicence(): void
    {
        $organisation = new \Dvsa\Olcs\Api\Entity\Organisation\Organisation();
        $licence = new Licence($organisation, new RefData());
        $application = new Application($licence, new RefData(), false);
        $application->setGoodsOrPsv(new RefData(Licence::LICENCE_CATEGORY_PSV));
        $application->setLicenceType(new RefData(Licence::LICENCE_TYPE_RESTRICTED));
        $licence->addApplications($application);
        $organisation->addLicences($licence);

        $ta = new TrafficArea();
        $ta->setId('TA')->setName('TA_NAME');
        $lic2 = m::mock(Licence::class);
        $lic2->shouldReceive('getStatus->getId')->andReturn(Licence::LICENCE_STATUS_VALID);
        $lic2->shouldReceive('getTrafficArea')->andReturn($ta);
        $lic2->shouldReceive('hasQueuedRevocation')->andReturn(false);
        $lic2->shouldReceive('getApplications')->andReturn(new ArrayCollection());
        $lic2->shouldReceive('isGoods')->andReturn(false);
        $lic2->shouldReceive('isPsv')->andReturn(true);
        $lic2->shouldReceive('isSpecialRestricted')->andReturn(false);
        $lic2->shouldReceive('isRestricted')->andReturn(true);
        $organisation->addLicences($lic2);

        $result = $this->sut->validateForSameTrafficAreas($application, 'TA');

        $this->assertSame(['ERR_TA_PSV_RES' => 'TA_NAME'], $result);
    }

    public function testValidateErrorWithPsvSrLicence(): void
    {
        $organisation = new \Dvsa\Olcs\Api\Entity\Organisation\Organisation();
        $licence = new Licence($organisation, new RefData());
        $application = new Application($licence, new RefData(), false);
        $application->setGoodsOrPsv(new RefData(Licence::LICENCE_CATEGORY_PSV));
        $application->setLicenceType(new RefData(Licence::LICENCE_TYPE_SPECIAL_RESTRICTED));

        $licence->addApplications($application);
        $organisation->addLicences($licence);

        $ta = new TrafficArea();
        $ta->setId('TA')->setName('TA_NAME');
        $lic2 = m::mock(Licence::class);
        $lic2->shouldReceive('getStatus->getId')->andReturn(Licence::LICENCE_STATUS_VALID);
        $lic2->shouldReceive('getTrafficArea')->andReturn($ta);
        $lic2->shouldReceive('hasQueuedRevocation')->andReturn(false);
        $lic2->shouldReceive('getApplications')->andReturn(new ArrayCollection());
        $lic2->shouldReceive('isGoods')->andReturn(false);
        $lic2->shouldReceive('isPsv')->andReturn(true);
        $lic2->shouldReceive('isSpecialRestricted')->andReturn(true);
        $organisation->addLicences($lic2);

        $result = $this->sut->validateForSameTrafficAreas($application, 'TA');

        $this->assertSame(['ERR_TA_PSV_SR' => 'TA_NAME'], $result);
    }

    public function testValidateErrorWithPsvApplication(): void
    {
        $organisation = new \Dvsa\Olcs\Api\Entity\Organisation\Organisation();
        $licence = new Licence($organisation, new RefData());
        $application = new Application($licence, new RefData(), false);
        $application->setGoodsOrPsv(new RefData(Licence::LICENCE_CATEGORY_PSV));

        $licence->addApplications($application);
        $organisation->addLicences($licence);

        $trafficArea = new TrafficArea();
        $trafficArea->setId('TA');
        $trafficArea->setName('TA_NAME');

        $app1 = m::mock(Application::class);
        $app1->shouldReceive('getStatus->getId')->andReturn(Application::APPLICATION_STATUS_GRANTED);
        $app1->shouldReceive('isNew')->andReturn(true);
        $app1->shouldReceive('getTrafficArea')->andReturn($trafficArea);
        $app1->shouldReceive('isGoods')->andReturn(false);
        $app1->shouldReceive('isPsv')->andReturn(true);
        $app1->shouldReceive('isSpecialRestricted')->andReturn(false);

        $lic1 = m::mock(Licence::class);
        $lic1->shouldReceive('getStatus->getId')->andReturn(Licence::LICENCE_STATUS_REVOKED);
        $lic1->shouldReceive('getApplications')->andReturn(new ArrayCollection([$app1]));
        $organisation->addLicences($lic1);

        $result = $this->sut->validateForSameTrafficAreas($application, 'TA');

        $this->assertSame(['ERR_TA_PSV' => 'TA_NAME'], $result);
    }

    public function testValidateErrorWithPsvSrApplication(): void
    {
        $organisation = new \Dvsa\Olcs\Api\Entity\Organisation\Organisation();
        $licence = new Licence($organisation, new RefData());
        $application = new Application($licence, new RefData(), false);
        $application->setGoodsOrPsv(new RefData(Licence::LICENCE_CATEGORY_PSV));
        $application->setLicenceType(new RefData(Licence::LICENCE_TYPE_SPECIAL_RESTRICTED));

        $licence->addApplications($application);
        $organisation->addLicences($licence);

        $trafficArea = new TrafficArea();
        $trafficArea->setId('TA');
        $trafficArea->setName('TA_NAME');

        $app1 = m::mock(Application::class);
        $app1->shouldReceive('getStatus->getId')->andReturn(Application::APPLICATION_STATUS_GRANTED);
        $app1->shouldReceive('isNew')->andReturn(true);
        $app1->shouldReceive('getTrafficArea')->andReturn($trafficArea);
        $app1->shouldReceive('isGoods')->andReturn(false);
        $app1->shouldReceive('isPsv')->andReturn(true);
        $app1->shouldReceive('isSpecialRestricted')->andReturn(true);

        $lic1 = m::mock(Licence::class);
        $lic1->shouldReceive('getStatus->getId')->andReturn(Licence::LICENCE_STATUS_REVOKED);
        $lic1->shouldReceive('getApplications')->andReturn(new ArrayCollection([$app1]));
        $organisation->addLicences($lic1);

        $result = $this->sut->validateForSameTrafficAreas($application, 'TA');

        $this->assertSame(['ERR_TA_PSV_SR' => 'TA_NAME'], $result);
    }

    public function testValidateErrorWithGoodsLicenceSameLicence(): void
    {
        $organisation = new \Dvsa\Olcs\Api\Entity\Organisation\Organisation();
        $licence = new Licence($organisation, new RefData());
        $application = new Application($licence, new RefData(), false);
        $application->setGoodsOrPsv(new RefData(Licence::LICENCE_CATEGORY_GOODS_VEHICLE));

        $licence->addApplications($application);
        $organisation->addLicences($licence);

        $lic1 = m::mock(Licence::class);
        $lic1->shouldReceive('getStatus->getId')->andReturn(Licence::LICENCE_STATUS_VALID);
        $lic1->shouldReceive('getTrafficArea->getId')->andReturn('TA');
        $lic1->shouldReceive('getTrafficArea->getName')->andReturn('TA_NAME');
        $lic1->shouldReceive('hasQueuedRevocation')->andReturn(false);
        $lic1->shouldReceive('getApplications')->andReturn(new ArrayCollection());
        $lic1->shouldReceive('isGoods')->andReturn(true);
        $lic1->shouldReceive('getOrganisation')->andReturn($organisation);
        $organisation->addLicences($lic1);

        $application->setLicence($lic1);

        $result = $this->sut->validateForSameTrafficAreas($application, 'TA');

        $this->assertSame(true, $result);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dataProviderActiveLicenceStatusTypes')]
    public function testValidateErrorWithGoodsLicenceStatus(mixed $licenceStatus, mixed $expectValidationMessage): void
    {
        $organisation = new \Dvsa\Olcs\Api\Entity\Organisation\Organisation();
        $licence = new Licence($organisation, new RefData());
        $application = new Application($licence, new RefData(), false);
        $application->setGoodsOrPsv(new RefData(Licence::LICENCE_CATEGORY_GOODS_VEHICLE));

        $licence->addApplications($application);
        $organisation->addLicences($licence);

        $trafficArea = new TrafficArea();
        $trafficArea->setId('TA')->setName('TA_NAME');

        $lic1 = m::mock(Licence::class);
        $lic1->shouldReceive('getStatus->getId')->andReturn($licenceStatus);
        $lic1->shouldReceive('getTrafficArea')->andReturn($trafficArea);
        $lic1->shouldReceive('hasQueuedRevocation')->andReturn(false);
        $lic1->shouldReceive('getApplications')->andReturn(new ArrayCollection());
        $lic1->shouldReceive('isGoods')->andReturn(true);
        $lic1->shouldReceive('getOrganisation')->andReturn($organisation);
        $organisation->addLicences($lic1);

        $result = $this->sut->validateForSameTrafficAreas($application, 'TA');

        if ($expectValidationMessage) {
            $this->assertSame(['ERR_TA_GOODS' => 'TA_NAME'], $result);
        } else {
            $this->assertSame(true, $result);
        }
    }

    public function testValidateErrorWithGoodsLicenceHasQueuedRevocation(): void
    {
        $organisation = new \Dvsa\Olcs\Api\Entity\Organisation\Organisation();
        $licence = new Licence($organisation, new RefData());
        $application = new Application($licence, new RefData(), false);
        $application->setGoodsOrPsv(new RefData(Licence::LICENCE_CATEGORY_GOODS_VEHICLE));

        $licence->addApplications($application);
        $organisation->addLicences($licence);

        $lic1 = m::mock(Licence::class);
        $lic1->shouldReceive('getStatus->getId')->andReturn(Licence::LICENCE_STATUS_VALID);
        $lic1->shouldReceive('getTrafficArea->getId')->andReturn('TA');
        $lic1->shouldReceive('getTrafficArea->getName')->andReturn('TA_NAME');
        $lic1->shouldReceive('hasQueuedRevocation')->andReturn(true);
        $lic1->shouldReceive('getApplications')->andReturn(new ArrayCollection());
        $lic1->shouldReceive('isGoods')->andReturn(true);
        $lic1->shouldReceive('getOrganisation')->andReturn($organisation);
        $organisation->addLicences($lic1);

        $result = $this->sut->validateForSameTrafficAreas($application, 'TA');

        $this->assertSame(true, $result);
    }

    public function testValidateErrorWithGoodsLicenceDifferentTrafficArea(): void
    {
        $organisation = new \Dvsa\Olcs\Api\Entity\Organisation\Organisation();
        $licence = new Licence($organisation, new RefData());
        $application = new Application($licence, new RefData(), false);
        $application->setGoodsOrPsv(new RefData(Licence::LICENCE_CATEGORY_GOODS_VEHICLE));

        $licence->addApplications($application);
        $organisation->addLicences($licence);

        $lic1 = m::mock(Licence::class);
        $lic1->shouldReceive('getStatus->getId')->andReturn(Licence::LICENCE_STATUS_VALID);
        $lic1->shouldReceive('getTrafficArea->getId')->andReturn('TA');
        $lic1->shouldReceive('getTrafficArea->getName')->andReturn('TA_NAME');
        $lic1->shouldReceive('hasQueuedRevocation')->andReturn(false);
        $lic1->shouldReceive('getApplications')->andReturn(new ArrayCollection());
        $lic1->shouldReceive('isGoods')->andReturn(true);
        $lic1->shouldReceive('getOrganisation')->andReturn($organisation);
        $organisation->addLicences($lic1);

        $result = $this->sut->validateForSameTrafficAreas($application, 'TA2');

        $this->assertSame(true, $result);
    }

    public function testValidateErrorWithGoodsApplication(): void
    {
        $organisation = new \Dvsa\Olcs\Api\Entity\Organisation\Organisation();
        $licence = new Licence($organisation, new RefData());
        $application = new Application($licence, new RefData(), false);
        $application->setGoodsOrPsv(new RefData(Licence::LICENCE_CATEGORY_GOODS_VEHICLE));

        $licence->addApplications($application);
        $organisation->addLicences($licence);

        $trafficArea = new TrafficArea();
        $trafficArea->setId('TA');
        $trafficArea->setName('TA_NAME');

        $app1 = m::mock(Application::class);
        $app1->shouldReceive('getStatus->getId')->andReturn(Application::APPLICATION_STATUS_GRANTED);
        $app1->shouldReceive('isNew')->andReturn(true);
        $app1->shouldReceive('getTrafficArea')->andReturn($trafficArea);
        $app1->shouldReceive('isGoods')->andReturn(false);

        $app2 = m::mock(Application::class);
        $app2->shouldReceive('getStatus->getId')->andReturn(Application::APPLICATION_STATUS_GRANTED);
        $app2->shouldReceive('isNew')->andReturn(true);
        $app2->shouldReceive('getTrafficArea')->andReturn($trafficArea);
        $app2->shouldReceive('isGoods')->andReturn(true);

        $lic1 = m::mock(Licence::class);
        $lic1->shouldReceive('getStatus->getId')->andReturn(Licence::LICENCE_STATUS_REVOKED);
        $lic1->shouldReceive('getApplications')->andReturn(new ArrayCollection([$app1, $app2]));
        $organisation->addLicences($lic1);

        $result = $this->sut->validateForSameTrafficAreas($application, 'TA');

        $this->assertSame(['ERR_TA_GOODS' => 'TA_NAME'], $result);
    }

    public function testValidateErrorWithGoodsApplicationSameApp(): void
    {
        $organisation = new \Dvsa\Olcs\Api\Entity\Organisation\Organisation();
        $licence = new Licence($organisation, new RefData());
        $application = new Application($licence, new RefData(), false);
        $application->setGoodsOrPsv(new RefData(Licence::LICENCE_CATEGORY_GOODS_VEHICLE));

        $licence->addApplications($application);
        $organisation->addLicences($licence);

        $trafficArea = new TrafficArea();
        $trafficArea->setId('TA');
        $trafficArea->setName('TA_NAME');

        $app1 = m::mock(Application::class);
        $app1->shouldReceive('getStatus->getId')->andReturn(Application::APPLICATION_STATUS_GRANTED);
        $app1->shouldReceive('isNew')->andReturn(true);
        $app1->shouldReceive('getTrafficArea')->andReturn($trafficArea);
        $app1->shouldReceive('isGoods')->andReturn(true);
        $app1->shouldReceive('getLicence')->andReturn($licence);

        $lic1 = m::mock(Licence::class);
        $lic1->shouldReceive('getStatus->getId')->andReturn(Licence::LICENCE_STATUS_REVOKED);
        $lic1->shouldReceive('getApplications')->andReturn(new ArrayCollection([$app1]));
        $organisation->addLicences($lic1);

        $result = $this->sut->validateForSameTrafficAreas($app1, 'TA');

        $this->assertSame(true, $result);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dataProviderActiveApplicationStatusTypes')]
    public function testValidateErrorWithGoodsApplicationStatus(mixed $status, mixed $expectValidationMessage): void
    {
        $organisation = new \Dvsa\Olcs\Api\Entity\Organisation\Organisation();
        $licence = new Licence($organisation, new RefData());
        $application = new Application($licence, new RefData(), false);
        $application->setGoodsOrPsv(new RefData(Licence::LICENCE_CATEGORY_GOODS_VEHICLE));

        $licence->addApplications($application);
        $organisation->addLicences($licence);

        $trafficArea = new TrafficArea();
        $trafficArea->setId('TA');
        $trafficArea->setName('TA_NAME');

        $app1 = m::mock(Application::class);
        $app1->shouldReceive('getStatus->getId')->andReturn($status);
        $app1->shouldReceive('isNew')->andReturn(true);
        $app1->shouldReceive('getTrafficArea')->andReturn($trafficArea);
        $app1->shouldReceive('isGoods')->andReturn(true);

        $lic1 = m::mock(Licence::class);
        $lic1->shouldReceive('getStatus->getId')->andReturn(Licence::LICENCE_STATUS_REVOKED);
        $lic1->shouldReceive('getApplications')->andReturn(new ArrayCollection([$app1]));
        $organisation->addLicences($lic1);
        $organisation->addLicences($licence);

        $result = $this->sut->validateForSameTrafficAreas($application, 'TA');

        if ($expectValidationMessage) {
            $this->assertSame(['ERR_TA_GOODS' => 'TA_NAME'], $result);
        } else {
            $this->assertSame(true, $result);
        }
    }

    public static function dataProviderActiveLicenceStatusTypes(): array
    {
        return [
            [Licence::LICENCE_STATUS_VALID, true],
            [Licence::LICENCE_STATUS_SUSPENDED, true],
            [Licence::LICENCE_STATUS_CURTAILED, true],
            [Licence::LICENCE_STATUS_CANCELLED, false],
            [Licence::LICENCE_STATUS_CONTINUATION_NOT_SOUGHT, false],
            [Licence::LICENCE_STATUS_GRANTED, false],
            [Licence::LICENCE_STATUS_NOT_SUBMITTED, false],
            [Licence::LICENCE_STATUS_REFUSED, false],
            [Licence::LICENCE_STATUS_REVOKED, false],
            [Licence::LICENCE_STATUS_SURRENDERED, false],
            [Licence::LICENCE_STATUS_TERMINATED, false],
            [Licence::LICENCE_STATUS_UNDER_CONSIDERATION, false],
            [Licence::LICENCE_STATUS_UNLICENSED, false],
            [Licence::LICENCE_STATUS_WITHDRAWN, false],
        ];
    }

    public static function dataProviderActiveApplicationStatusTypes(): array
    {
        return [
            [Application::APPLICATION_STATUS_NOT_SUBMITTED, true],
            [Application::APPLICATION_STATUS_UNDER_CONSIDERATION, true],
            [Application::APPLICATION_STATUS_GRANTED, true],
            [Application::APPLICATION_STATUS_CANCELLED, false],
            [Application::APPLICATION_STATUS_CURTAILED, false],
            [Application::APPLICATION_STATUS_NOT_TAKEN_UP, false],
            [Application::APPLICATION_STATUS_REFUSED, false],
            [Application::APPLICATION_STATUS_VALID, false],
            [Application::APPLICATION_STATUS_WITHDRAWN, false],
        ];
    }

    public function testValidateTrafficAreaWithPostcodeWithEmptyPostcode(): void
    {
        $postcode = '';

        $app = m::mock(Application::class);

        $exception = false;
        try {
            $this->sut->validateTrafficAreaWithPostcode($app, $postcode);
        } catch (\Exception) {
            $exception = true;
        }

        $this->assertFalse($exception);
    }

    public function testValidateTrafficAreaWithPostcodeWithAddressServiceFailure(): void
    {
        $postcode = 'AB1 2CD';

        $this->addressService->shouldReceive('fetchTrafficAreaByPostcodeOrUprn')
            ->with($postcode)
            ->once()
            ->andThrow(new \Exception());

        $app = m::mock(Application::class);

        $exception = false;
        try {
            $this->sut->validateTrafficAreaWithPostcode($app, $postcode);
        } catch (\Exception) {
            $exception = true;
        }

        $this->assertFalse($exception);
    }

    public function testValidateTrafficAreaWithPostcodeWithNoTaMatched(): void
    {
        $postcode = 'AB1 2CD';

        $this->addressService->shouldReceive('fetchTrafficAreaByPostcodeOrUprn')
            ->with($postcode)
            ->once()
            ->andReturn(null);

        $app = m::mock(Application::class);

        $exception = false;
        try {
            $this->sut->validateTrafficAreaWithPostcode($app, $postcode);
        } catch (\Exception) {
            $exception = true;
        }

        $this->assertFalse($exception);
    }

    public function testValidateTrafficAreaWithPostcodeForGbAppAndNiTa(): void
    {
        $postcode = 'AB1 2CD';

        $trafficArea = new TrafficArea();
        $trafficArea->setId(TrafficArea::NORTHERN_IRELAND_TRAFFIC_AREA_CODE);

        $this->addressService->shouldReceive('fetchTrafficAreaByPostcodeOrUprn')
            ->with($postcode)
            ->once()
            ->andReturn($trafficArea);

        $app = m::mock(Application::class);
        $app->shouldReceive('isNew')
            ->withNoArgs()
            ->once()
            ->andReturnTrue()
            ->shouldReceive('getNiFlag')
            ->withNoArgs()
            ->once()
            ->andReturn('N');

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage(TrafficAreaValidator::ERR_TA_NI_APP);

        $this->sut->validateTrafficAreaWithPostcode($app, $postcode);
    }

    public function testValidateTrafficAreaWithPostcodeForNiAppAndNiTa(): void
    {
        $postcode = 'AB1 2CD';

        $trafficArea = new TrafficArea();
        $trafficArea->setId(TrafficArea::NORTHERN_IRELAND_TRAFFIC_AREA_CODE);

        $this->addressService->shouldReceive('fetchTrafficAreaByPostcodeOrUprn')
            ->with($postcode)
            ->once()
            ->andReturn($trafficArea);

        $app = m::mock(Application::class);
        $app->shouldReceive('isNew')
            ->withNoArgs()
            ->once()
            ->andReturnTrue()
            ->shouldReceive('getNiFlag')
            ->withNoArgs()
            ->once()
            ->andReturn('Y');

        $exception = false;
        try {
            $this->sut->validateTrafficAreaWithPostcode($app, $postcode);
        } catch (\Exception) {
            $exception = true;
        }

        $this->assertFalse($exception);
    }
}
