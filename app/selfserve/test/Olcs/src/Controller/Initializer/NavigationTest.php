<?php
namespace OlcsTest\Controller\Initializer;

use Common\Controller\AbstractOlcsController;
use Dvsa\Olcs\Auth\Controller\LoginController;
use Olcs\Controller\Initializer\Navigation as NavigationInitializer;
use Olcs\Controller\Listener\Navigation as NavigationListener;
use Laminas\EventManager\EventManager as LaminasEventManager;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\ServiceManager\ServiceManager;
use Mockery as m;

/**
 * Navigation initializer test
 */
class NavigationTest extends m\Adapter\Phpunit\MockeryTestCase
{
    public function testInitialize()
    {
        $navListener = m::mock(NavigationListener::class);
        $mockEventManager = m::mock(LaminasEventManager::class);
        $mockEventManager->shouldReceive('attach')->once()->with($navListener);

        /** @var ServiceManager|m\mock $sl */
        $sl = m::mock(ServiceLocatorInterface::class);
        $sl->shouldReceive('getServiceLocator->get')->with(NavigationListener::class)->andReturn($navListener);

        //this could be any controller or controller interface
        $instance = m::mock(AbstractOlcsController::class);
        $instance->shouldReceive('getEventManager')->andReturn($mockEventManager);

        $initializer = new NavigationInitializer();
        $initializer->initialize($instance, $sl);
    }

    /**
     * Check the initializer doesn't try to attach the nav listener on the login page
     */
    public function testInitializerFromLoginPage()
    {
        $instance = m::mock(LoginController::class);
        $instance->shouldNotReceive('getEventManager->attach');
        $sl = m::mock(ServiceLocatorInterface::class);
        $sl->shouldNotReceive('getServiceLocator->get')->with(NavigationListener::class);

        $initializer = new NavigationInitializer();
        $initializer->initialize($instance, $sl);
    }
}
