<?php

namespace OlcsTest\Listener\RouteParam;

use Common\RefData;
use OlcsTest\Bootstrap;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;
use Olcs\Event\RouteParam;
use Olcs\Listener\RouteParams;
use Olcs\Listener\RouteParam\Application;
use Common\Service\Entity\LicenceEntityService;
use Common\Service\Entity\ApplicationEntityService;

/**
 * Class ApplicationTest
 * @package OlcsTest\Listener\RouteParam
 */
class ApplicationTest extends MockeryTestCase
{
    public function setUp()
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

        $this->sut->setAnnotationBuilder($mockAnnotationBuilder);
        $this->sut->setQueryService($mockQueryService);
        $this->sut->setMarkerService($mockMarkerService);
    }

    public function testAttach()
    {
        $sut = new Application();

        $mockEventManager = m::mock('Zend\EventManager\EventManagerInterface');
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
            ]
        ];

        $quickViewActionsVisible = ($status !== ApplicationEntityService::APPLICATION_STATUS_VALID);

        $event = new RouteParam();
        $event->setValue($applicationId);

        $mockApplicationCaseNavigationService = m::mock('\StdClass');
        $mockApplicationCaseNavigationService->shouldReceive('setVisible')->times($expectedCallsNo)->with(false);

        $mockNavigationService = m::mock('Zend\Navigation\Navigation');
        $mockNavigationService->shouldReceive('findOneById')
            ->with('application_case')->andReturn($mockApplicationCaseNavigationService);

        $this->setupMockApplication($applicationId, $application);

        $mockContainer = m::mock('Zend\View\Helper\Placeholder\Container');
        $mockContainer->shouldReceive('set')->with($application);

        $mockPlaceholder = m::mock('Zend\View\Helper\Placeholder');
        $mockPlaceholder->shouldReceive('getContainer')->with('application')->andReturn($mockContainer);

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
                ApplicationEntityService::APPLICATION_STATUS_UNDER_CONSIDERATION,
                LicenceEntityService::LICENCE_CATEGORY_GOODS_VEHICLE,
                ApplicationEntityService::APPLICATION_TYPE_NEW,
                true,
                0
            ],
            [
                ApplicationEntityService::APPLICATION_STATUS_GRANTED,
                LicenceEntityService::LICENCE_CATEGORY_GOODS_VEHICLE,
                ApplicationEntityService::APPLICATION_TYPE_NEW,
                false,
                1
            ],
            [
                ApplicationEntityService::APPLICATION_STATUS_GRANTED,
                LicenceEntityService::LICENCE_CATEGORY_GOODS_VEHICLE,
                ApplicationEntityService::APPLICATION_TYPE_VARIATION,
                false,
                1
            ],
            [
                ApplicationEntityService::APPLICATION_STATUS_VALID,
                LicenceEntityService::LICENCE_CATEGORY_GOODS_VEHICLE,
                ApplicationEntityService::APPLICATION_TYPE_VARIATION,
                false,
                1
            ],
        ];
    }

    public function testCreateService()
    {
        $mockNavigationService = m::mock('Zend\Navigation\Navigation');
        $mockViewHelperManager = m::mock('Zend\View\HelperPluginManager');
        $mockSidebar = m::mock();
        $mockTransferAnnotationBuilder = m::mock();
        $mockQueryService = m::mock();
        $mockMarkerService = m::mock(\Olcs\Service\Marker\MarkerService::class);

        $mockSl = m::mock('Zend\ServiceManager\ServiceLocatorInterface');
        $mockSl->shouldReceive('get')->with('ViewHelperManager')->andReturn($mockViewHelperManager);
        $mockSl->shouldReceive('get')->with('Navigation')->andReturn($mockNavigationService);
        $mockSl->shouldReceive('get')->with('right-sidebar')->andReturn($mockSidebar);
        $mockSl->shouldReceive('get')->with('TransferAnnotationBuilder')->andReturn($mockTransferAnnotationBuilder);
        $mockSl->shouldReceive('get')->with('QueryService')->andReturn($mockQueryService);
        $mockSl->shouldReceive('get')->with(\Olcs\Service\Marker\MarkerService::class)->andReturn($mockMarkerService);

        $sut = new Application();
        $service = $sut->createService($mockSl);

        $this->assertSame($sut, $service);
        $this->assertSame($mockNavigationService, $sut->getNavigationService());
        $this->assertSame($mockViewHelperManager, $sut->getViewHelperManager());
        $this->assertSame($mockTransferAnnotationBuilder, $sut->getAnnotationBuilder());
        $this->assertSame($mockQueryService, $sut->getQueryService());
        $this->assertSame($mockMarkerService, $sut->getMarkerService());
    }

    /**
     * @expectedException \Common\Exception\ResourceNotFoundException
     */
    public function testOnApplicationNotFound()
    {
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
