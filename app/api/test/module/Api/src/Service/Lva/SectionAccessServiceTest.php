<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Lva;

use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\Application\ApplicationCompletion;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Entity\User\Permission;
use Dvsa\Olcs\Api\Service\Lva\RestrictionService;
use Dvsa\Olcs\Api\Service\Lva\SectionAccessService;
use Laminas\ServiceManager\ServiceManager;
use LmcRbacMvc\Service\AuthorizationService;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Service\Lva\SectionAccessService::class)]
class SectionAccessServiceTest extends MockeryTestCase
{
    /**
     * Holds the sut
     *
     * @var \Dvsa\Olcs\Api\Service\Lva\SectionAccessService;
     */
    private $sut;

    /** @var m\MockInterface */
    private $mockRestrictionHelper;

    private $serviceLocator;
    /** @var  m\MockInterface */
    private $sectionConfig;
    /** @var  m\MockInterface */
    private $authService;

    public function setUp(): void
    {
        $this->mockRestrictionHelper = m::mock(RestrictionService::class);

        $this->sectionConfig = m::mock();
        $this->authService = m::mock(AuthorizationService::class);

        $sm = m::mock(ServiceManager::class);

        $sm->shouldReceive('setService')
            ->andReturnUsing(
                function ($alias, $service) use ($sm) {
                    $sm->shouldReceive('get')->with($alias)->andReturn($service);
                    $sm->shouldReceive('has')->with($alias)->andReturn(true);
                    return $sm;
                }
            );

        $this->serviceLocator = $sm;
        $this->serviceLocator->setService('RestrictionService', $this->mockRestrictionHelper);
        $this->serviceLocator->setService('SectionConfig', $this->sectionConfig);
        $this->serviceLocator->setService(AuthorizationService::class, $this->authService);

        $sut = new SectionAccessService();
        $this->sut = $sut->__invoke($this->serviceLocator, SectionAccessService::class);

        $sections = [
            'no_restriction' => [],
            'has_access' => [
                'restricted' => [
                    'access'
                ]
            ],
            'hasnt_got_access' => [
                'restricted' => [
                    'no-access'
                ]
            ]
        ];

        $this->sectionConfig->shouldReceive('getAll')
            ->andReturn($sections);
    }

    public function testGetAccessibleSectionsApplication(): void
    {
        /** @var RefData|m\MockInterface $goodsOrPsv */
        $goodsOrPsv = m::mock(RefData::class)->makePartial();
        $goodsOrPsv->setId(Licence::LICENCE_CATEGORY_GOODS_VEHICLE);

        /** @var RefData|m\MockInterface $licenceType */
        $licenceType = m::mock(RefData::class)->makePartial();
        $licenceType->setId(Licence::LICENCE_TYPE_STANDARD_NATIONAL);

        /** @var RefData|m\MockInterface $vehicleType */
        $vehicleType = m::mock(RefData::class)->makePartial();
        $vehicleType->setId(RefData::APP_VEHICLE_TYPE_HGV);

        /** @var RefData|m\MockInterface $vehicleSizes */
        $vehicleSizes = m::mock(RefData::class)->makePartial();
        $vehicleSizes->setId(Application::PSV_VEHICLE_SIZE_SMALL);

        /** @var Licence|m\MockInterface $licence */
        $licence = m::mock(Licence::class)->makePartial();
        $licence->shouldReceive('hasApprovedUnfulfilledConditions')
            ->andReturn(false);

        /** @var Application $application */
        $application = m::mock(Application::class)->makePartial();
        $application->setIsVariation(false);
        $application->setGoodsOrPsv($goodsOrPsv);
        $application->setLicenceType($licenceType);
        $application->setVehicleType($vehicleType);
        $application->setLicence($licence);
        $application->setPsvWhichVehicleSizes($vehicleSizes);
        $application->setPsvOperateSmallVhl('Y');

        $this->authService->shouldReceive('isGranted')
            ->with(Permission::INTERNAL_USER, null)
            ->andReturn(true);

        $expectedAccess = [
            'internal',
            'application',
            Licence::LICENCE_CATEGORY_GOODS_VEHICLE,
            Licence::LICENCE_TYPE_STANDARD_NATIONAL,
            RefData::APP_VEHICLE_TYPE_HGV,
            Application::PSV_VEHICLE_SIZE_SMALL,
            'isNotOperatingSmallVehiclesSmallPart',
            'noConditions'
        ];

        $this->setSharedMockRestrictionHelperExpectations($expectedAccess);

        $sections = $this->sut->getAccessibleSections($application);

        $expected = [
            'no_restriction' => [],
            'has_access' => [
                'restricted' => [
                    'access'
                ]
            ],
        ];

        $this->assertEquals($expected, $sections);
    }

