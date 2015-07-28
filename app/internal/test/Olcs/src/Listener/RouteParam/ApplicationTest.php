<?php

namespace OlcsTest\Listener\RouteParam;

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
            's4s' => []
        ];

        $quickViewActionsVisible = ($status !== ApplicationEntityService::APPLICATION_STATUS_VALID);

        $event = new RouteParam();
        $event->setValue($applicationId);

        $mockApplicationCaseNavigationService = m::mock('\StdClass');
        $mockApplicationCaseNavigationService->shouldReceive('setVisible')->times($expectedCallsNo)->with(false);

        $mockNavigationService = m::mock('Zend\Navigation\Navigation');
        $mockNavigationService->shouldReceive('findOneById')
            ->with('application_case')->andReturn($mockApplicationCaseNavigationService);

        $mockApplicationService = m::mock('Common\Service\Data\Application');
        $mockApplicationService->shouldReceive('fetchData')
            ->with(
                $applicationId,
                [
                    'children' => [
                        'licence',
                        'status',
                        's4s'
                    ]
                ]
            )->andReturn($application);

        $mockApplicationService->shouldReceive('canHaveCases')->with($applicationId)->andReturn($canHaveCases);
        $mockApplicationService->shouldReceive('setId')->with($applicationId);

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

        $mockApplicationEntityService = m::mock()
            ->shouldReceive('getApplicationType')
                ->with($applicationId)
                ->andReturn($type)
            ->shouldReceive('getCategory')
                ->with($applicationId)
                ->andReturn($category)
            ->getMock();

        $sut = new Application();
        $sut->setApplicationService($mockApplicationService);
        $sut->setViewHelperManager($mockViewHelperManager);
        $sut->setNavigationService($mockNavigationService);
        $sut->setSidebarNavigationService($mockSidebar);
        $sut->setApplicationEntityService($mockApplicationEntityService);

        $sut->onApplication($event);
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
        $mockApplicationService = m::mock('Common\Service\Data\Application');
        $mockNavigationService = m::mock('Zend\Navigation\Navigation');
        $mockViewHelperManager = m::mock('Zend\View\HelperPluginManager');
        $mockApplicationEntityService = m::mock('Common\Service\Entity\ApplicationEntityService');
        $mockSidebar = m::mock();

        $mockSl = m::mock('Zend\ServiceManager\ServiceLocatorInterface');
        $mockSl->shouldReceive('get')->with('ViewHelperManager')->andReturn($mockViewHelperManager);
        $mockSl->shouldReceive('get')->with('DataServiceManager')->andReturnSelf();
        $mockSl->shouldReceive('get')->with('Common\Service\Data\Application')->andReturn($mockApplicationService);
        $mockSl->shouldReceive('get')->with('Navigation')->andReturn($mockNavigationService);
        $mockSl->shouldReceive('get')->with('right-sidebar')->andReturn($mockSidebar);
        $mockSl->shouldReceive('get')->with('Entity\Application')->andReturn($mockApplicationEntityService);

        $sut = new Application();
        $service = $sut->createService($mockSl);

        $this->assertSame($sut, $service);
        $this->assertSame($mockApplicationService, $sut->getApplicationService());
        $this->assertSame($mockNavigationService, $sut->getNavigationService());
        $this->assertSame($mockViewHelperManager, $sut->getViewHelperManager());
        $this->assertSame($mockApplicationEntityService, $sut->getApplicationEntityService());
        $this->assertSame($mockSidebar, $sut->getSidebarNavigationService());
    }

    /**
     * @dataProvider applicationNotFoundProvider
     * @expectedException \Common\Exception\ResourceNotFoundException
     */
    public function testOnApplicationNotFound($applicationData)
    {
        $applicationId = 69;

        $event = new RouteParam();
        $event->setValue($applicationId);

        $mockApplicationService = m::mock('Common\Service\Data\Application');
        $mockApplicationService->shouldReceive('setId')->with($applicationId);
        $mockApplicationService->shouldReceive('fetchData')
            ->with(
                $applicationId,
                [
                    'children' => [
                        'licence',
                        'status',
                        's4s'
                    ]
                ]
            )
            ->andReturn($applicationData);

        $sut = new Application();
        $sut->setApplicationService($mockApplicationService);
        $sut->onApplication($event);
    }

    public function applicationNotFoundProvider()
    {
        return [
            [false],
            [null],
        ];
    }
}
