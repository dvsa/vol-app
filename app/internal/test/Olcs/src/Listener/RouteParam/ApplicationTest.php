<?php

namespace OlcsTest\Listener\RouteParam;

use Common\RefData;
use OlcsTest\Bootstrap;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;
use Olcs\Event\RouteParam;
use Olcs\Listener\RouteParams;
use Olcs\Listener\RouteParam\Application;
use Zend\Navigation\Page\AbstractPage;

/**
 * Class ApplicationTest
 * @package OlcsTest\Listener\RouteParam
 */
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

        $mockMarkerService = m::mock(\Olcs\Service\Marker\MarkerService::class);
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

        $event = new RouteParam();
        $event->setValue($applicationId);
        $event->setTarget(
            m::mock()
            ->shouldReceive('trigger')->once()->with('licence', 101)
            ->getMock()
        );

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

        $event = new RouteParam();
        $event->setValue($applicationId);
        $event->setTarget(
            m::mock()
            ->shouldReceive('trigger')->once()->with('licence', 101)
            ->getMock()
        );

        $mockApplicationCaseNavigationService = m::mock('\StdClass');
        $mockApplicationCaseNavigationService->shouldReceive('setVisible')->times($expectedCallsNo)->with(false);

        $mockNavigationService = m::mock('Zend\Navigation\Navigation');
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
            ->andReturn('page.label');
        $variationCommunityLicencesPage->shouldReceive('setLabel')
            ->with('page.label.psv')
            ->once();

        $mockNavigationService->shouldReceive('findOneById')
            ->with('variation_community_licences')
            ->andReturn($variationCommunityLicencesPage);

        $this->setupMockApplication($applicationId, $application);

        $mockContainer = m::mock('Zend\View\Helper\Placeholder\Container');
        $mockContainer->shouldReceive('set')->with($application)->once();
        $mockContainer->shouldReceive('set')->with('latest note')->once();

        $mockPlaceholder = m::mock('Zend\View\Helper\Placeholder');
        $mockPlaceholder->shouldReceive('getContainer')->with('application')->andReturn($mockContainer)->once();
        $mockPlaceholder->shouldReceive('getContainer')->with('note')->andReturn($mockContainer)->once();

        $mockViewHelperManager = m::mock('Zend\View\HelperPluginManager');
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

        $event = new RouteParam();
        $event->setValue(1066);
        $event->setTarget(
            m::mock()->shouldReceive('trigger')->once()->with('licence', 99)->getMock()
        );

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

        $event = new RouteParam();
        $event->setValue(1066);
        $event->setTarget(
            m::mock()->shouldReceive('trigger')->once()->with('licence', 99)->getMock()
        );

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

        $event = new RouteParam();
        $event->setValue(1066);
        $event->setTarget(
            m::mock()->shouldReceive('trigger')->once()->with('licence', 99)->getMock()
        );

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

    public function testCreateService()
    {
        $mockNavigationService = m::mock('Laminas\Navigation\Navigation');
        $mockViewHelperManager = m::mock('Laminas\View\HelperPluginManager');
        $mockSidebar = m::mock();
        $mockTransferAnnotationBuilder = m::mock();
        $mockQueryService = m::mock();
        $mockMarkerService = m::mock(\Olcs\Service\Marker\MarkerService::class);
        $mockApplicationService = m::mock();

        $mockSl = m::mock('Laminas\ServiceManager\ServiceLocatorInterface');
        $mockSl->shouldReceive('get')->with('ViewHelperManager')->andReturn($mockViewHelperManager);
        $mockSl->shouldReceive('get')->with('Navigation')->andReturn($mockNavigationService);
        $mockSl->shouldReceive('get')->with('right-sidebar')->andReturn($mockSidebar);
        $mockSl->shouldReceive('get')->with('TransferAnnotationBuilder')->andReturn($mockTransferAnnotationBuilder);
        $mockSl->shouldReceive('get')->with('QueryService')->andReturn($mockQueryService);
        $mockSl->shouldReceive('get')->with(\Olcs\Service\Marker\MarkerService::class)->andReturn($mockMarkerService);
        $mockSl->shouldReceive('get')->with('Common\Service\Data\Application')->andReturn($mockApplicationService);

        $sut = new Application();
        $service = $sut->createService($mockSl);

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

        $event = new RouteParam();
        $event->setValue($applicationId);

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
