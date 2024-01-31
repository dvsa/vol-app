<?php

namespace OlcsTest\Listener\RouteParam;

use Common\RefData;
use Interop\Container\ContainerInterface;
use Laminas\EventManager\Event;
use Olcs\Service\Marker\MarkerService;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;
use Olcs\Event\RouteParam;
use Olcs\Listener\RouteParams;
use Olcs\Listener\RouteParam\Application;
use Laminas\Navigation\Page\AbstractPage;

class ApplicationTest extends MockeryTestCase
{
    public function setUp(): void
    {
        $this->sut = new Application();

        parent::setUp();
    }

    protected function setupMockApplication($id, $applicationData)
    {
        $mockAnnotationBuilder = m::mock();
        $mockQueryService  = m::mock();

        $mockAnnotationBuilder->shouldReceive('createQuery')->once()->andReturnUsing(
            function ($dto) use ($id) {
                $this->assertSame($id, $dto->getId());
                return 'QUERY';
            }
        );

        $mockResult = m::mock();
        $mockResult->shouldReceive('isOk')->with()->once()->andReturn(true);
        $mockResult->shouldReceive('getResult')->with()->once()->andReturn($applicationData);

        $mockQueryService->shouldReceive('send')->with('QUERY')->once()->andReturn($mockResult);

        $mockMarkerService = m::mock(MarkerService::class);
        $mockMarkerService->shouldReceive('addData')->with('organisation', $applicationData['licence']['organisation']);

        $mockApplicationService = m::mock()->shouldReceive('setId')->with($id)->getMock();
        $this->sut->setApplicationService($mockApplicationService);

        $this->sut->setAnnotationBuilder($mockAnnotationBuilder);
        $this->sut->setQueryService($mockQueryService);
        $this->sut->setMarkerService($mockMarkerService);
    }

    public function testAttach()
    {
        $sut = new Application();

        $mockEventManager = m::mock('Laminas\EventManager\EventManagerInterface');
        $mockEventManager->shouldReceive('attach')->once()
            ->with(RouteParams::EVENT_PARAM . 'application', [$sut, 'onApplication'], 1);

        $sut->attach($mockEventManager);
    }

    /**
     * @dataProvider onApplicationProvider
     * @param string $status
     * @param string $category
     * @param string $type
     * @param bool $canHaveCases
     * @param int $expectedCallsNo
     */
    public function testOnApplication($status, $category, $type, $canHaveCases, $expectedCallsNo)
    {
        $applicationId = 69;
        $application = [
            'id' => $applicationId,
            'status' => [
                'id' => $status
            ],
            's4s' => [
                [
                    'outcome' => null
                ],
                [
                    'outcome' => ['id' => RefData::S4_STATUS_APPROVED]
                ]
            ],
            'canCreateCase' => $canHaveCases,
            'goodsOrPsv' => ['id' => $category],
            'vehicleType' => [
                'id' => RefData::APP_VEHICLE_TYPE_HGV
            ],
            'isVariation' => $type,
            'licence' => [
                'organisation' => 'ORGANISATION',
                'id' => 101,
            ],
            'licenceType' => ['id' => 'foo'],
            'existingPublication' => false,
            'latestNote' => ['comment' => 'latest note'],
            'canHaveInspectionRequest' => false,
        ];

        $quickViewActionsVisible = ($status !== RefData::APPLICATION_STATUS_VALID);

        $routeParam = new RouteParam();
        $routeParam->setValue($applicationId);
        $routeParam->setTarget(
            m::mock()
                ->shouldReceive('trigger')->once()->with('licence', 101)
                ->getMock()
        );

        $event = new Event(null, $routeParam);

        $mockApplicationCaseNavigationService = m::mock('\StdClass');
        $mockApplicationCaseNavigationService->shouldReceive('setVisible')->times($expectedCallsNo)->with(false);

        $mockNavigationService = m::mock('Laminas\Navigation\Navigation');
        $mockNavigationService
            ->shouldReceive('findOneById')
            ->with('application_case')
            ->andReturn($mockApplicationCaseNavigationService)
            ->shouldReceive('findOneById')
            ->with('application_processing_inspection_request')
            ->andReturn(
                m::mock()
                    ->shouldReceive('setVisible')
                    ->with(false)
                    ->once()
                    ->getMock()
            )
            ->once();

        $this->setupMockApplication($applicationId, $application);

        $mockContainer = m::mock('Laminas\View\Helper\Placeholder\Container');
        $mockContainer->shouldReceive('set')->with($application)->once();
        $mockContainer->shouldReceive('set')->with('latest note')->once();

        $mockPlaceholder = m::mock('Laminas\View\Helper\Placeholder');
        $mockPlaceholder->shouldReceive('getContainer')->with('application')->andReturn($mockContainer)->once();
        $mockPlaceholder->shouldReceive('getContainer')->with('note')->andReturn($mockContainer)->once();

        $mockViewHelperManager = m::mock('Laminas\View\HelperPluginManager');
        $mockViewHelperManager->shouldReceive('get')->with('placeholder')->andReturn($mockPlaceholder);

        $mockSidebar = m::mock()
            ->shouldReceive('findById')
            ->with('application-quick-actions')
            ->andReturn(
                m::mock()
                ->shouldReceive('setVisible')
                ->with($quickViewActionsVisible)
                ->getMock()
            )
            ->shouldReceive('findById')
            ->andReturn(
                m::mock()
                ->shouldReceive('setVisible')
                ->getMock()
            )
            ->getMock();

        $this->sut->setViewHelperManager($mockViewHelperManager);
        $this->sut->setNavigationService($mockNavigationService);
        $this->sut->setSidebarNavigationService($mockSidebar);

        $this->sut->onApplication($event);
    }

