<?php

namespace OlcsTest\Listener;

use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Mockery as m;
use Olcs\Event\RouteParam;
use Olcs\Listener\RouteParams;
use Laminas\Mvc\MvcEvent;

/**
 * Class RouteParamsTest
 * @package OlcsTest\Listener
 */
class RouteParamsTest extends TestCase
{
    public function testAttach()
    {
        $sut = new RouteParams();

        $mockEventManager = m::mock(\Laminas\EventManager\EventManagerInterface::class);
        $mockEventManager->shouldReceive('attach')->once()
            ->with(MvcEvent::EVENT_DISPATCH, [$sut, 'onDispatch'], 20);

        $sut->attach($mockEventManager);
    }

    public function testOnDispatch()
    {
        $params = ['test' => 'value'];

        $mockEvent = m::mock(\Laminas\Mvc\MvcEvent::class);
        $mockEvent->shouldReceive('getRouteMatch->getParams')->andReturn($params);

        $sut = new RouteParams();

        $matcher = function ($item) use ($params, $sut) {
            if (!($item instanceof RouteParam)) {
                return false;
            }
            if ($item->getValue() != 'value' || $item->getContext() != $params || $item->getTarget() != $sut) {
                return false;
            }

            return true;
        };

        $mockEventManager = m::mock(\Laminas\EventManager\EventManagerInterface::class);
        $mockEventManager->shouldIgnoreMissing();
        $mockEventManager->shouldReceive('trigger')->with(RouteParams::EVENT_PARAM . 'test', m::on($matcher))->once();

        $sut->setEventManager($mockEventManager);

        $sut->onDispatch($mockEvent);
    }

    public function testEventMatchedOnlyOnce()
    {
        $sut = new RouteParams();

        $event = 'event';
        $value = 'value';

        $sut->addTriggeredEvent($event);

        $sut->setParams([$event => $value]);

        $this->assertEquals(false, $sut->trigger($event, $value));
    }
}
