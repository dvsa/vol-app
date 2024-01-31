<?php

namespace OlcsTest\Listener\RouteParam;

use Common\Exception\DataServiceException;
use Common\FeatureToggle;
use Common\RefData;
use Common\Service\Data\Surrender;
use Interop\Container\ContainerInterface;
use Dvsa\Olcs\Transfer\Query\FeatureToggle\IsEnabled;
use Laminas\EventManager\Event;
use Laminas\Navigation\Navigation;
use Laminas\Navigation\Page\AbstractPage;
use Laminas\View\Helper\Placeholder;
use Laminas\View\HelperPluginManager;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Olcs\Event\RouteParam;
use Olcs\Listener\RouteParam\Licence;
use Olcs\Listener\RouteParams;
use Olcs\Service\Marker\MarkerService;

class LicenceTest extends TestCase
{
    /**
     * @var Licence
     */
    protected $sut;
    protected $signatureType;

    public function setUp(): void
    {
        parent::setup();
        $this->sut = new Licence();
    }

    public function tearDown(): void
    {
        $this->signatureType = null;
    }

    public function testAttach()
    {
        $mockEventManager = m::mock('Laminas\EventManager\EventManagerInterface');
        $mockEventManager->shouldReceive('attach')->once()
            ->with(RouteParams::EVENT_PARAM . 'licence', [$this->sut, 'onLicence'], 1);

        $this->sut->attach($mockEventManager);
    }

    protected function onLicenceSetup($licenceId, $licenceData)
    {
        $mockAnnotationBuilder = m::mock();
        $this->sut->setAnnotationBuilderService($mockAnnotationBuilder);

        $mockQueryService = m::mock();
        $this->sut->setQueryService($mockQueryService);

        $mockResult = m::mock();

        $mockMarkerService = m::mock(MarkerService::class);
        $this->sut->setMarkerService($mockMarkerService);

        $mockLicenceService = m::mock();
        $this->sut->setLicenceService($mockLicenceService);

        $mockViewHelperManager = m::mock(HelperPluginManager::class);
        $this->sut->setViewHelperManager($mockViewHelperManager);

        $mockAnnotationBuilder->shouldReceive('createQuery')->once()->andReturnUsing(
            function ($dto) use ($licenceId) {
                $this->assertInstanceOf(\Dvsa\Olcs\Transfer\Query\Licence\Licence::class, $dto);
                $this->assertSame(['id' => $licenceId], $dto->getArrayCopy());
                return 'QUERY';
            }
        );

        $mockQueryService->shouldReceive('send')->with('QUERY')->once()->andReturn($mockResult);

        if ($licenceData === false) {
            $mockResult->shouldReceive('isOk')->with()->once()->andReturn(false);
        } else {
            $mockResult->shouldReceive('isOk')->with()->once()->andReturn(true);
            $mockResult->shouldReceive('getResult')->with()->once()->andReturn($licenceData);

            $mockMarkerService->shouldReceive('addData')->with('licence', $licenceData)->once();
            $mockMarkerService->shouldReceive('addData')->with('continuationDetail', $licenceData['continuationMarker'])
                ->once();
            $mockMarkerService->shouldReceive('addData')->with('organisation', $licenceData['organisation'])->once();
            $mockMarkerService->shouldReceive('addData')->with('cases', $licenceData['cases'])->once();

            $mockLicenceService->shouldReceive('setId')->with($licenceId);

            $mockViewHelperManager->shouldReceive('get->getContainer')->with('licence')->andReturn(
                m::mock(Placeholder::class)
                    ->shouldReceive('set')
                    ->with($licenceData)
                    ->once()
                    ->getMock()
            );
            $mockViewHelperManager->shouldReceive('get->getContainer')->with('note')->andReturn(
                m::mock(Placeholder::class)
                    ->shouldReceive('set')
                    ->with('latest note')
                    ->once()
                    ->getMock()
            );
            $mockViewHelperManager->shouldReceive('get->getContainer')->with('isPriorityNote')->andReturn(
                m::mock(Placeholder::class)
                    ->shouldReceive('set')
                    ->with(true)
                    ->once()
                    ->getMock()
            );

            $mockAnnotationBuilder->shouldReceive('createQuery')->once()->andReturnUsing(
                function ($dto) use ($licenceId) {
                    $this->assertInstanceOf(IsEnabled::class, $dto);
                    $this->assertSame(['ids' => [FeatureToggle::MESSAGING]], $dto->getArrayCopy());
                    return 'FT_QUERY';
                }
            );
            $mockFtQueryResult = m::mock();
            $mockFtQueryResult->expects('getResult')->once()->andReturn(['isEnabled' => true]);
            $mockQueryService->shouldReceive('send')->with('FT_QUERY')->once()->andReturn($mockFtQueryResult);
        }
    }