    public function testGetAccessibleSectionsVariation(): void
    {
        /** @var ApplicationCompletion $appCompletion */
        $appCompletion = m::mock(ApplicationCompletion::class);

        /** @var RefData|m\MockInterface $goodsOrPsv */
        $goodsOrPsv = m::mock(RefData::class)->makePartial();
        $goodsOrPsv->setId(Licence::LICENCE_CATEGORY_GOODS_VEHICLE);

        /** @var RefData|m\MockInterface $licenceType */
        $licenceType = m::mock(RefData::class)->makePartial();
        $licenceType->setId(Licence::LICENCE_TYPE_STANDARD_NATIONAL);

        /** @var RefData|m\MockInterface $vehicleType */
        $vehicleType = m::mock(RefData::class)->makePartial();
        $vehicleType->setId(RefData::APP_VEHICLE_TYPE_HGV);

        /** @var RefData|m\MockInterface $vehicleSizes */
        $vehicleSizes = m::mock(RefData::class)->makePartial();
        $vehicleSizes->setId(Application::PSV_VEHICLE_SIZE_BOTH);

        /** @var Licence|m\MockInterface $licence */
        $licence = m::mock(Licence::class)->makePartial();
        $licence->shouldReceive('hasApprovedUnfulfilledConditions')
            ->andReturn(true);

        /** @var Application $application */
        $application = m::mock(Application::class)->makePartial();
        $application->setIsVariation(true);
        $application->setGoodsOrPsv($goodsOrPsv);
        $application->setLicenceType($licenceType);
        $application->setVehicleType($vehicleType);
        $application->setLicence($licence);
        $application->setPsvWhichVehicleSizes($vehicleSizes);
        $application->setPsvOperateSmallVhl('Y');
        $application->setApplicationCompletion($appCompletion);

        $this->authService->shouldReceive('isGranted')
            ->with(Permission::INTERNAL_USER, null)
            ->andReturn(false);

        $this->sectionConfig->shouldReceive('setVariationCompletion')
            ->with($appCompletion);

        $expectedAccess = [
            'external',
            'variation',
            Licence::LICENCE_CATEGORY_GOODS_VEHICLE,
            Licence::LICENCE_TYPE_STANDARD_NATIONAL,
            RefData::APP_VEHICLE_TYPE_HGV,
            Application::PSV_VEHICLE_SIZE_BOTH,
            'isOperatingSmallVehiclesSmallPart',
            'hasConditions'
        ];

        $this->setSharedMockRestrictionHelperExpectations($expectedAccess);

        $sections = $this->sut->getAccessibleSections($application);

        $expected = [
            'no_restriction' => [],
            'has_access' => [
                'restricted' => [
                    'access'
                ]
            ],
        ];

        $this->assertEquals($expected, $sections);
    }

    public function testGetAccessibleSectionsForLicence(): void
    {
        /** @var Licence $mockLic */
        $mockLic = m::mock(Licence::class);

        /** @var SectionAccessService|m\MockInterface $sut */
        $sut = m::mock(SectionAccessService::class . '[getAccessibleSectionsForLva]')
            ->shouldAllowMockingProtectedMethods();

        $sut->shouldReceive('getAccessibleSectionsForLva')
            ->with('licence', $mockLic, $mockLic)
            ->once()
            ->andReturn('EXPECTED')
            ->getMock();

        static::assertEquals('EXPECTED', $sut->getAccessibleSectionsForLicence($mockLic));
    }

    public function testGetAccessibleSectionsForLicenceContinuation(): void
    {
        /** @var Licence $mockLic */
        $mockLic = m::mock(Licence::class);

        /** @var SectionAccessService|m\MockInterface $sut */
        $sut = m::mock(SectionAccessService::class . '[getAccessibleSectionsForLva]')
            ->shouldAllowMockingProtectedMethods();

        $sut->shouldReceive('getAccessibleSectionsForLva')
            ->with('continuation', $mockLic, $mockLic)
            ->once()
            ->andReturn('EXPECTED')
            ->getMock();

        static::assertEquals('EXPECTED', $sut->getAccessibleSectionsForLicenceContinuation($mockLic));
    }

    /**
     * Helper method to DRY up the test
     */
    private function setSharedMockRestrictionHelperExpectations(mixed $access): void
    {
        $this->mockRestrictionHelper->shouldReceive('isRestrictionSatisfied')
            ->with(['access'], $access, 'has_access')
            ->andReturn(true);

        $this->mockRestrictionHelper->shouldReceive('isRestrictionSatisfied')
            ->with(['no-access'], $access, 'hasnt_got_access')
            ->andReturn(false);
    }
}
