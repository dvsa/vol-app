<?php

declare(strict_types=1);

namespace OlcsTest\Controller\Initializer;

use Common\Controller\AbstractOlcsController;
use Olcs\Controller\Auth\LoginController;
use Psr\Container\ContainerInterface;
use Laminas\Mvc\MvcEvent;
use Olcs\Controller\Initializer\Navigation as NavigationInitializer;
use Olcs\Controller\Listener\Navigation as NavigationListener;
use Laminas\EventManager\EventManager as LaminasEventManager;
use Mockery as m;

/**
 * Navigation initializer test
 */
class NavigationTest extends m\Adapter\Phpunit\MockeryTestCase
{
    public function testInvoke(): void
    {
        $navListener = m::mock(NavigationListener::class);
        $mockEventManager = m::mock(LaminasEventManager::class);
        $mockEventManager->expects('attach')->with(MvcEvent::EVENT_DISPATCH, [$navListener, 'onDispatch']);

        $container = m::mock(ContainerInterface::class);
        $container->expects('get')->with(NavigationListener::class)->andReturn($navListener);

        //this could be any controller or controller interface
        $instance = m::mock(AbstractOlcsController::class);
        $instance->expects('getEventManager')->andReturn($mockEventManager);

        $initializer = new NavigationInitializer();
        $initializer($container, $instance);
    }

    /**
     * Check the initializer doesn't try to attach the nav listener on the login page
     */
    public function testInvokeFromLoginPage(): void
    {
        $instance = m::mock(LoginController::class);
        $instance->shouldNotReceive('getEventManager->attach');
        $container = m::mock(ContainerInterface::class);
        $container->shouldNotReceive('get');

        $initializer = new NavigationInitializer();
        $initializer($container, $instance);
    }
}