    public function testOnLicenceQueryError()
    {
        $this->onLicenceSetup(32, false);
        $routeParam = new RouteParam();
        $routeParam->setValue(32);

        $event = new Event(null, $routeParam);

        $this->expectException(\RuntimeException::class);

        $this->sut->onLicence($event);
    }

    public function testOnLicenceWithValidGoodsLicence()
    {
        $licenceId = 4;
        $licence = [
            'id' => $licenceId,
            'licNo' => 'L2347137',
            'licenceType' => [
                'id' => RefData::LICENCE_TYPE_STANDARD_NATIONAL
            ],
            'status' => [
                'id' => RefData::LICENCE_STATUS_VALID
            ],
            'goodsOrPsv' => [
                'id' => RefData::LICENCE_CATEGORY_GOODS_VEHICLE
            ],
            'vehicleType' => [
                'id' => RefData::APP_VEHICLE_TYPE_HGV,
            ],
            'continuationMarker' => 'CONTINUATION_MARKER',
            'organisation' => 'ORGANISATION',
            'cases' => 'CASES',
            'licenceStatusRules' => [],
            'latestNote' => ['comment' => 'latest note', 'priority' => 'Y'],
            'canHaveInspectionRequest' => false,
        ];

        $this->onLicenceSetup($licenceId, $licence);

        // 'terminate' should be hidden for Goods vehicles
        $mockSidebar = m::mock();
        $this->mockHideButton($mockSidebar, 'licence-decisions-terminate');
        $this->mockHideButton($mockSidebar, 'licence-decisions-undo-surrender');
        $this->mockHideButton($mockSidebar, 'licence-decisions-undo-terminate');
        $this->mockHideButton($mockSidebar, 'licence-decisions-reset-to-valid');
        $this->sut->setNavigationService($mockSidebar);

        $mainNav = m::mock();
        $mainNav
            ->shouldReceive('findOneById')
            ->with('licence_bus')
            ->andReturn(
                m::mock()
                    ->shouldReceive('setVisible')
                    ->with(0)
                    ->once()
                    ->getMock()
            )
            ->once()
            ->shouldReceive('findOneBy')
            ->with('id', 'licence_processing_inspection_request')
            ->andReturn(
                m::mock()
                    ->shouldReceive('setVisible')
                    ->with(false)
                    ->once()
                    ->getMock()
            )
            ->once()
            ->shouldReceive('findOneById')
            ->with('licence_surrender')
            ->andReturn(
                m::mock()
                    ->shouldReceive('setVisible')
                    ->with(false)
                    ->once()
                    ->getMock()
            )
            ->once()
            ->getMock();

        $this->sut->setMainNavigationService($mainNav);

        $routeParam = new RouteParam();
        $routeParam->setValue($licenceId);

        $event = new Event(null, $routeParam);

        $this->sut->onLicence($event);
    }

    public function testOnLicenceWithValidPsvLicence()
    {
        $licenceId = 4;
        $licence = [
            'id' => $licenceId,
            'licNo' => 'L2347137',
            'licenceType' => [
                'id' => RefData::LICENCE_TYPE_STANDARD_NATIONAL
            ],
            'status' => [
                'id' => RefData::LICENCE_STATUS_VALID
            ],
            'goodsOrPsv' => [
                'id' => RefData::LICENCE_CATEGORY_PSV
            ],
            'vehicleType' => [
                'id' => RefData::APP_VEHICLE_TYPE_HGV,
            ],
            'continuationMarker' => 'CONTINUATION_MARKER',
            'organisation' => 'ORGANISATION',
            'cases' => 'CASES',
            'licenceStatusRules' => [],
            'latestNote' => ['comment' => 'latest note', 'priority' => 'Y'],
            'canHaveInspectionRequest' => true,
        ];

        $this->onLicenceSetup($licenceId, $licence);

        $mockSidebar = m::mock();
        $this->mockHideButton($mockSidebar, 'licence-decisions-undo-surrender');
        $this->mockHideButton($mockSidebar, 'licence-decisions-undo-terminate');
        $this->mockHideButton($mockSidebar, 'licence-decisions-reset-to-valid');
        $this->sut->setNavigationService($mockSidebar);
        $this->mockMainNavigation($licence['goodsOrPsv']['id']);

        $routeParam = new RouteParam();
        $routeParam->setValue($licenceId);

        $event = new Event(null, $routeParam);

        $this->sut->onLicence($event);
    }

