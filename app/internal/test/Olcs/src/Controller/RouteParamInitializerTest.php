<?php
namespace OlcsTest\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Controller\RouteParamInitializer;
use Zend\ServiceManager\ServiceManager;
use Mockery as m;

/**
 * Task controller tests
 */
class RouteParamInitializerTest extends MockeryTestCase
{
    public function testInitialize()
    {
        $config = [
            'route_param_listeners' => [
                'Olcs\Controller\Interfaces\CaseControllerInterface' => [
                    'Olcs\Listener\RouteParam\Cases',
                ]
            ]
        ];

        $mockCaseListener = m::mock('Olcs\Listener\RouteParam\Cases');
        $mockHeaderSearchListener = m::mock('Olcs\Listener\HeaderSearch');
        $mockNavigationToggleListener = m::mock('Olcs\Listener\NavigationToggle');

        $mockEm = m::mock('Zend\EventManager\EventManager');
        $mockEm->shouldReceive('attach')->with($mockCaseListener);

        $mockListener = m::mock('Olcs\Listener\RouteParams');
        $mockListener->shouldReceive('getEventManager')->andReturn($mockEm);

        /** @var ServiceManager|m\mock $sl */
        $sl = m::mock('\Zend\ServiceManager\ServiceManager');
        $sl->shouldReceive('getServiceLocator')->andReturnSelf();
        $sl->shouldReceive('get')->with('Config')->andReturn($config);
        $sl->shouldReceive('get')->with('RouteParamsListener')->andReturn($mockListener);
        $sl->shouldReceive('get')->with('HeaderSearchListener')->andReturn($mockHeaderSearchListener);
        $sl->shouldReceive('get')->with('NavigationToggleListener')->andReturn($mockNavigationToggleListener);
        $sl->shouldReceive('get')->once()->with('Olcs\Listener\RouteParam\Cases')
            ->andReturn($mockCaseListener);

        $mockEm2 = m::mock('Zend\EventManager\EventManager');
        $mockEm2->shouldReceive('attach')->with($mockListener);
        $mockEm2->shouldReceive('attach')->with($mockHeaderSearchListener);
        $mockEm2->shouldreceive('attach')->with($mockNavigationToggleListener);

        /** @var AbstractActionController|m\mock $instance */
        $instance = m::mock('Olcs\Controller\Interfaces\CaseControllerInterface');
        $instance->shouldReceive('getEventManager')->andReturn($mockEm2);

        $initializer = new RouteParamInitializer();
        $initializer->initialize($instance, $sl);
    }
}
