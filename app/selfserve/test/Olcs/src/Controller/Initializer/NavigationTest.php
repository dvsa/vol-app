<?php
namespace OlcsTest\Controller\Initializer;

use Common\Controller\AbstractOlcsController;
use Olcs\Controller\Initializer\Navigation as NavigationInitializer;
use Olcs\Controller\Listener\Navigation as NavigationListener;
use Zend\EventManager\EventManager as ZendEventManager;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceManager;
use Mockery as m;

/**
 * Navigation initializer test
 */
class NavigationTest extends m\Adapter\Phpunit\MockeryTestCase
{
    public function testInitialize()
    {
        $navListener = m::mock(NavigationListener::class);
        $mockEventManager = m::mock(ZendEventManager::class);
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
}
