<?php

namespace OlcsTest\Listener\RouteParam;

use OlcsTest\Bootstrap;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;
use Olcs\Event\RouteParam;
use Olcs\Listener\RouteParam\Application;
use Common\Service\Entity\LicenceEntityService;
use Common\Service\Entity\ApplicationEntityService;

/**
 * Class ApplicationTest
 * @package OlcsTest\Listener\RouteParam
 */
class ApplicationTest extends MockeryTestCase
{
    /**
     * @dataProvider onApplicationProvider
     * @param string $status
     * @param string $category
     */
    public function testOnApplication($status, $category, $type)
    {
        $applicationId = 69;
        $application = [
            'id' => $applicationId,
            'status' => [
                'id' => $status
            ],
        ];

        $event = new RouteParam();
        $event->setValue($applicationId);

        $mockApplicationService = m::mock('Common\Service\Data\Application');
        $mockApplicationService->shouldReceive('get')->with($applicationId)->andReturn($application);
        $mockApplicationService->shouldReceive('setId')->with($applicationId);

        $mockContainer = m::mock('Zend\View\Helper\Placeholder\Container');
        $mockContainer->shouldReceive('set')->with($application);

        $mockPlaceholder = m::mock('Zend\View\Helper\Placeholder');
        $mockPlaceholder->shouldReceive('getContainer')->with('application')->andReturn($mockContainer);

        $mockViewHelperManager = m::mock('Zend\View\HelperPluginManager');
        $mockViewHelperManager->shouldReceive('get')->with('placeholder')->andReturn($mockPlaceholder);

        $mockSidebar = m::mock()
            ->shouldReceive('findById')
            ->andReturn(
                m::mock()
                ->shouldReceive('setVisible')
                ->getMock()
            )
            ->getMock();

        $mockApplicationEntityService = m::mock()
            ->shouldReceive('getStatus')
                ->with($applicationId)
                ->once()
                ->andReturn($status)
            ->shouldReceive('getApplicationType')
                ->with($applicationId)
                ->andReturn($type)
            ->shouldReceive('getCategory')
                ->with($applicationId)
                ->andReturn($category)
            ->getMock();

        $sm = Bootstrap::getServiceManager();
        $sm->setService('right-sidebar', $mockSidebar);
        $sm->setService('Entity\Application', $mockApplicationEntityService);

        $sut = new Application();
        $sut->setApplicationService($mockApplicationService);
        $sut->setViewHelperManager($mockViewHelperManager);
        $sut->setServiceLocator($sm);
        $sut->onApplication($event);
    }

    public function onApplicationProvider()
    {
        return [
            [
                ApplicationEntityService::APPLICATION_STATUS_UNDER_CONSIDERATION,
                LicenceEntityService::LICENCE_CATEGORY_GOODS_VEHICLE,
                ApplicationEntityService::APPLICATION_TYPE_NEW,
            ],
            [
                ApplicationEntityService::APPLICATION_STATUS_GRANTED,
                LicenceEntityService::LICENCE_CATEGORY_GOODS_VEHICLE,
                ApplicationEntityService::APPLICATION_TYPE_NEW,
            ],
            [
                ApplicationEntityService::APPLICATION_STATUS_GRANTED,
                LicenceEntityService::LICENCE_CATEGORY_GOODS_VEHICLE,
                ApplicationEntityService::APPLICATION_TYPE_VARIATION,
            ],

        ];
    }
}
