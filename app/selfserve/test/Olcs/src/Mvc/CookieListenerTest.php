<?php

declare(strict_types=1);

namespace OlcsTest\Mvc;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Mvc\CookieListener;
use Olcs\Service\Cookie\CookieReader;
use Olcs\Service\Cookie\CookieState;
use Olcs\Service\Cookie\Preferences;
use Laminas\EventManager\EventManagerInterface;
use Laminas\Http\Header\Cookie;
use Laminas\Http\Request;
use Laminas\Mvc\MvcEvent;
use Laminas\View\Helper\Placeholder;
use Laminas\View\Helper\Placeholder\Container;

class CookieListenerTest extends MockeryTestCase
{
    /**
     * @var CookieListener
     */
    protected $sut;

    protected $cookieReader;

    protected $placeholder;

    public function setUp(): void
    {
        $this->cookieReader = m::mock(CookieReader::class);
        $this->placeholder = m::mock(Placeholder::class);

        $this->sut = new CookieListener($this->cookieReader, $this->placeholder);
    }

    public function testAttach(): void
    {
        $em = m::mock(EventManagerInterface::class);
        $em->expects('attach')
            ->with(
                MvcEvent::EVENT_ROUTE,
                m::on(function ($listener) {
                    $rf = new \ReflectionFunction($listener);
                    return $rf->getClosureThis() === $this->sut && $rf->getName() === 'onRoute';
                }),
                1
            );

        $this->sut->attach($em);
    }

    public function testOnRouteNonHttp(): void
    {
        $request = m::mock();

        $event = m::mock(MvcEvent::class);
        $event->shouldReceive('getRequest')->andReturn($request);

        $this->cookieReader->shouldReceive('getState')->never();
        $this->placeholder->shouldReceive('getContainer')->with('cookieAnalytics')->never();

        $this->sut->onRoute($event);
    }

    public function testOnRoute(): void
    {
        $isActive = true;

        $cookie = m::mock(Cookie::class);

        $request = m::mock(Request::class);
        $request->shouldReceive('getCookie')->andReturn($cookie);

        $event = m::mock(MvcEvent::class);
        $event->shouldReceive('getRequest')->andReturn($request);

        $cookieState = m::mock(CookieState::class);
        $cookieState->shouldReceive('isActive')->with(Preferences::KEY_ANALYTICS)->once()->andReturn($isActive);

        $this->cookieReader->shouldReceive('getState')->once()->andReturn($cookieState);

        $container = m::mock(Container::class);
        $container->shouldReceive('set')->with($isActive)->once();

        $this->placeholder->shouldReceive('getContainer')->with('cookieAnalytics')->once()->andReturn($container);

        $this->sut->onRoute($event);
    }
}