    public function testOnLicenceWithTerminatedPsvLicence()
    {
        $licenceId = 4;
        $licence = [
            'id' => $licenceId,
            'licNo' => 'L2347137',
            'licenceType' => [
                'id' => RefData::LICENCE_TYPE_STANDARD_NATIONAL
            ],
            'status' => [
                'id' => RefData::LICENCE_STATUS_TERMINATED
            ],
            'goodsOrPsv' => [
                'id' => RefData::LICENCE_CATEGORY_PSV
            ],
            'vehicleType' => [
                'id' => RefData::APP_VEHICLE_TYPE_HGV,
            ],
            'continuationMarker' => 'CONTINUATION_MARKER',
            'organisation' => 'ORGANISATION',
            'cases' => 'CASES',
            'licenceStatusRules' => [],
            'latestNote' => ['comment' => 'latest note', 'priority' => 'Y'],
            'canHaveInspectionRequest' => true,
        ];

        $this->onLicenceSetup($licenceId, $licence);

        $mockSidebar = m::mock();
        $this->mockHideButton($mockSidebar, 'licence-quick-actions-create-variation');
        $this->mockHideButton($mockSidebar, 'licence-quick-actions-print-licence');
        $this->mockHideButton($mockSidebar, 'licence-decisions-curtail');
        $this->mockHideButton($mockSidebar, 'licence-decisions-revoke');
        $this->mockHideButton($mockSidebar, 'licence-decisions-suspend');
        $this->mockHideButton($mockSidebar, 'licence-decisions-surrender');
        $this->mockHideButton($mockSidebar, 'licence-decisions-terminate');
        $this->mockHideButton($mockSidebar, 'licence-decisions-undo-surrender');
        $this->mockHideButton($mockSidebar, 'licence-decisions-reset-to-valid');
        $this->sut->setNavigationService($mockSidebar);
        $this->mockMainNavigation($licence['goodsOrPsv']['id']);
        $routeParam = new RouteParam();
        $routeParam->setValue($licenceId);

        $event = new Event(null, $routeParam);

        $this->sut->onLicence($event);
    }

    public function testOnLicenceWithSurrenderedGoodsLicence()
    {
        $licenceId = 4;
        $licence = [
            'id' => $licenceId,
            'licNo' => 'L2347137',
            'licenceType' => [
                'id' => RefData::LICENCE_TYPE_STANDARD_NATIONAL
            ],
            'status' => [
                'id' => RefData::LICENCE_STATUS_SURRENDERED
            ],
            'goodsOrPsv' => [
                'id' => RefData::LICENCE_CATEGORY_GOODS_VEHICLE
            ],
            'vehicleType' => [
                'id' => RefData::APP_VEHICLE_TYPE_HGV,
            ],
            'continuationMarker' => 'CONTINUATION_MARKER',
            'organisation' => 'ORGANISATION',
            'cases' => 'CASES',
            'licenceStatusRules' => [],
            'latestNote' => ['comment' => 'latest note', 'priority' => 'Y'],
            'canHaveInspectionRequest' => true,
        ];

        $this->onLicenceSetup($licenceId, $licence);

        $mockSidebar = m::mock();
        $this->mockHideButton($mockSidebar, 'licence-quick-actions-create-variation');
        $this->mockHideButton($mockSidebar, 'licence-quick-actions-print-licence');
        $this->mockHideButton($mockSidebar, 'licence-decisions-curtail');
        $this->mockHideButton($mockSidebar, 'licence-decisions-revoke');
        $this->mockHideButton($mockSidebar, 'licence-decisions-suspend');
        $this->mockHideButton($mockSidebar, 'licence-decisions-surrender');
        $this->mockHideButton($mockSidebar, 'licence-decisions-terminate');
        $this->mockHideButton($mockSidebar, 'licence-decisions-undo-terminate');
        $this->mockHideButton($mockSidebar, 'licence-decisions-reset-to-valid');
        $this->sut->setNavigationService($mockSidebar);


        $this->mockMainNavigation($licence['goodsOrPsv']['id']);

        $routeParam = new RouteParam();
        $routeParam->setValue($licenceId);

        $event = new Event(null, $routeParam);

        $this->sut->onLicence($event);
    }

