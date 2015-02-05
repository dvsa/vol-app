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

        $mockCaseListener = m::mock('Olcs\Listener\RouteParam\Cases');
        $mockHeaderSearchListener = m::mock('Olcs\Listener\HeaderSearch');

        $mockEm = m::mock('Zend\EventManager\EventManager');

        $mockEm->shouldReceive('attach')->with($mockCaseListener);

        $mockListener = m::mock('Olcs\Listener\RouteParams');
        $mockListener->shouldReceive('getEventManager')->andReturn($mockEm);

        $sl = m::mock('\Zend\ServiceManager\ServiceManager');
        $sl->shouldReceive('getServiceLocator')->andReturnSelf();
        $sl->shouldReceive('get')->with('Config')->andReturn($config);
        $sl->shouldReceive('get')->with('RouteParamsListener')->andReturn($mockListener);
        $sl->shouldReceive('get')->with('HeaderSearchListener')->andReturn($mockHeaderSearchListener);
        $sl->shouldReceive('get')->once()->with('Olcs\Listener\RouteParam\Cases')
            ->andReturn($mockCaseListener);

        $mockEm2 = m::mock('Zend\EventManager\EventManager');
        $mockEm2->shouldReceive('attach')->with($mockListener);
        $mockEm2->shouldReceive('attach')->with($mockHeaderSearchListener);

        $instance = m::mock('Olcs\Controller\Interfaces\CaseControllerInterface');
        $instance->shouldReceive('getEventManager')->andReturn($mockEm2);

        $initializer = new RouteParamInitializer();
        $initializer->initialize($instance, $sl);
    }
}
