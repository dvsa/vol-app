<?php

declare(strict_types=1);

/**
 * Financial Standing Helper Service Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\OlcsTest\Api\Service;

use Dvsa\Olcs\Api\Entity;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Api\Entity\System\FinancialStandingRate;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Service\FinancialStandingHelperService;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Financial Standing Helper Service Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class FinancialStandingHelperServiceTest extends MockeryTestCase
{
    /**
     * @var FinancialStandingHelperService
     */
    protected $sut;

    private $repoMap = [];

    public function setUp(): void
    {
        $this->repoMap['FinancialStandingRate'] = m::mock();
        $this->repoMap['Organisation'] = m::mock();
        $this->repoMap['Application'] = m::mock();

        $this->repoMap['FinancialStandingRate']
            ->shouldReceive('fetchRatesInEffect')
            ->andReturnUsing($this->getStubRates(...));

        // Create service with mocked dependencies
        $this->sut = $this->createService();

        parent::setUp();
    }

    private function createService(): mixed
    {
        $mockRepoServiceManager = m::mock()
            ->shouldReceive('get')->with('FinancialStandingRate')->once()
                ->andReturn($this->repoMap['FinancialStandingRate'])
            ->shouldReceive('get')->with('Organisation')->once()->andReturn($this->repoMap['Organisation'])
            ->shouldReceive('get')->with('Application')->once()->andReturn($this->repoMap['Application'])
            ->getMock();

        $sm = m::mock(ContainerInterface::class)
            ->shouldReceive('get')
            ->with('RepositoryServiceManager')
            ->andReturn($mockRepoServiceManager)
            ->getMock();

        $sut = new FinancialStandingHelperService();
        return $sut->__invoke($sm, FinancialStandingHelperService::class);
    }

    /**
     * @param array $auths
     * @param int $expected
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('financeCalculationProvider')]
    public function testGetFinanceCalculation(mixed $auths, mixed $expected): void
    {
        $this->assertEquals($expected, $this->sut->getFinanceCalculation($auths));
    }

    public static function financeCalculationProvider(): array
    {
        // For an operator:
        //  * with a goods standard international application with 3 vehicles,
        //    the finance is £7000 + (2 x £3900) = £14,800
        //  * plus a goods restricted licence with 3 vehicles, the finance is (3 x £1700) = £5,100
        //  * plus a psv restricted licence with 1 vehicle, the finance is £2,700
        //  * plus another goods app with 2 vehicles (2 x 3900) = £7,800
        //  * The total required finance is £14,800 + £5,100 + £2,700 + £7,800 = £30,400
        return [
            'list contains entry that is psv, either si or sn, and vehicle authorisation > 0' => [
                'auths' => [
                    0 => [
                      'category' => Licence::LICENCE_CATEGORY_GOODS_VEHICLE,
                      'type' => Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                      'count' => 3,
                      'hgvCount' => 3,
                      'lgvCount' => 0,
                    ],
                    1 => [
                      'category' => Licence::LICENCE_CATEGORY_GOODS_VEHICLE,
                      'type' => Licence::LICENCE_TYPE_RESTRICTED,
                      'count' => 3,
                      'hgvCount' => 3,
                      'lgvCount' => 0,
                    ],
                    2 => [
                      'category' => Licence::LICENCE_CATEGORY_PSV,
                      'type' => Licence::LICENCE_TYPE_RESTRICTED,
                      'count' => 1,
                      'hgvCount' => 1,
                      'lgvCount' => 0,
                    ],
                    3 => [
                      'category' => Licence::LICENCE_CATEGORY_GOODS_VEHICLE,
                      'type' => Licence::LICENCE_TYPE_STANDARD_NATIONAL,
                      'count' => 2,
                      'hgvCount' => 2,
                      'lgvCount' => 0,
                    ],
                ],
                'expected' => 30400,
            ],
            'list contains entry that is goods, either si or sn, and vehicle/hgv authorisation > 0' => [
                'auths' => [
                    0 => [
                      'category' => Licence::LICENCE_CATEGORY_GOODS_VEHICLE,
                      'type' => Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                      'count' => 5,
                      'hgvCount' => 2,
                      'lgvCount' => 3,
                    ],
                    1 => [
                      'category' => Licence::LICENCE_CATEGORY_GOODS_VEHICLE,
                      'type' => Licence::LICENCE_TYPE_RESTRICTED,
                      'count' => 3,
                      'hgvCount' => 3,
                      'lgvCount' => 0,
                    ],
                    2 => [
                      'category' => Licence::LICENCE_CATEGORY_GOODS_VEHICLE,
                      'type' => Licence::LICENCE_TYPE_STANDARD_NATIONAL,
                      'count' => 4,
                      'hgvCount' => 4,
                      'lgvCount' => 0,
                    ],
                ],
                'expected' => 34000,
            ],
            'list contains entry that is restricted and lgv authorisation > 0' => [
                'auths' => [
                    0 => [
                      'category' => Licence::LICENCE_CATEGORY_GOODS_VEHICLE,
                      'type' => Licence::LICENCE_TYPE_RESTRICTED,
                      'count' => 2,
                      'hgvCount' => 2,
                      'lgvCount' => 0,
                    ],
                    1 => [
                      'category' => Licence::LICENCE_CATEGORY_PSV,
                      'type' => Licence::LICENCE_TYPE_RESTRICTED,
                      'count' => 3,
                      'hgvCount' => 3,
                      'lgvCount' => 0,
                    ],
                    2 => [
                      'category' => Licence::LICENCE_CATEGORY_GOODS_VEHICLE,
                      'type' => Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                      'count' => 2,
                      'hgvCount' => 0,
                      'lgvCount' => 2,
                    ],
                ],
                'expected' => 14500,
            ],
            'list contains entry that is goods, si, and lgv authorisation > 0' => [
                'auths' => [
                    0 => [
                      'category' => Licence::LICENCE_CATEGORY_GOODS_VEHICLE,
                      'type' => Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                      'count' => 2,
                      'hgvCount' => 0,
                      'lgvCount' => 2,
                    ],
                    1 => [
                      'category' => Licence::LICENCE_CATEGORY_GOODS_VEHICLE,
                      'type' => Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                      'count' => 5,
                      'hgvCount' => 0,
                      'lgvCount' => 5,
                    ],
                ],
                'expected' => 6400,
            ],
            'list contains goods/sn and goods/restricted (bugfix)' => [
                'auths' => [
                    0 => [
                      'category' => Licence::LICENCE_CATEGORY_GOODS_VEHICLE,
                      'type' => Licence::LICENCE_TYPE_STANDARD_NATIONAL,
                      'count' => 14,
                      'hgvCount' => 14,
                      'lgvCount' => 0,
                    ],
                    1 => [
                      'category' => Licence::LICENCE_CATEGORY_GOODS_VEHICLE,
                      'type' => Licence::LICENCE_TYPE_RESTRICTED,
                      'count' => 12,
                      'hgvCount' => 12,
                      'lgvCount' => 0,
                    ],
                ],
                'expected' => 78100,
            ],
            'no auths' => [
                'auths' => [],
                'expected' => 0,
            ],
        ];
    }

    public function testGetRatesForView(): void
    {
        $expected = [
            'restrictedHeavyGoodsFirst' => 3100.0,
            'restrictedHeavyGoodsAdditional' => 1700.0,
            'restrictedPassengerServiceFirst' => 4100.0,
            'restrictedPassengerServiceAdditional' => 2700.0,
            'standardNationalHeavyGoodsFirst' => 7000.0,
            'standardNationalHeavyGoodsAdditional' => 3900.0,
            'standardNationalPassengerServiceFirst' => 8000.0,
            'standardNationalPassengerServiceAdditional' => 4900.0,
            'standardInternationalHeavyGoodsFirst' => 7000.0,
            'standardInternationalHeavyGoodsAdditional' => 3900.0,
            'standardInternationalLightGoodsFirst' => 1600.0,
            'standardInternationalLightGoodsAdditional' => 800.0,
            'standardInternationalPassengerServiceFirst' => 8000.0,
            'standardInternationalPassengerServiceAdditional' => 4900.0,
        ];

        $this->assertEquals(
            $expected,
            $this->sut->getRatesForView()
        );
    }

    public function testGetAdditionalVehicleRateNull(): void
    {
        $mockRatesRepo = m::mock()
            ->shouldReceive('fetchRatesInEffect')
            ->andReturn([])
            ->getMock();

        $this->assertNull(
            $this->createService()->getAdditionalVehicleRate(null, null, null)
        );
    }

    public function testGetFirstVehicleRateNull(): void
    {
        $mockRatesRepo = m::mock()
            ->shouldReceive('fetchRatesInEffect')
            ->andReturn([])
            ->getMock();

        $this->assertNull(
            $this->createService()->getFirstVehicleRate(null, null, null)
        );
    }

    public function getStubRates(): array
    {
        return [
            $this->getStubRate(
                7000,
                3900,
                Licence::LICENCE_CATEGORY_GOODS_VEHICLE,
                Licence::LICENCE_TYPE_STANDARD_NATIONAL,
                FinancialStandingRate::VEHICLE_TYPE_NOT_APPLICABLE
            ),
            $this->getStubRate(
                7000,
                3900,
                Licence::LICENCE_CATEGORY_GOODS_VEHICLE,
                Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                FinancialStandingRate::VEHICLE_TYPE_HGV
            ),
            $this->getStubRate(
                1600,
                800,
                Licence::LICENCE_CATEGORY_GOODS_VEHICLE,
                Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                FinancialStandingRate::VEHICLE_TYPE_LGV
            ),
            $this->getStubRate(
                3100,
                1700,
                Licence::LICENCE_CATEGORY_GOODS_VEHICLE,
                Licence::LICENCE_TYPE_RESTRICTED,
                FinancialStandingRate::VEHICLE_TYPE_NOT_APPLICABLE
            ),
            $this->getStubRate(
                8000,
                4900,
                Licence::LICENCE_CATEGORY_PSV,
                Licence::LICENCE_TYPE_STANDARD_NATIONAL,
                FinancialStandingRate::VEHICLE_TYPE_NOT_APPLICABLE
            ),
            $this->getStubRate(
                8000,
                4900,
                Licence::LICENCE_CATEGORY_PSV,
                Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                FinancialStandingRate::VEHICLE_TYPE_NOT_APPLICABLE
            ),
            $this->getStubRate(
                4100,
                2700,
                Licence::LICENCE_CATEGORY_PSV,
                Licence::LICENCE_TYPE_RESTRICTED,
                FinancialStandingRate::VEHICLE_TYPE_NOT_APPLICABLE
            ),
        ];
    }

    protected function getStubRate(mixed $firstVehicleRate, mixed $additionalVehicleRate, mixed $goodsOrPsv, mixed $licenceType, mixed $vehicleType): mixed
    {
        $rate = new FinancialStandingRate();
        $goodsOrPsvChild = new RefData();
        $goodsOrPsvChild->setId($goodsOrPsv);
        $licenceTypeChild = new RefData();
        $licenceTypeChild->setId($licenceType);
        $vehicleTypeChild = new RefData();
        $vehicleTypeChild->setId($vehicleType);

        $rate
            ->setFirstVehicleRate($firstVehicleRate)
            ->setAdditionalVehicleRate($additionalVehicleRate)
            ->setGoodsOrPsv($goodsOrPsvChild)
            ->setLicenceType($licenceTypeChild)
            ->setVehicleType($vehicleTypeChild);

        return $rate;
    }

    public function testGetFinanceCalculationForOrganisation(): void
    {
        $organisationId = 69;

        $application1 = m::mock(Entity\Application\Application::class)->makePartial()->setId(1);
        $application1->shouldReceive('getGoodsOrPsv->getId')->andReturn(Licence::LICENCE_CATEGORY_GOODS_VEHICLE);
        $application1->shouldReceive('getLicenceType->getId')->andReturn(Licence::LICENCE_TYPE_STANDARD_NATIONAL);
        $application1->shouldReceive('getTotAuthVehicles')->andReturn(4);
        $application1->shouldReceive('getTotAuthHgvVehiclesZeroCoalesced')->andReturn(4);
        $application1->shouldReceive('getTotAuthLgvVehiclesZeroCoalesced')->andReturn(0);

        $application2 = m::mock(Entity\Application\Application::class)->makePartial()->setId(2);
        $application2->shouldReceive('getGoodsOrPsv->getId')->andReturn(Licence::LICENCE_CATEGORY_GOODS_VEHICLE);
        $application2->shouldReceive('getLicenceType->getId')->andReturn(Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL);
        $application2->shouldReceive('getTotAuthVehicles')->andReturn(5);
        $application2->shouldReceive('getTotAuthHgvVehiclesZeroCoalesced')->andReturn(5);
        $application2->shouldReceive('getTotAuthLgvVehiclesZeroCoalesced')->andReturn(0);

        $application3 = m::mock(Entity\Application\Application::class)->makePartial()->setId(2)->setIsVariation(true);
        $application3->shouldReceive('getGoodsOrPsv->getId')->andReturn(Licence::LICENCE_CATEGORY_GOODS_VEHICLE);
        $application3->shouldReceive('getLicenceType->getId')->andReturn(Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL);
        $application3->shouldReceive('getTotAuthVehicles')->andReturn(5);
        $application3->shouldReceive('getTotAuthHgvVehiclesZeroCoalesced')->andReturn(5);
        $application3->shouldReceive('getTotAuthLgvVehiclesZeroCoalesced')->andReturn(0);

        $licence1 = m::mock(Entity\Licence\Licence::class)->makePartial()->setId(1);
        $licence1->shouldReceive('getGoodsOrPsv->getId')->andReturn(Licence::LICENCE_CATEGORY_PSV);
        $licence1->shouldReceive('getLicenceType->getId')->andReturn(Licence::LICENCE_TYPE_STANDARD_NATIONAL);
        $licence1->shouldReceive('getTotAuthVehicles')->andReturn(6);
        $licence1->shouldReceive('getTotAuthHgvVehiclesZeroCoalesced')->andReturn(6);
        $licence1->shouldReceive('getTotAuthLgvVehiclesZeroCoalesced')->andReturn(0);

        $licence2 = m::mock(Entity\Licence\Licence::class)->makePartial()->setId(2);
        $licence2->shouldReceive('getGoodsOrPsv->getId')->andReturn(Licence::LICENCE_CATEGORY_PSV);
        $licence2->shouldReceive('getLicenceType->getId')->andReturn(Licence::LICENCE_TYPE_RESTRICTED);
        $licence2->shouldReceive('getTotAuthVehicles')->andReturn(7);
        $licence2->shouldReceive('getTotAuthHgvVehiclesZeroCoalesced')->andReturn(7);
        $licence2->shouldReceive('getTotAuthLgvVehiclesZeroCoalesced')->andReturn(0);

        $this->repoMap['Application']
            ->shouldReceive('fetchActiveForOrganisation')
            ->with(69)
            ->andReturn([$application1, $application2, $application3]);

        $organisation = m::mock(Entity\Organisation\Organisation::class)->makePartial()->setId($organisationId);
        $organisation->shouldReceive('getActiveLicences')->with()->once()->andReturn([$licence1, $licence2]);
        $this->repoMap['Organisation']
            ->shouldReceive('fetchById')->with($organisationId)->once()->andReturn($organisation);

        $this->assertEquals(86500, $this->sut->getFinanceCalculationForOrganisation($organisationId));
    }

    public function testGetRequiredFinanceWhenApplicationIncluded(): void
    {
        $applicationType = Licence::LICENCE_TYPE_RESTRICTED;
        $applicationTotAuthVehicles = 9;
        $applicationTotAuthHgvVehicles = 9;
        $applicationTotAuthLgvVehicles = 0;
        $applicationCategory = Licence::LICENCE_CATEGORY_GOODS_VEHICLE;

        $licence1Type = Licence::LICENCE_TYPE_STANDARD_NATIONAL;
        $licence1TotAuthVehicles = 6;
        $licence1TotAuthHgvVehicles = 6;
        $licence1TotAuthLgvVehicles = 0;
        $licence1Category = Licence::LICENCE_CATEGORY_GOODS_VEHICLE;

        $licence2Type = Licence::LICENCE_TYPE_RESTRICTED;
        $licence2TotAuthVehicles = 5;
        $licence2TotAuthHgvVehicles = 5;
        $licence2TotAuthLgvVehicles = 0;
        $licence2Category = Licence::LICENCE_CATEGORY_PSV;

        $otherNewApplication1Type = Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL;
        $otherNewApplication1TotAuthVehicles = 10;
        $otherNewApplication1TotAuthHgvVehicles = 7;
        $otherNewApplication1TotAuthLgvVehicles = 3;
        $otherNewApplication1Category = Licence::LICENCE_CATEGORY_GOODS_VEHICLE;

        $otherNewApplication2Type = Licence::LICENCE_TYPE_STANDARD_NATIONAL;
        $otherNewApplication2TotAuthVehicles = 2;
        $otherNewApplication2TotAuthHgvVehicles = 2;
        $otherNewApplication2TotAuthLgvVehicles = 0;
        $otherNewApplication2Category = Licence::LICENCE_CATEGORY_PSV;

        $financeCalculation = 50150;

        $licence1 = $this->createMockApplicationOrLicence(
            Licence::class,
            $licence1Type,
            $licence1TotAuthVehicles,
            $licence1TotAuthHgvVehicles,
            $licence1TotAuthLgvVehicles,
            $licence1Category
        );

        $licence2 = $this->createMockApplicationOrLicence(
            Licence::class,
            $licence2Type,
            $licence2TotAuthVehicles,
            $licence2TotAuthHgvVehicles,
            $licence2TotAuthLgvVehicles,
            $licence2Category
        );

        $otherActiveLicencesForOrganisation = [$licence1, $licence2];

        $application = $this->createMockApplicationOrLicence(
            Application::class,
            $applicationType,
            $applicationTotAuthVehicles,
            $applicationTotAuthHgvVehicles,
            $applicationTotAuthLgvVehicles,
            $applicationCategory
        );

        $application->shouldReceive('getOtherActiveLicencesForOrganisation')
            ->withNoArgs()
            ->andReturn($otherActiveLicencesForOrganisation);

        $otherNewApplication1 = $this->createMockApplicationOrLicence(
            Application::class,
            $otherNewApplication1Type,
            $otherNewApplication1TotAuthVehicles,
            $otherNewApplication1TotAuthHgvVehicles,
            $otherNewApplication1TotAuthLgvVehicles,
            $otherNewApplication1Category
        );

        $otherNewApplication2 = $this->createMockApplicationOrLicence(
            Application::class,
            $otherNewApplication2Type,
            $otherNewApplication2TotAuthVehicles,
            $otherNewApplication2TotAuthHgvVehicles,
            $otherNewApplication2TotAuthLgvVehicles,
            $otherNewApplication2Category
        );

        $otherNewApplications = [$otherNewApplication1, $otherNewApplication2];
        $expectedAuths = [
            [
                'type' => $applicationType,
                'count' => $applicationTotAuthVehicles,
                'hgvCount' => $applicationTotAuthHgvVehicles,
                'lgvCount' => $applicationTotAuthLgvVehicles,
                'category' => $applicationCategory,
            ],
            [
                'type' => $licence1Type,
                'count' => $licence1TotAuthVehicles,
                'hgvCount' => $licence1TotAuthHgvVehicles,
                'lgvCount' => $licence1TotAuthLgvVehicles,
                'category' => $licence1Category,
            ],
            [
                'type' => $licence2Type,
                'count' => $licence2TotAuthVehicles,
                'hgvCount' => $licence2TotAuthHgvVehicles,
                'lgvCount' => $licence2TotAuthLgvVehicles,
                'category' => $licence2Category,
            ],
            [
                'type' => $otherNewApplication1Type,
                'count' => $otherNewApplication1TotAuthVehicles,
                'hgvCount' => $otherNewApplication1TotAuthHgvVehicles,
                'lgvCount' => $otherNewApplication1TotAuthLgvVehicles,
                'category' => $otherNewApplication1Category,
            ],
            [
                'type' => $otherNewApplication2Type,
                'count' => $otherNewApplication2TotAuthVehicles,
                'hgvCount' => $otherNewApplication2TotAuthHgvVehicles,
                'lgvCount' => $otherNewApplication2TotAuthLgvVehicles,
                'category' => $otherNewApplication2Category,
            ],
        ];

        $financialStandingHelperService = m::mock(FinancialStandingHelperService::class)->makePartial();
        $financialStandingHelperService->shouldReceive('getOtherNewApplications')
            ->with($application)
            ->andReturn($otherNewApplications);
        $financialStandingHelperService->shouldReceive('getFinanceCalculation')
            ->with($expectedAuths)
            ->andReturn($financeCalculation);

        $this->assertEquals(
            $financeCalculation,
            $financialStandingHelperService->getRequiredFinance($application)
        );
    }

    public function testGetRequiredFinancewhenApplicationNotIncluded(): void
    {
        $licence1Type = Licence::LICENCE_TYPE_STANDARD_NATIONAL;
        $licence1TotAuthVehicles = 6;
        $licence1TotAuthHgvVehicles = 6;
        $licence1TotAuthLgvVehicles = 0;
        $licence1Category = Licence::LICENCE_CATEGORY_GOODS_VEHICLE;

        $licence2Type = Licence::LICENCE_TYPE_RESTRICTED;
        $licence2TotAuthVehicles = 5;
        $licence2TotAuthHgvVehicles = 5;
        $licence2TotAuthLgvVehicles = 0;
        $licence2Category = Licence::LICENCE_CATEGORY_PSV;

        $otherNewApplication1Type = Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL;
        $otherNewApplication1TotAuthVehicles = 10;
        $otherNewApplication1TotAuthHgvVehicles = 7;
        $otherNewApplication1TotAuthLgvVehicles = 3;
        $otherNewApplication1Category = Licence::LICENCE_CATEGORY_GOODS_VEHICLE;

        $otherNewApplication2Type = Licence::LICENCE_TYPE_STANDARD_NATIONAL;
        $otherNewApplication2TotAuthVehicles = 2;
        $otherNewApplication2TotAuthHgvVehicles = 2;
        $otherNewApplication2TotAuthLgvVehicles = 0;
        $otherNewApplication2Category = Licence::LICENCE_CATEGORY_PSV;

        $financeCalculation = 50150;

        $licence1 = $this->createMockApplicationOrLicence(
            Licence::class,
            $licence1Type,
            $licence1TotAuthVehicles,
            $licence1TotAuthHgvVehicles,
            $licence1TotAuthLgvVehicles,
            $licence1Category
        );

        $licence2 = $this->createMockApplicationOrLicence(
            Licence::class,
            $licence2Type,
            $licence2TotAuthVehicles,
            $licence2TotAuthHgvVehicles,
            $licence2TotAuthLgvVehicles,
            $licence2Category
        );

        $activeLicencesForOrganisation = [$licence1, $licence2];

        $application = $this->createMockApplicationOrLicence(
            Application::class,
            Licence::LICENCE_TYPE_RESTRICTED,
            9,
            9,
            0,
            Licence::LICENCE_CATEGORY_GOODS_VEHICLE
        );

        $application->shouldReceive('getActiveLicencesForOrganisation')
            ->withNoArgs()
            ->andReturn($activeLicencesForOrganisation);

        $otherNewApplication1 = $this->createMockApplicationOrLicence(
            Application::class,
            $otherNewApplication1Type,
            $otherNewApplication1TotAuthVehicles,
            $otherNewApplication1TotAuthHgvVehicles,
            $otherNewApplication1TotAuthLgvVehicles,
            $otherNewApplication1Category
        );

        $otherNewApplication2 = $this->createMockApplicationOrLicence(
            Application::class,
            $otherNewApplication2Type,
            $otherNewApplication2TotAuthVehicles,
            $otherNewApplication2TotAuthHgvVehicles,
            $otherNewApplication2TotAuthLgvVehicles,
            $otherNewApplication2Category
        );

        $otherNewApplications = [$otherNewApplication1, $otherNewApplication2];
        $expectedAuths = [
            [
                'type' => $licence1Type,
                'count' => $licence1TotAuthVehicles,
                'hgvCount' => $licence1TotAuthHgvVehicles,
                'lgvCount' => $licence1TotAuthLgvVehicles,
                'category' => $licence1Category,
            ],
            [
                'type' => $licence2Type,
                'count' => $licence2TotAuthVehicles,
                'hgvCount' => $licence2TotAuthHgvVehicles,
                'lgvCount' => $licence2TotAuthLgvVehicles,
                'category' => $licence2Category,
            ],
            [
                'type' => $otherNewApplication1Type,
                'count' => $otherNewApplication1TotAuthVehicles,
                'hgvCount' => $otherNewApplication1TotAuthHgvVehicles,
                'lgvCount' => $otherNewApplication1TotAuthLgvVehicles,
                'category' => $otherNewApplication1Category,
            ],
            [
                'type' => $otherNewApplication2Type,
                'count' => $otherNewApplication2TotAuthVehicles,
                'hgvCount' => $otherNewApplication2TotAuthHgvVehicles,
                'lgvCount' => $otherNewApplication2TotAuthLgvVehicles,
                'category' => $otherNewApplication2Category,
            ],
        ];

        $financialStandingHelperService = m::mock(FinancialStandingHelperService::class)->makePartial();
        $financialStandingHelperService->shouldReceive('getOtherNewApplications')
            ->with($application)
            ->andReturn($otherNewApplications);
        $financialStandingHelperService->shouldReceive('getFinanceCalculation')
            ->with($expectedAuths)
            ->andReturn($financeCalculation);

        $this->assertEquals(
            $financeCalculation,
            $financialStandingHelperService->getRequiredFinance($application, false)
        );
    }

    private function createMockApplicationOrLicence(mixed $class, mixed $type, mixed $totAuthVehicles, mixed $totAuthHgvVehicles, mixed $totAuthLgvVehicles, mixed $category): mixed
    {
        $licence = m::mock($class)->makePartial();
        $licence->shouldReceive('getLicenceType->getId')
            ->withNoArgs()
            ->andReturn($type);
        $licence->shouldReceive('getTotAuthVehicles')
            ->withNoArgs()
            ->andReturn($totAuthVehicles);
        $licence->shouldReceive('getTotAuthHgvVehiclesZeroCoalesced')
            ->withNoArgs()
            ->andReturn($totAuthHgvVehicles);
        $licence->shouldReceive('getTotAuthLgvVehiclesZeroCoalesced')
            ->withNoArgs()
            ->andReturn($totAuthLgvVehicles);
        $licence->shouldReceive('getGoodsOrPsv->getId')
            ->withNoArgs()
            ->andReturn($category);

        return $licence;
    }

    public function testGetOtherNewApplications(): void
    {
        $organisationId = 53;
        $applicationId = 42;

        $organisation = m::mock(Organisation::class);
        $organisation->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($organisationId);

        $application = m::mock(Application::class);
        $application->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($applicationId);
        $application->shouldReceive('getLicence->getOrganisation')
            ->withNoArgs()
            ->andReturn($organisation);

        $activeApplication1 = m::mock(Application::class);
        $activeApplication1->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(50);
        $activeApplication1->shouldReceive('isVariation')
            ->withNoArgs()
            ->andReturnFalse();

        $activeApplication2 = m::mock(Application::class);
        $activeApplication2->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(78);
        $activeApplication2->shouldReceive('isVariation')
            ->withNoArgs()
            ->andReturnTrue();

        $activeApplication3 = m::mock(Application::class);
        $activeApplication3->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($applicationId);
        $activeApplication3->shouldReceive('isVariation')
            ->withNoArgs()
            ->andReturnFalse();

        $activeApplication4 = m::mock(Application::class);
        $activeApplication4->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(60);
        $activeApplication4->shouldReceive('isVariation')
            ->withNoArgs()
            ->andReturnFalse();

        $activeApplications = [$activeApplication1, $activeApplication2, $activeApplication3, $activeApplication4];

        $this->repoMap['Application']->shouldReceive('fetchActiveForOrganisation')
            ->with($organisationId)
            ->andReturn($activeApplications);

        $otherNewApplications = $this->sut->getOtherNewApplications($application);
        $this->assertCount(2, $otherNewApplications);
        $this->assertContains($activeApplication1, $otherNewApplications);
        $this->assertContains($activeApplication4, $otherNewApplications);
    }
}
