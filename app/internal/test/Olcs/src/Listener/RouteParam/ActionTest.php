<?php

namespace OlcsTest\Listener\RouteParam;

use Interop\Container\ContainerInterface;
use Laminas\EventManager\Event;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Olcs\Event\RouteParam;
use Olcs\Listener\RouteParam\Action;
use Mockery as m;
use Olcs\Listener\RouteParams;

class ActionTest extends TestCase
{
    public function testAttach()
    {
        $sut = new Action();

        $mockEventManager = m::mock('Laminas\EventManager\EventManagerInterface');
        $mockEventManager->shouldReceive('attach')->once()
            ->with(RouteParams::EVENT_PARAM . 'action', [$sut, 'onAction'], 1);

        $sut->attach($mockEventManager);
    }

    public function testOnAction()
    {
        $action = 'add';

        $routeParam = new RouteParam();
        $routeParam->setValue($action);

        $event = new Event(null, $routeParam);

        $mockRouter = m::mock('Laminas\Router\RouteStackInterface');
        $mockRouter->shouldReceive('assemble')
            ->with(['action' => $action])
            ->andReturn('http://anything/');

        $mockContainer = m::mock('Laminas\View\Helper\Placeholder\Container');
        $mockContainer->shouldReceive('set')->with($action);

        $mockPlaceholder = m::mock('Laminas\View\Helper\Placeholder');
        $mockPlaceholder->shouldReceive('getContainer')->with('action')->andReturn($mockContainer);

        $mockViewHelperManager = m::mock('Laminas\View\HelperPluginManager');
        $mockViewHelperManager->shouldReceive('get')->with('placeholder')->andReturn($mockPlaceholder);

        $sut = new Action();
        $sut->setViewHelperManager($mockViewHelperManager);

        $sut->onAction($event);
    }

    public function testInvoke()
    {
        $mockViewHelperManager = m::mock('Laminas\View\HelperPluginManager');

        $mockSl = m::mock(ContainerInterface::class);
        $mockSl->shouldReceive('get')->with('ViewHelperManager')->andReturn($mockViewHelperManager);

        $sut = new Action();
        $service = $sut->__invoke($mockSl, Action::class);

        $this->assertSame($sut, $service);
        $this->assertSame($mockViewHelperManager, $sut->getViewHelperManager());
    }
}