    public function testOnLicenceWithNotSubmittedPsvSpecialRestricted()
    {
        $licenceId = 4;
        $licence = [
            'id' => $licenceId,
            'licNo' => 'L2347137',
            'licenceType' => [
                'id' => RefData::LICENCE_TYPE_SPECIAL_RESTRICTED
            ],
            'goodsOrPsv' => [
                'id' => RefData::LICENCE_CATEGORY_PSV
            ],
            'vehicleType' => [
                'id' => RefData::APP_VEHICLE_TYPE_HGV,
            ],
            'status' => [
                'id' => RefData::LICENCE_STATUS_NOT_SUBMITTED
            ],
            'continuationMarker' => 'CONTINUATION_MARKER',
            'organisation' => 'ORGANISATION',
            'cases' => 'CASES',
            'licenceStatusRules' => [],
            'latestNote' => ['comment' => 'latest note', 'priority' => 'Y'],
            'canHaveInspectionRequest' => true,
        ];

        $this->onLicenceSetup($licenceId, $licence);

        $mockSidebar = m::mock();
        $this->mockHideButton($mockSidebar, 'licence-quick-actions-create-variation');
        $this->mockHideButton($mockSidebar, 'licence-quick-actions-print-licence');
        $this->mockHideButton($mockSidebar, 'licence-decisions-curtail');
        $this->mockHideButton($mockSidebar, 'licence-decisions-revoke');
        $this->mockHideButton($mockSidebar, 'licence-decisions-suspend');
        $this->mockHideButton($mockSidebar, 'licence-decisions-surrender');
        $this->mockHideButton($mockSidebar, 'licence-decisions-terminate');
        $this->mockHideButton($mockSidebar, 'licence-decisions-undo-surrender');
        $this->mockHideButton($mockSidebar, 'licence-decisions-undo-terminate');
        $this->mockHideButton($mockSidebar, 'licence-decisions-reset-to-valid');
        $this->sut->setNavigationService($mockSidebar);

        $this->mockMainNavigation($licence['goodsOrPsv']['id']);

        $routeParam = new RouteParam();
        $routeParam->setValue($licenceId);

        $event = new Event(null, $routeParam);

        $this->sut->onLicence($event);
    }

    public function testOnLicenceWithValidGoodsLicenceAndPendingChange()
    {
        $licenceId = 4;
        $licence = [
            'id' => $licenceId,
            'licNo' => 'L2347137',
            'licenceType' => [
                'id' => RefData::LICENCE_TYPE_STANDARD_NATIONAL
            ],
            'status' => [
                'id' => RefData::LICENCE_STATUS_VALID
            ],
            'goodsOrPsv' => [
                'id' => RefData::LICENCE_CATEGORY_GOODS_VEHICLE
            ],
            'vehicleType' => [
                'id' => RefData::APP_VEHICLE_TYPE_HGV,
            ],
            'continuationMarker' => 'CONTINUATION_MARKER',
            'organisation' => 'ORGANISATION',
            'cases' => 'CASES',
            'licenceStatusRules' => [],
            'licenceStatusRules' => [
                [
                    'startProcessedDate' => null,
                    'licenceStatus' => ['id' => 'lsts_suspended'],
                ]
            ],
            'latestNote' => ['comment' => 'latest note', 'priority' => 'Y'],
            'canHaveInspectionRequest' => true,
        ];

        $this->onLicenceSetup($licenceId, $licence);

        $mockSidebar = m::mock();
        $this->mockHideButton($mockSidebar, 'licence-decisions-terminate');
        $this->mockHideButton($mockSidebar, 'licence-decisions-surrender');
        $this->mockHideButton($mockSidebar, 'licence-decisions-curtail');
        $this->mockHideButton($mockSidebar, 'licence-decisions-revoke');
        $this->mockHideButton($mockSidebar, 'licence-decisions-suspend');
        $this->mockHideButton($mockSidebar, 'licence-decisions-undo-surrender');
        $this->mockHideButton($mockSidebar, 'licence-decisions-undo-terminate');
        $this->mockHideButton($mockSidebar, 'licence-decisions-reset-to-valid');
        $this->sut->setNavigationService($mockSidebar);

        $this->mockMainNavigation($licence['goodsOrPsv']['id']);

        $routeParam = new RouteParam();
        $routeParam->setValue($licenceId);

        $event = new Event(null, $routeParam);

        $this->sut->onLicence($event);
    }