    public function testModifyCommunityLicenceNav()
    {
        $status = RefData::APPLICATION_STATUS_UNDER_CONSIDERATION;
        $category = RefData::LICENCE_CATEGORY_PSV;
        $type = RefData::APPLICATION_TYPE_NEW;
        $canHaveCases = true;
        $expectedCallsNo = 0;

        $applicationId = 69;
        $application = [
            'id' => $applicationId,
            'status' => [
                'id' => $status
            ],
            's4s' => [
                [
                    'outcome' => null
                ],
                [
                    'outcome' => ['id' => RefData::S4_STATUS_APPROVED]
                ]
            ],
            'canCreateCase' => $canHaveCases,
            'goodsOrPsv' => ['id' => $category],
            'vehicleType' => [
                'id' => RefData::APP_VEHICLE_TYPE_HGV
            ],
            'isVariation' => $type,
            'licence' => [
                'organisation' => 'ORGANISATION',
                'id' => 101,
            ],
            'licenceType' => ['id' => 'foo'],
            'existingPublication' => false,
            'latestNote' => ['comment' => 'latest note'],
            'canHaveInspectionRequest' => false,
        ];

        $quickViewActionsVisible = ($status !== RefData::APPLICATION_STATUS_VALID);

        $routeParam = new RouteParam();
        $routeParam->setValue($applicationId);
        $routeParam->setTarget(
            m::mock()->shouldReceive('trigger')->once()->with('licence', 101)->getMock()
        );

        $event = new Event(null, $routeParam);

        $mockApplicationCaseNavigationService = m::mock('\StdClass');
        $mockApplicationCaseNavigationService->shouldReceive('setVisible')->times($expectedCallsNo)->with(false);

        $mockNavigationService = m::mock('Laminas\Navigation\Navigation');
        $mockNavigationService->shouldReceive('findOneById')
            ->with('application_case')
            ->andReturn($mockApplicationCaseNavigationService);
        $mockNavigationService->shouldReceive('findOneById')
            ->with('application_processing_inspection_request')
            ->andReturn(
                m::mock()
                    ->shouldReceive('setVisible')
                    ->with(false)
                    ->once()
                    ->getMock()
            )
            ->once();

        $variationCommunityLicencesPage = m::mock(AbstractPage::class);
        $variationCommunityLicencesPage->shouldReceive('getLabel')
            ->andReturn('variation.page.label');
        $variationCommunityLicencesPage->shouldReceive('setLabel')
            ->with('variation.page.label.psv')
            ->once();

        $mockNavigationService->shouldReceive('findOneById')
            ->with('variation_community_licences')
            ->andReturn($variationCommunityLicencesPage);

        $applicationCommunityLicencesPage = m::mock(AbstractPage::class);
        $applicationCommunityLicencesPage->shouldReceive('getLabel')
            ->andReturn('application.page.label');
        $applicationCommunityLicencesPage->shouldReceive('setLabel')
            ->with('application.page.label.psv')
            ->once();

        $mockNavigationService->shouldReceive('findOneById')
            ->with('application_community_licences')
            ->andReturn($applicationCommunityLicencesPage);

        $this->setupMockApplication($applicationId, $application);

        $mockContainer = m::mock('Laminas\View\Helper\Placeholder\Container');
        $mockContainer->shouldReceive('set')->with($application)->once();
        $mockContainer->shouldReceive('set')->with('latest note')->once();

        $mockPlaceholder = m::mock('Laminas\View\Helper\Placeholder');
        $mockPlaceholder->shouldReceive('getContainer')->with('application')->andReturn($mockContainer)->once();
        $mockPlaceholder->shouldReceive('getContainer')->with('note')->andReturn($mockContainer)->once();

        $mockViewHelperManager = m::mock('Laminas\View\HelperPluginManager');
        $mockViewHelperManager->shouldReceive('get')->with('placeholder')->andReturn($mockPlaceholder);

        $mockSidebar = m::mock()
            ->shouldReceive('findById')
            ->with('application-quick-actions')
            ->andReturn(
                m::mock()
                ->shouldReceive('setVisible')
                ->with($quickViewActionsVisible)
                ->getMock()
            )
            ->shouldReceive('findById')
            ->andReturn(
                m::mock()
                ->shouldReceive('setVisible')
                ->getMock()
            )
            ->getMock();

        $this->sut->setViewHelperManager($mockViewHelperManager);
        $this->sut->setNavigationService($mockNavigationService);
        $this->sut->setSidebarNavigationService($mockSidebar);

        $this->sut->onApplication($event);
    }

