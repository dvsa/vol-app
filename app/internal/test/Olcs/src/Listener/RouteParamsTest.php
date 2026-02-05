<?php

declare(strict_types=1);

namespace OlcsTest\Listener;

use Laminas\EventManager\EventManagerInterface;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Mockery as m;
use Olcs\Event\RouteParam;
use Olcs\Listener\RouteParams;
use Laminas\Mvc\MvcEvent;

class RouteParamsTest extends TestCase
{
    private RouteParams $sut;

    public function testAttach(): void
    {
        $this->sut = new RouteParams();

        $mockEventManager = m::mock(EventManagerInterface::class);
        $mockEventManager->expects('attach')
            ->with(
                MvcEvent::EVENT_DISPATCH,
                m::on(function ($listener) {
                    $rf = new \ReflectionFunction($listener);
                    return $rf->getClosureThis() === $this->sut && $rf->getName() === 'onDispatch';
                }),
                20
            );

        $this->sut->attach($mockEventManager);
    }

    public function testOnDispatch(): void
    {
        $params = ['test' => 'value'];

        $mockEvent = m::mock(MvcEvent::class);
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

        $mockEventManager = m::mock(EventManagerInterface::class);
        $mockEventManager->shouldIgnoreMissing();
        $mockEventManager->shouldReceive('trigger')->with(RouteParams::EVENT_PARAM . 'test', m::on($matcher))->once();

        $sut->setEventManager($mockEventManager);

        $sut->onDispatch($mockEvent);
    }

    public function testEventMatchedOnlyOnce(): void
    {
        $sut = new RouteParams();

        $event = 'event';
        $value = 'value';

        $sut->addTriggeredEvent($event);

        $sut->setParams([$event => $value]);

        $this->assertEquals(false, $sut->trigger($event, $value));
    }
}
