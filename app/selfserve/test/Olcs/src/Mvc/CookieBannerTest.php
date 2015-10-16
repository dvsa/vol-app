<?php

/**
 * Cookie Banner Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace OlcsTest\Mvc;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Mvc\CookieBanner;
use Zend\Http\Header\SetCookie;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Http\Header\Cookie;
use Zend\ServiceManager\ServiceManager;

/**
 * Cookie Banner Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class CookieBannerTest extends MockeryTestCase
{
    /**
     * @var CookieBanner
     */
    protected $sut;

    /**
     * @var Cookie
     */
    protected $cookie;

    protected $response;

    protected $placeholder;

    public function setUp()
    {
        $sm = m::mock(ServiceManager::class)->makePartial();

        $this->cookie = m::mock(Cookie::class)->makePartial();

        $request = m::mock();
        $request->shouldReceive('getCookie')->andReturn($this->cookie);
        $sm->setService('Request', $request);

        $this->response = m::mock();
        $sm->setService('Response', $this->response);

        $this->placeholder = m::mock();

        $vhm = m::mock();
        $vhm->shouldReceive('get')->with('Placeholder')->andReturn($this->placeholder);
        $sm->setService('ViewHelperManager', $vhm);

        $this->sut = new CookieBanner();

        $this->sut->createService($sm);
    }

    public function testToSeeOrNotToSeeWhenShouldSee()
    {
        $this->placeholder->shouldReceive('getContainer->set')->once()->with(true);

        $this->response->shouldReceive('getHeaders->addHeader')->once()
            ->andReturnUsing(
                function (SetCookie $cookie) {
                    $this->assertEquals(CookieBanner::KEY, $cookie->getName());
                    $this->assertEquals(1, $cookie->getValue());
                    $this->assertEquals('/', $cookie->getPath());
                    $expires = $cookie->getExpires();
                    $this->assertEquals(date('Y-m-d', strtotime('+1 month')), date('Y-m-d', strtotime($expires)));
                }
            );

        $this->sut->toSeeOrNotToSee();
    }

    public function testToSeeOrNotToSeeWhenShouldntSee()
    {
        $this->cookie->offsetSet(CookieBanner::KEY, 1);

        $this->placeholder->shouldReceive('getContainer->set')->once()->with(false);

        $this->sut->toSeeOrNotToSee();
    }
}