    public function testModifyOperatingCentreNav()
    {
        $status = RefData::APPLICATION_STATUS_UNDER_CONSIDERATION;
        $category = RefData::LICENCE_CATEGORY_GOODS_VEHICLE;
        $type = RefData::APPLICATION_TYPE_NEW;
        $canHaveCases = true;
        $expectedCallsNo = 0;

        $applicationId = 69;
        $application = [
            'id' => $applicationId,
            'status' => [
                'id' => $status
            ],
            's4s' => [
                [
                    'outcome' => null
                ],
                [
                    'outcome' => ['id' => RefData::S4_STATUS_APPROVED]
                ]
            ],
            'canCreateCase' => $canHaveCases,
            'goodsOrPsv' => ['id' => $category],
            'vehicleType' => [
                'id' => RefData::APP_VEHICLE_TYPE_LGV
            ],
            'isVariation' => $type,
            'licence' => [
                'organisation' => 'ORGANISATION',
                'id' => 101,
            ],
            'licenceType' => ['id' => 'foo'],
            'existingPublication' => false,
            'latestNote' => ['comment' => 'latest note'],
            'canHaveInspectionRequest' => false,
        ];

        $quickViewActionsVisible = ($status !== RefData::APPLICATION_STATUS_VALID);

        $routeParam = new RouteParam();
        $routeParam->setValue($applicationId);
        $routeParam->setTarget(
            m::mock()
                ->shouldReceive('trigger')->once()->with('licence', 101)
                ->getMock()
        );

        $event = new Event(null, $routeParam);

        $mockApplicationCaseNavigationService = m::mock('\StdClass');
        $mockApplicationCaseNavigationService->shouldReceive('setVisible')->times($expectedCallsNo)->with(false);

        $mockNavigationService = m::mock('Laminas\Navigation\Navigation');
        $mockNavigationService->shouldReceive('findOneById')
            ->with('application_case')
            ->andReturn($mockApplicationCaseNavigationService);
        $mockNavigationService->shouldReceive('findOneById')
            ->with('application_processing_inspection_request')
            ->andReturn(
                m::mock()
                    ->shouldReceive('setVisible')
                    ->with(false)
                    ->once()
                    ->getMock()
            )
            ->once();

        $variationOperatingCentresPage = m::mock(AbstractPage::class);
        $variationOperatingCentresPage->shouldReceive('getLabel')
            ->andReturn('variation.page.label');
        $variationOperatingCentresPage->shouldReceive('setLabel')
            ->with('variation.page.label.lgv')
            ->once();

        $mockNavigationService->shouldReceive('findOneById')
            ->with('variation_operating_centres')
            ->andReturn($variationOperatingCentresPage);

        $applicationOperatingCentresPage = m::mock(AbstractPage::class);
        $applicationOperatingCentresPage->shouldReceive('getLabel')
            ->andReturn('application.page.label');
        $applicationOperatingCentresPage->shouldReceive('setLabel')
            ->with('application.page.label.lgv')
            ->once();

        $mockNavigationService->shouldReceive('findOneById')
            ->with('application_operating_centres')
            ->andReturn($applicationOperatingCentresPage);

        $this->setupMockApplication($applicationId, $application);

        $mockContainer = m::mock('Laminas\View\Helper\Placeholder\Container');
        $mockContainer->shouldReceive('set')->with($application)->once();
        $mockContainer->shouldReceive('set')->with('latest note')->once();

        $mockPlaceholder = m::mock('Laminas\View\Helper\Placeholder');
        $mockPlaceholder->shouldReceive('getContainer')->with('application')->andReturn($mockContainer)->once();
        $mockPlaceholder->shouldReceive('getContainer')->with('note')->andReturn($mockContainer)->once();

        $mockViewHelperManager = m::mock('Laminas\View\HelperPluginManager');
        $mockViewHelperManager->shouldReceive('get')->with('placeholder')->andReturn($mockPlaceholder);

        $mockSidebar = m::mock()
            ->shouldReceive('findById')
            ->with('application-quick-actions')
            ->andReturn(
                m::mock()
                ->shouldReceive('setVisible')
                ->with($quickViewActionsVisible)
                ->getMock()
            )
            ->shouldReceive('findById')
            ->andReturn(
                m::mock()
                ->shouldReceive('setVisible')
                ->getMock()
            )
            ->getMock();

        $this->sut->setViewHelperManager($mockViewHelperManager);
        $this->sut->setNavigationService($mockNavigationService);
        $this->sut->setSidebarNavigationService($mockSidebar);

        $this->sut->onApplication($event);
    }