    public function testOnLicenceWithValidPsvLicenceAndPendingChange()
    {
        $licenceId = 4;
        $licence = [
            'id' => $licenceId,
            'licNo' => 'L2347137',
            'licenceType' => [
                'id' => RefData::LICENCE_TYPE_STANDARD_NATIONAL
            ],
            'status' => [
                'id' => RefData::LICENCE_STATUS_VALID
            ],
            'goodsOrPsv' => [
                'id' => RefData::LICENCE_CATEGORY_PSV
            ],
            'vehicleType' => [
                'id' => RefData::APP_VEHICLE_TYPE_HGV,
            ],
            'continuationMarker' => 'CONTINUATION_MARKER',
            'organisation' => 'ORGANISATION',
            'cases' => 'CASES',
            'licenceStatusRules' => [
                [
                    'startProcessedDate' => '',
                    'licenceStatus' => ['id' => 'lsts_suspended'],
                ]
            ],
            'latestNote' => ['comment' => 'latest note', 'priority' => 'Y'],
            'canHaveInspectionRequest' => true,
        ];

        $this->onLicenceSetup($licenceId, $licence);

        $mockSidebar = m::mock();
        $this->mockHideButton($mockSidebar, 'licence-decisions-terminate');
        $this->mockHideButton($mockSidebar, 'licence-decisions-surrender');
        $this->mockHideButton($mockSidebar, 'licence-decisions-curtail');
        $this->mockHideButton($mockSidebar, 'licence-decisions-revoke');
        $this->mockHideButton($mockSidebar, 'licence-decisions-suspend');
        $this->mockHideButton($mockSidebar, 'licence-decisions-undo-surrender');
        $this->mockHideButton($mockSidebar, 'licence-decisions-undo-terminate');
        $this->mockHideButton($mockSidebar, 'licence-decisions-reset-to-valid');
        $this->sut->setNavigationService($mockSidebar);

        $this->mockMainNavigation($licence['goodsOrPsv']['id']);

        $routeParam = new RouteParam();
        $routeParam->setValue($licenceId);

        $event = new Event(null, $routeParam);

        $this->sut->onLicence($event);
    }

    public function testOnLicenceWithRevokedLicence()
    {
        $licenceId = 4;
        $licence = [
            'id' => $licenceId,
            'licNo' => 'L2347137',
            'licenceType' => [
                'id' => RefData::LICENCE_TYPE_STANDARD_NATIONAL
            ],
            'status' => [
                'id' => RefData::LICENCE_STATUS_REVOKED
            ],
            'goodsOrPsv' => [
                'id' => RefData::LICENCE_CATEGORY_GOODS_VEHICLE
            ],
            'vehicleType' => [
                'id' => RefData::APP_VEHICLE_TYPE_HGV,
            ],
            'continuationMarker' => 'CONTINUATION_MARKER',
            'organisation' => 'ORGANISATION',
            'cases' => 'CASES',
            'licenceStatusRules' => [
                [
                    'startProcessedDate' => '2013-12-12',
                    'licenceStatus' => ['id' => 'lsts_suspended'],
                ]
            ],
            'latestNote' => ['comment' => 'latest note', 'priority' => 'Y'],
            'canHaveInspectionRequest' => true,
        ];

        $this->onLicenceSetup($licenceId, $licence);

        $mockSidebar = m::mock();
        $this->mockHideButton($mockSidebar, 'licence-quick-actions-create-variation');
        $this->mockHideButton($mockSidebar, 'licence-quick-actions-print-licence');
        $this->mockHideButton($mockSidebar, 'licence-decisions-curtail');
        $this->mockHideButton($mockSidebar, 'licence-decisions-revoke');
        $this->mockHideButton($mockSidebar, 'licence-decisions-suspend');
        $this->mockHideButton($mockSidebar, 'licence-decisions-surrender');
        $this->mockHideButton($mockSidebar, 'licence-decisions-terminate');
        $this->mockHideButton($mockSidebar, 'licence-decisions-undo-surrender');
        $this->mockHideButton($mockSidebar, 'licence-decisions-undo-terminate');
        $this->sut->setNavigationService($mockSidebar);

        $this->mockMainNavigation($licence['goodsOrPsv']['id']);

        $routeParam = new RouteParam();
        $routeParam->setValue($licenceId);

        $event = new Event(null, $routeParam);

        $this->sut->onLicence($event);
    }

