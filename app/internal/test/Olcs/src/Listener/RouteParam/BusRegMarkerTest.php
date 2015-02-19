<?php

namespace OlcsTest\Listener\RouteParam;

use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Olcs\Event\RouteParam;
use Olcs\Listener\RouteParam\BusRegMarker;
use Mockery as m;
use Olcs\Listener\RouteParams;

/**
 * Class BusRegMarkerTest
 * @package OlcsTest\Listener\RouteParam
 */
class BusRegMarkerTest extends TestCase
{
    public function testAttach()
    {
        $sut = new BusRegMarker();

        $mockEventManager = m::mock('Zend\EventManager\EventManagerInterface');
        $mockEventManager->shouldReceive('attach')->once()
            ->with(RouteParams::EVENT_PARAM . 'busRegId', [$sut, 'onBusRegMarker'], 1);

        $sut->attach($mockEventManager);
    }

    public function testOnBusRegMarker()
    {
        $busRegId = 1;
        $busReg = ['id' => $busRegId];
        $markers = ['busReg' => 'BusReg marker'];

        $event = new RouteParam();
        $event->setValue($busRegId);

        $mockBusRegService = m::mock('Common\Service\Data\BusReg');
        $mockBusRegService->shouldReceive('fetchOne')->with($busRegId)->andReturn($busReg);

        $mockContainer = m::mock('Zend\View\Helper\Placeholder\Container');
        $mockContainer->shouldReceive('set')->with($markers);

        $mockPlaceholder = m::mock('Zend\View\Helper\Placeholder');
        $mockPlaceholder->shouldReceive('getContainer')->with('markers')->andReturn($mockContainer);

        $mockViewHelperManager = m::mock('Zend\ServiceManager\ServiceLocatorInterface');
        $mockViewHelperManager->shouldReceive('get')->with('placeholder')->andReturn($mockPlaceholder);

        $mockBusRegMarkerService = m::mock('Olcs\Service\Marker\BusRegMarkers');
        $mockBusRegMarkerService->shouldReceive('generateMarkerTypes')
            ->with(['busReg'], ['busReg' => $busReg])
            ->andReturn($markers);

        $sut = new BusRegMarker();
        $sut->setViewHelperManager($mockViewHelperManager);
        $sut->setBusRegMarkerService($mockBusRegMarkerService);
        $sut->setBusRegService($mockBusRegService);

        $sut->onBusRegMarker($event);
    }

    public function testCreateService()
    {
        $mockBusRegService = m::mock('Common\Service\Data\BusReg');
        $mockBusRegMarkerService = m::mock('Olcs\Service\Marker\BusRegMarkers');
        $mockViewHelperManager = m::mock('Zend\ServiceManager\ServiceLocatorInterface');

        $mockSl = m::mock('Zend\ServiceManager\ServiceLocatorInterface');
        $mockSl->shouldReceive('get')->with('ViewHelperManager')->andReturn($mockViewHelperManager);
        $mockSl->shouldReceive('get')->with('DataServiceManager')->andReturnSelf();
        $mockSl->shouldReceive('get')->with('Olcs\Service\Marker\MarkerPluginManager')->andReturnSelf();
        $mockSl->shouldReceive('get')->with('Common\Service\Data\BusReg')->andReturn($mockBusRegService);
        $mockSl->shouldReceive('get')->with('Olcs\Service\Marker\BusRegMarkers')->andReturn($mockBusRegMarkerService);

        $sut = new BusRegMarker();
        $service = $sut->createService($mockSl);

        $this->assertSame($sut, $service);
        $this->assertSame($mockBusRegService, $sut->getBusRegService());
        $this->assertSame($mockBusRegMarkerService, $sut->getBusRegMarkerService());
        $this->assertSame($mockViewHelperManager, $sut->getViewHelperManager());
    }
}