    public function onApplicationProvider()
    {
        return [
            [
                RefData::APPLICATION_STATUS_UNDER_CONSIDERATION,
                RefData::LICENCE_CATEGORY_GOODS_VEHICLE,
                RefData::APPLICATION_TYPE_NEW,
                true,
                0
            ],
            [
                RefData::APPLICATION_STATUS_GRANTED,
                RefData::LICENCE_CATEGORY_GOODS_VEHICLE,
                RefData::APPLICATION_TYPE_NEW,
                false,
                1
            ],
            [
                RefData::APPLICATION_STATUS_GRANTED,
                RefData::LICENCE_CATEGORY_GOODS_VEHICLE,
                RefData::APPLICATION_TYPE_VARIATION,
                false,
                1
            ],
            [
                RefData::APPLICATION_STATUS_VALID,
                RefData::LICENCE_CATEGORY_GOODS_VEHICLE,
                RefData::APPLICATION_TYPE_VARIATION,
                false,
                1
            ],
            [
                RefData::APPLICATION_STATUS_NOT_SUBMITTED,
                RefData::LICENCE_CATEGORY_GOODS_VEHICLE,
                RefData::APPLICATION_TYPE_NEW,
                true,
                0
            ],
        ];
    }

    public function testSetupPublishApplicationButtonExistingPublicationTrue()
    {
        $applicationData = [
            'id' => 1066,
            'licence' => [
                'organisation' => [],
                'id' => 99,
            ],
            'status' => ['id' => 'xx'],
            's4s' => [],
            'isVariation' => false,
            'canCreateCase' => false,
            'goodsOrPsv' => ['id' => RefData::LICENCE_CATEGORY_GOODS_VEHICLE],
            'vehicleType' => [
                'id' => RefData::APP_VEHICLE_TYPE_HGV
            ],
            'licenceType' => ['id' => 'xx'],
            'existingPublication' => true,
            'latestNote' => ['comment' => 'latest note'],
            'canHaveInspectionRequest' => true,
        ];

        $this->setupMockApplication(1066, $applicationData);

        $mockContainer = m::mock('Laminas\View\Helper\Placeholder\Container');
        $mockContainer->shouldReceive('set')->with($applicationData)->once();
        $mockContainer->shouldReceive('set')->with('latest note')->once();

        $mockPlaceholder = m::mock('Laminas\View\Helper\Placeholder');
        $mockPlaceholder->shouldReceive('getContainer')->with('application')->andReturn($mockContainer)->once();
        $mockPlaceholder->shouldReceive('getContainer')->with('note')->andReturn($mockContainer)->once();

        $mockViewHelperManager = m::mock('Laminas\View\HelperPluginManager');
        $mockViewHelperManager->shouldReceive('get')->with('placeholder')->andReturn($mockPlaceholder);
        $this->sut->setViewHelperManager($mockViewHelperManager);

        $mockNavigationService = m::mock('Laminas\Navigation\Navigation');
        $mockNavigationService->shouldReceive('findOneById')->andReturn(
            m::mock()->shouldReceive('setVisible')->getMock()
        );
        $this->sut->setNavigationService($mockNavigationService);

        $mockButton = m::mock();
        $mockButton->shouldReceive('setLabel')->with('Republish application')->once();
        $mockButton->shouldReceive('setVisible');

        $mockSidebar = m::mock();
        $mockSidebar->shouldReceive('findById')->andReturn($mockButton);
        $this->sut->setSidebarNavigationService($mockSidebar);

        $routeParam = new RouteParam();
        $routeParam->setValue(1066);
        $routeParam->setTarget(
            m::mock()->shouldReceive('trigger')->once()->with('licence', 99)->getMock()
        );

        $event = new Event(null, $routeParam);

        $this->sut->onApplication($event);
    }