    public function testInvoke()
    {
        $mockViewHelperManager = m::mock('Laminas\View\HelperPluginManager');
        $mockLicenceService = m::mock('Common\Service\Data\Licence');
        $mockSurrenderService = m::mock(Surrender::class);
        $mockNavigation = m::mock(Navigation::class); // 'right-sidebar'
        $mockAnnotationBuilder = m::mock();
        $mockQueryService = m::mock();
        $mockMarkerService = m::mock(MarkerService::class);
        $mainNav = m::mock(Navigation::class);

        $mockSl = m::mock(ContainerInterface::class);
        $mockSl->shouldReceive('get')->with('ViewHelperManager')->andReturn($mockViewHelperManager);
        $mockSl->shouldReceive('get')->with('DataServiceManager')->andReturnSelf();
        $mockSl->shouldReceive('get')->with('Common\Service\Data\Licence')->andReturn($mockLicenceService);
        $mockSl->shouldReceive('get')->with('Common\Service\Data\Surrender')->andReturn($mockSurrenderService);
        $mockSl->shouldReceive('get')->with('right-sidebar')->andReturn($mockNavigation);
        $mockSl->shouldReceive('get')->with(MarkerService::class)->andReturn($mockMarkerService);
        $mockSl->shouldReceive('get')->with('TransferAnnotationBuilder')->andReturn($mockAnnotationBuilder);
        $mockSl->shouldReceive('get')->with('QueryService')->andReturn($mockQueryService);
        $mockSl->shouldReceive('get')->with('navigation')->andReturn($mainNav);

        $sut = new Licence();
        $service = $sut->__invoke($mockSl, Licence::class);

        $this->assertSame($sut, $service);
        $this->assertSame($mockViewHelperManager, $sut->getViewHelperManager());
        $this->assertSame($mockLicenceService, $sut->getLicenceService());
        $this->assertSame($mockNavigation, $sut->getNavigationService());
        $this->assertSame($mockMarkerService, $sut->getMarkerService());
        $this->assertSame($mockAnnotationBuilder, $sut->getAnnotationBuilderService());
        $this->assertSame($mockQueryService, $sut->getQueryService());
        $this->assertSame($mainNav, $sut->getMainNavigationService());
    }

    public function testOnLicenceSurrenderDigitallySigned()
    {
        $licenceId = 4;
        $licence = [
            'id' => $licenceId,
            'licNo' => 'L2347137',
            'licenceType' => [
                'id' => RefData::LICENCE_TYPE_STANDARD_NATIONAL
            ],
            'status' => [
                'id' => RefData::LICENCE_STATUS_SURRENDER_UNDER_CONSIDERATION
            ],
            'goodsOrPsv' => [
                'id' => RefData::LICENCE_CATEGORY_GOODS_VEHICLE
            ],
            'vehicleType' => [
                'id' => RefData::APP_VEHICLE_TYPE_HGV,
            ],
            'continuationMarker' => 'CONTINUATION_MARKER',
            'organisation' => 'ORGANISATION',
            'cases' => 'CASES',
            'licenceStatusRules' => [],
            'licenceStatusRules' => [
                [
                    'startProcessedDate' => null,
                    'licenceStatus' => ['id' => 'lsts_suspended'],
                ]
            ],
            'latestNote' => ['comment' => 'latest note', 'priority' => 'Y'],
            'canHaveInspectionRequest' => true,
        ];

        $this->onLicenceSetup($licenceId, $licence);

        $mockSidebar = m::mock();
        $this->mockHideButton($mockSidebar, 'licence-quick-actions-print-licence');
        $this->mockHideButton($mockSidebar, 'licence-decisions-reset-to-valid');
        $this->mockHideButton($mockSidebar, 'licence-decisions-curtail', 2);
        $this->mockHideButton($mockSidebar, 'licence-decisions-revoke', 2);
        $this->mockHideButton($mockSidebar, 'licence-decisions-suspend', 2);
        $this->mockHideButton($mockSidebar, 'licence-decisions-surrender');
        $this->mockHideButton($mockSidebar, 'licence-decisions-terminate');
        $this->mockHideButton($mockSidebar, 'licence-decisions-undo-surrender');
        $this->mockHideButton($mockSidebar, 'licence-decisions-undo-terminate');
        $this->mockHideButton($mockSidebar, 'licence-quick-actions-create-variation');

        $this->sut->setNavigationService($mockSidebar);
        $this->signatureType = RefData::SIGNATURE_TYPE_DIGITAL_SIGNATURE;
        $this->mockMainNavigation($licence['goodsOrPsv']['id'], true);

        $mockSurrenderService = m::mock(Surrender::class);
        $mockSurrenderService->shouldReceive('fetchSurrenderData')->with(4)->times(1)->andReturn([
            'signatureType' => ['id' => $this->signatureType]
        ]);
        $this->sut->setSurrenderService($mockSurrenderService);
        $routeParam = new RouteParam();
        $routeParam->setValue($licenceId);

        $event = new Event(null, $routeParam);
        $this->sut->onLicence($event);
    }

