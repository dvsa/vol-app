<?php

declare(strict_types=1);

namespace OlcsTest\Listener\RouteParam;

use Laminas\EventManager\EventManagerInterface;
use Laminas\Router\RouteStackInterface;
use Laminas\View\Helper\Placeholder;
use Laminas\View\Helper\Placeholder\Container;
use Laminas\View\HelperPluginManager;
use Psr\Container\ContainerInterface;
use Laminas\EventManager\Event;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Olcs\Event\RouteParam;
use Olcs\Listener\RouteParam\Action;
use Mockery as m;
use Olcs\Listener\RouteParams;

class ActionTest extends TestCase
{
    private Action $sut;

    public function testAttach(): void
    {
        $this->sut = new Action();

        $mockEventManager = m::mock(EventManagerInterface::class);
        $mockEventManager->expects('attach')
            ->with(
                RouteParams::EVENT_PARAM . 'action',
                m::on(function ($listener) {
                    $rf = new \ReflectionFunction($listener);
                    return $rf->getClosureThis() === $this->sut && $rf->getName() === 'onAction';
                }),
                1
            );

        $this->sut->attach($mockEventManager);
    }

    #[\PHPUnit\Framework\Attributes\DoesNotPerformAssertions]
    public function testOnAction(): void
    {
        $action = 'add';

        $routeParam = new RouteParam();
        $routeParam->setValue($action);

        $event = new Event(null, $routeParam);

        $mockRouter = m::mock(RouteStackInterface::class);
        $mockRouter->shouldReceive('assemble')
            ->with(['action' => $action])
            ->andReturn('http://anything/');

        $mockContainer = m::mock(Container::class);
        $mockContainer->shouldReceive('set')->with($action);

        $mockPlaceholder = m::mock(Placeholder::class);
        $mockPlaceholder->shouldReceive('getContainer')->with('action')->andReturn($mockContainer);

        $mockViewHelperManager = m::mock(HelperPluginManager::class);
        $mockViewHelperManager->shouldReceive('get')->with('placeholder')->andReturn($mockPlaceholder);

        $sut = new Action();
        $sut->setViewHelperManager($mockViewHelperManager);

        $sut->onAction($event);
    }

    public function testInvoke(): void
    {
        $mockViewHelperManager = m::mock(HelperPluginManager::class);

        $mockSl = m::mock(ContainerInterface::class);
        $mockSl->shouldReceive('get')->with('ViewHelperManager')->andReturn($mockViewHelperManager);

        $sut = new Action();
        $service = $sut->__invoke($mockSl, Action::class);

        $this->assertSame($sut, $service);
        $this->assertSame($mockViewHelperManager, $sut->getViewHelperManager());
    }
}