    public function testSetupPublishApplicationButtonExistingPublicationFalse()
    {
        $applicationData = [
            'id' => 1066,
            'licence' => [
                'organisation' => [],
                'id' => 99,
            ],
            'status' => ['id' => 'xx'],
            's4s' => [],
            'isVariation' => false,
            'canCreateCase' => false,
            'goodsOrPsv' => ['id' => RefData::LICENCE_CATEGORY_GOODS_VEHICLE],
            'vehicleType' => [
                'id' => RefData::APP_VEHICLE_TYPE_HGV
            ],
            'licenceType' => ['id' => 'xx'],
            'existingPublication' => false,
            'latestNote' => ['comment' => 'latest note'],
            'canHaveInspectionRequest' => true,
        ];

        $this->setupMockApplication(1066, $applicationData);

        $mockContainer = m::mock('Laminas\View\Helper\Placeholder\Container');
        $mockContainer->shouldReceive('set')->with($applicationData)->once();
        $mockContainer->shouldReceive('set')->with('latest note')->once();

        $mockPlaceholder = m::mock('Laminas\View\Helper\Placeholder');
        $mockPlaceholder->shouldReceive('getContainer')->with('application')->andReturn($mockContainer)->once();
        $mockPlaceholder->shouldReceive('getContainer')->with('note')->andReturn($mockContainer)->once();

        $mockViewHelperManager = m::mock('Laminas\View\HelperPluginManager');
        $mockViewHelperManager->shouldReceive('get')->with('placeholder')->andReturn($mockPlaceholder);
        $this->sut->setViewHelperManager($mockViewHelperManager);

        $mockNavigationService = m::mock('Laminas\Navigation\Navigation');
        $mockNavigationService->shouldReceive('findOneById')->andReturn(
            m::mock()->shouldReceive('setVisible')->getMock()
        );
        $this->sut->setNavigationService($mockNavigationService);

        $mockButton = m::mock();
        $mockButton->shouldReceive('setVisible');

        $mockSidebar = m::mock();
        $mockSidebar->shouldReceive('findById')->andReturn($mockButton);
        $this->sut->setSidebarNavigationService($mockSidebar);

        $routeParam = new RouteParam();
        $routeParam->setValue(1066);
        $routeParam->setTarget(
            m::mock()->shouldReceive('trigger')->once()->with('licence', 99)->getMock()
        );

        $event = new Event(null, $routeParam);

        $this->sut->onApplication($event);
    }

    public function testSetupPublishApplicationButton()
    {
        $applicationData = [
            'id' => 1066,
            'licence' => [
                'organisation' => [],
                'id' => 99,
            ],
            'status' => ['id' => 'xx'],
            's4s' => [],
            'isVariation' => false,
            'canCreateCase' => false,
            'goodsOrPsv' => ['id' => RefData::LICENCE_CATEGORY_GOODS_VEHICLE],
            'vehicleType' => [
                'id' => RefData::APP_VEHICLE_TYPE_HGV
            ],
            'licenceType' => ['id' => 'xx'],
            'existingPublication' => false,
            'latestNote' => ['comment' => 'latest note'],
            'canHaveInspectionRequest' => true,
        ];

        $this->setupMockApplication(1066, $applicationData);

        $mockContainer = m::mock('Laminas\View\Helper\Placeholder\Container');
        $mockContainer->shouldReceive('set')->with($applicationData)->once();
        $mockContainer->shouldReceive('set')->with('latest note')->once();

        $mockPlaceholder = m::mock('Laminas\View\Helper\Placeholder');
        $mockPlaceholder->shouldReceive('getContainer')->with('application')->andReturn($mockContainer);
        $mockPlaceholder->shouldReceive('getContainer')->with('note')->andReturn($mockContainer)->once();

        $mockViewHelperManager = m::mock('Laminas\View\HelperPluginManager');
        $mockViewHelperManager->shouldReceive('get')->with('placeholder')->andReturn($mockPlaceholder);
        $this->sut->setViewHelperManager($mockViewHelperManager);

        $mockNavigationService = m::mock('Laminas\Navigation\Navigation');
        $mockNavigationService->shouldReceive('findOneById')->andReturn(
            m::mock()->shouldReceive('setVisible')->getMock()
        );
        $this->sut->setNavigationService($mockNavigationService);

        $mockSidebar = m::mock()->shouldReceive('findById')->andReturn(
            m::mock()->shouldReceive('setVisible')->getMock()
        )->getMock();
        $this->sut->setSidebarNavigationService($mockSidebar);

        $routeParam = new RouteParam();
        $routeParam->setValue(1066);
        $routeParam->setTarget(
            m::mock()->shouldReceive('trigger')->once()->with('licence', 99)->getMock()
        );

        $event = new Event(null, $routeParam);

        $this->sut->onApplication($event);
    }