    public function testOnLicenceSurrenderedPhysicallySigned()
    {
        $licenceId = 4;
        $licence = [
            'id' => $licenceId,
            'licNo' => 'L2347137',
            'licenceType' => [
                'id' => RefData::LICENCE_TYPE_STANDARD_NATIONAL
            ],
            'status' => [
                'id' => RefData::LICENCE_STATUS_SURRENDER_UNDER_CONSIDERATION
            ],
            'goodsOrPsv' => [
                'id' => RefData::LICENCE_CATEGORY_GOODS_VEHICLE
            ],
            'vehicleType' => [
                'id' => RefData::APP_VEHICLE_TYPE_HGV,
            ],
            'continuationMarker' => 'CONTINUATION_MARKER',
            'organisation' => 'ORGANISATION',
            'cases' => 'CASES',
            'licenceStatusRules' => [],
            'licenceStatusRules' => [
                [
                    'startProcessedDate' => null,
                    'licenceStatus' => ['id' => 'lsts_suspended'],
                ]
            ],
            'latestNote' => ['comment' => 'latest note', 'priority' => 'Y'],
            'canHaveInspectionRequest' => true,
        ];

        $this->onLicenceSetup($licenceId, $licence);

        $mockSidebar = m::mock();

        $this->mockHideButton($mockSidebar, 'licence-quick-actions-print-licence');
        $this->mockHideButton($mockSidebar, 'licence-decisions-reset-to-valid');
        $this->mockHideButton($mockSidebar, 'licence-decisions-curtail', 2);
        $this->mockHideButton($mockSidebar, 'licence-decisions-revoke', 2);
        $this->mockHideButton($mockSidebar, 'licence-decisions-suspend', 2);
        $this->mockHideButton($mockSidebar, 'licence-decisions-surrender');
        $this->mockHideButton($mockSidebar, 'licence-decisions-terminate');
        $this->mockHideButton($mockSidebar, 'licence-decisions-undo-surrender');
        $this->mockHideButton($mockSidebar, 'licence-decisions-undo-terminate');
        $this->mockHideButton($mockSidebar, 'licence-quick-actions-create-variation');

        $this->sut->setNavigationService($mockSidebar);

        $this->signatureType = RefData::SIGNATURE_TYPE_PHYSICAL_SIGNATURE;
        $this->mockMainNavigation($licence['goodsOrPsv']['id'], true);

        $mockSurrenderService = m::mock(Surrender::class);
        $mockSurrenderService->shouldReceive('fetchSurrenderData')->with(4)->times(1)->andReturn([
            'signatureType' => ['id' => $this->signatureType]
        ]);
        $this->sut->setSurrenderService($mockSurrenderService);

        $routeParam = new RouteParam();
        $routeParam->setValue($licenceId);

        $event = new Event(null, $routeParam);

        $this->sut->onLicence($event);
    }

    /**
     * Set an expectation that a given nav button should be hidden
     *
     * @param Mockery\Mock $mockSidebar
     * @param string       $navId
     * @param int          $times
     */
    protected function mockHideButton($mockSidebar, $navId, $times = 1)
    {
        $mockSidebar
            ->shouldReceive('findById')
            ->with($navId)
            ->andReturn(
                m::namedMock(str_replace('-', '', $navId))
                    ->shouldReceive('setVisible')
                    ->with(0)
                    ->times($times)
                    ->getMock()
            );
    }

