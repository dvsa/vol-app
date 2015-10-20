<?php

/**
 * Cookie Banner Listener Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace OlcsTest\Mvc;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Mvc\CookieBannerListener;
use Zend\EventManager\EventManagerInterface;
use Zend\Http\Request;
use Zend\Mvc\MvcEvent;
use Zend\ServiceManager\ServiceManager;

/**
 * Cookie Banner Listener Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class CookieBannerListenerTest extends MockeryTestCase
{
    /**
     * @var CookieBannerListener
     */
    protected $sut;

    protected $cookieBanner;

    public function setUp()
    {
        $this->cookieBanner = m::mock();

        $sm = m::mock(ServiceManager::class)->makePartial();
        $sm->setService('CookieBanner', $this->cookieBanner);

        $this->sut = new CookieBannerListener();

        $this->sut->createService($sm);
    }

    public function testAttach()
    {
        $em = m::mock(EventManagerInterface::class);
        $em->shouldReceive('attach')->with(MvcEvent::EVENT_ROUTE, [$this->sut, 'onRoute'], 1);

        $this->sut->attach($em);
    }

    public function testOnRouteNonHttp()
    {
        $request = m::mock();

        $event = m::mock(MvcEvent::class);
        $event->shouldReceive('getRequest')->andReturn($request);

        $this->sut->onRoute($event);
    }

    public function testOnRoute()
    {
        $this->cookieBanner->shouldReceive('toSeeOrNotToSee')->once();

        $request = m::mock(Request::class);

        $event = m::mock(MvcEvent::class);
        $event->shouldReceive('getRequest')->andReturn($request);

        $this->sut->onRoute($event);
    }
}
