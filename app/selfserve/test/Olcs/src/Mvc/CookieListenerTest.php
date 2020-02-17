<?php

/**
 * Cookie Listener Test
 */
namespace OlcsTest\Mvc;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Mvc\CookieListener;
use Olcs\Service\Cookie\CookieReader;
use Olcs\Service\Cookie\CookieState;
use Olcs\Service\Cookie\Preferences;
use Zend\EventManager\EventManagerInterface;
use Zend\Http\Header\Cookie;
use Zend\Http\Request;
use Zend\Mvc\MvcEvent;
use Zend\View\Helper\Placeholder;
use Zend\View\Helper\Placeholder\Container;

class CookieListenerTest extends MockeryTestCase
{
    /**
     * @var CookieListener
     */
    protected $sut;

    protected $cookieReader;

    protected $placeholder;

    public function setUp()
    {
        $this->cookieReader = m::mock(CookieReader::class);
        $this->placeholder = m::mock(Placeholder::class);

        $this->sut = new CookieListener($this->cookieReader, $this->placeholder);
    }

    public function testAttach()
    {
        $em = m::mock(EventManagerInterface::class);
        $em->shouldReceive('attach')->with(MvcEvent::EVENT_ROUTE, [$this->sut, 'onRoute'], 1)->once();

        $this->sut->attach($em);
    }

    public function testOnRouteNonHttp()
    {
        $request = m::mock();

        $event = m::mock(MvcEvent::class);
        $event->shouldReceive('getRequest')->andReturn($request);

        $this->cookieReader->shouldReceive('getState')->never();
        $this->placeholder->shouldReceive('getContainer')->with('cookieAnalytics')->never();

        $this->sut->onRoute($event);
    }

    public function testOnRoute()
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