    public function dataProviderSetupPublishApplicationButton()
    {
        return [
            // isVariation, status, isPublishable, goodsOrPsv, licenceType, isVisible
            [
                true,
                RefData::APPLICATION_STATUS_UNDER_CONSIDERATION,
                true,
                RefData::LICENCE_CATEGORY_GOODS_VEHICLE,
                true,
            ],
            [
                true,
                RefData::APPLICATION_STATUS_VALID,
                true,
                RefData::LICENCE_CATEGORY_GOODS_VEHICLE,
                false,
            ],
        ];
    }

    public function testInvoke()
    {
        $mockNavigationService = m::mock('Laminas\Navigation\Navigation');
        $mockViewHelperManager = m::mock('Laminas\View\HelperPluginManager');
        $mockSidebar = m::mock();
        $mockTransferAnnotationBuilder = m::mock();
        $mockQueryService = m::mock();
        $mockMarkerService = m::mock(MarkerService::class);
        $mockApplicationService = m::mock();

        $mockSl = m::mock(ContainerInterface::class);
        $mockSl->shouldReceive('get')->with('ViewHelperManager')->andReturn($mockViewHelperManager);
        $mockSl->shouldReceive('get')->with('navigation')->andReturn($mockNavigationService);
        $mockSl->shouldReceive('get')->with('right-sidebar')->andReturn($mockSidebar);
        $mockSl->shouldReceive('get')->with('TransferAnnotationBuilder')->andReturn($mockTransferAnnotationBuilder);
        $mockSl->shouldReceive('get')->with('QueryService')->andReturn($mockQueryService);
        $mockSl->shouldReceive('get')->with(MarkerService::class)->andReturn($mockMarkerService);
        $mockSl->shouldReceive('get')->with('Common\Service\Data\Application')->andReturn($mockApplicationService);

        $sut = new Application();
        $service = $sut->__invoke($mockSl, Application::class);

        $this->assertSame($sut, $service);
        $this->assertSame($mockNavigationService, $sut->getNavigationService());
        $this->assertSame($mockViewHelperManager, $sut->getViewHelperManager());
        $this->assertSame($mockTransferAnnotationBuilder, $sut->getAnnotationBuilder());
        $this->assertSame($mockQueryService, $sut->getQueryService());
        $this->assertSame($mockMarkerService, $sut->getMarkerService());
    }

    public function testOnApplicationNotFound()
    {
        $this->expectException(\Common\Exception\ResourceNotFoundException::class);

        $applicationId = 69;

        $routeParam = new RouteParam();
        $routeParam->setValue($applicationId);

        $event = new Event(null, $routeParam);

        $mockAnnotationBuilder = m::mock();
        $mockQueryService  = m::mock();

        $mockAnnotationBuilder->shouldReceive('createQuery')->once()->andReturnUsing(
            function ($dto) use ($applicationId) {
                $this->assertSame($applicationId, $dto->getId());
                return 'QUERY';
            }
        );

        $mockResult = m::mock();
        $mockResult->shouldReceive('isOk')->with()->once()->andReturn(false);

        $mockQueryService->shouldReceive('send')->with('QUERY')->once()->andReturn($mockResult);

        $this->sut->setAnnotationBuilder($mockAnnotationBuilder);
        $this->sut->setQueryService($mockQueryService);

        $this->sut->onApplication($event);
    }
}