    protected function mockMainNavigation($type, $surrender = false): void
    {
        $mainNav = m::mock(Navigation::class);

        if (!$surrender) {
            if ($type === RefData::LICENCE_CATEGORY_GOODS_VEHICLE) {
                $mainNav->shouldReceive('findOneById')->with('licence_bus')->andReturn(
                    m::mock()->shouldReceive('setVisible')->with(0)->once()->getMock()
                )->getMock();
            }

            $mainNav->shouldReceive('findOneById')->with('licence_surrender')->andReturn(
                m::mock()->shouldReceive('setVisible')->with(0)->once()->getMock()
            )->getMock();
        } else {
            if ($type === RefData::LICENCE_CATEGORY_GOODS_VEHICLE) {
                $mockLicenceBusMenu = m::mock(AbstractPage::class);
                $mockLicenceBusMenu->shouldReceive('setVisible')->with(0)->once()->getMock();
                $mainNav->shouldReceive('findOneById')->with('licence_bus')->andReturn(
                    $mockLicenceBusMenu
                );
            }
        }

        if ($type === RefData::LICENCE_CATEGORY_PSV) {
            $communityLicencesPage = m::mock(AbstractPage::class);
            $communityLicencesPage->shouldReceive('getLabel')
                ->andReturn('licences.page');
            $communityLicencesPage->shouldReceive('setLabel')
                ->with('licences.page.psv')
                ->once();

            $mainNav->shouldReceive('findOneById')
                ->with('licence_community_licences')
                ->andReturn($communityLicencesPage);

            $mockIrhpPermitMenu = m::mock(AbstractPage::class);
            $mockIrhpPermitMenu->expects('setVisible')->with(false);

            $mainNav->expects('findOneById')->with('licence_irhp_permits')->andReturn(
                $mockIrhpPermitMenu
            );
        }

        $this->sut->setMainNavigationService($mainNav);
    }

    public function testSurrenderServiceFails()
    {
        $licenceId = 4;
        $licence = [
            'id' => $licenceId,
            'licNo' => 'L2347137',
            'licenceType' => [
                'id' => RefData::LICENCE_TYPE_STANDARD_NATIONAL
            ],
            'status' => [
                'id' => RefData::LICENCE_STATUS_SURRENDER_UNDER_CONSIDERATION
            ],
            'goodsOrPsv' => [
                'id' => RefData::LICENCE_CATEGORY_GOODS_VEHICLE
            ],
            'vehicleType' => [
                'id' => RefData::APP_VEHICLE_TYPE_HGV,
            ],
            'continuationMarker' => 'CONTINUATION_MARKER',
            'organisation' => 'ORGANISATION',
            'cases' => 'CASES',
            'licenceStatusRules' => [],
            'licenceStatusRules' => [
                [
                    'startProcessedDate' => null,
                    'licenceStatus' => ['id' => 'lsts_suspended'],
                ]
            ],
            'latestNote' => ['comment' => 'latest note', 'priority' => 'Y'],
            'canHaveInspectionRequest' => true,
        ];

        $this->onLicenceSetup($licenceId, $licence);

        $mockSidebar = m::mock();

        $this->mockHideButton($mockSidebar, 'licence-quick-actions-print-licence');
        $this->mockHideButton($mockSidebar, 'licence-decisions-reset-to-valid');
        $this->mockHideButton($mockSidebar, 'licence-decisions-curtail', 2);
        $this->mockHideButton($mockSidebar, 'licence-decisions-revoke', 2);
        $this->mockHideButton($mockSidebar, 'licence-decisions-suspend', 2);
        $this->mockHideButton($mockSidebar, 'licence-decisions-terminate');
        $this->mockHideButton($mockSidebar, 'licence-decisions-undo-surrender');
        $this->mockHideButton($mockSidebar, 'licence-decisions-undo-terminate');
        $this->mockHideButton($mockSidebar, 'licence-decisions-surrender');
        $this->mockHideButton($mockSidebar, 'licence-quick-actions-create-variation');


        $this->sut->setNavigationService($mockSidebar);
        $this->signatureType = RefData::SIGNATURE_TYPE_DIGITAL_SIGNATURE;
        $this->mockMainNavigation($licence['goodsOrPsv']['id'], true);

        $mockSurrenderService = m::mock(Surrender::class);
        $mockSurrenderService->shouldReceive('fetchSurrenderData')->with(4)->times(1)->andThrow(
            new DataServiceException('TEST')
        );
        $this->sut->setSurrenderService($mockSurrenderService);

        $routeParam = new RouteParam();
        $routeParam->setValue($licenceId);

        $event = new Event(null, $routeParam);

        $this->sut->onLicence($event);
    }
}
