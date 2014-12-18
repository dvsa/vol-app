<?php

/**
 * Task controller tests
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace OlcsTest\Controller;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Controller\RouteParamInitializer;

/**
 * Task controller tests
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
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

        $sl = m::mock('\Zend\ServiceManager\ServiceManager');
        $sl->shouldReceive('getServiceLocator')->andReturnSelf();
        $sl->shouldReceive('get')->with('Config')->andReturn($config);
        $sl->shouldReceive('get')->with('RouteParamsListener')->andReturn(new \Olcs\Listener\RouteParams());
        $sl->shouldReceive('get')->with('Olcs\Listener\RouteParam\Cases')
            ->andReturn(new \Olcs\Listener\RouteParam\Cases());

        $eventManager = new \Zend\EventManager\EventManager();
        $instance = m::mock('Olcs\Controller\Interfaces\CaseControllerInterface');
        $instance->shouldReceive('getEventManager')->andReturn($eventManager);

        $initializer = new RouteParamInitializer();
        $initializer->initialize($instance, $sl);
    }
}
