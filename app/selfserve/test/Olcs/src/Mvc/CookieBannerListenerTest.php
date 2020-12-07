<?php

/**
 * Cookie Banner Listener Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace OlcsTest\Mvc;

use Common\Service\Helper\UrlHelperService;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Mvc\CookieBannerListener;
use Olcs\Service\Cookie\AcceptAllSetCookieGenerator;
use Olcs\Service\Cookie\BannerVisibilityProvider;
use Laminas\EventManager\EventManagerInterface;
use Laminas\Http\Header\SetCookie;
use Laminas\Http\Headers;
use Laminas\Http\Request;
use Laminas\Http\Response;
use Laminas\Mvc\MvcEvent;
use Laminas\Mvc\Router\Http\RouteMatch;
use Laminas\View\Helper\Placeholder;
use Laminas\View\Helper\Placeholder\Container\AbstractContainer;

/**
 * Cookie Banner Listener Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class CookieBannerListenerTest extends MockeryTestCase
{
    private $acceptAllSetCookieGenerator;

    private $bannerVisibilityProvider;

    private $placeholder;

    private $urlHelper;

    /**
     * @var CookieBannerListener
     */
    protected $sut;

    public function setUp(): void
    {
        $this->acceptAllSetCookieGenerator = m::mock(AcceptAllSetCookieGenerator::class);

        $this->bannerVisibilityProvider = m::mock(BannerVisibilityProvider::class);

        $this->placeholder = m::mock(Placeholder::class);

        $this->urlHelper = m::mock(UrlHelperService::class);

        $this->sut = new CookieBannerListener(
            $this->acceptAllSetCookieGenerator,
            $this->bannerVisibilityProvider,
            $this->placeholder,
            $this->urlHelper
        );
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

    public function testOnRouteAcceptAllCookiesRedirect()
    {
        $redirectUrl = '/redirect/url?param1Name=param1Value&param2Name=param2Value';

        $routeName = 'route/name';
        $routeParams = [
            'param1Name' => 'param1Value',
            'param2Name' => 'param2Value'
        ];

        $expectedRedirectOptions = [
            'query' => [
                'acceptedAllCookiesConfirmation' => 'true'
            ]
        ];

        $routeMatch = m::mock(RouteMatch::class);
        $routeMatch->shouldReceive('getMatchedRouteName')
            ->withNoArgs()
            ->andReturn($routeName);
        $routeMatch->shouldReceive('getParams')
            ->withNoArgs()
            ->andReturn($routeParams);

        $this->urlHelper->shouldReceive('fromRoute')
            ->with($routeName, $routeParams, $expectedRedirectOptions)
            ->once()
            ->andReturn($redirectUrl);

        $request = m::mock(Request::class);
        $request->shouldReceive('getQuery')
            ->with('acceptAllCookies')
            ->andReturn('true');

        $setCookie = m::mock(SetCookie::class);

        $this->acceptAllSetCookieGenerator->shouldReceive('generate')
            ->withNoArgs()
            ->andReturn($setCookie);

        $responseHeaders = m::mock(Headers::class);
        $responseHeaders->shouldReceive('addHeaderLine')
            ->with('Location', $redirectUrl)
            ->once()
            ->globally()
            ->ordered();
        $responseHeaders->shouldReceive('addHeader')
            ->with($setCookie)
            ->once()
            ->globally()
            ->ordered();

        $response = m::mock(Response::class);
        $response->shouldReceive('getHeaders')
            ->withNoArgs()
            ->andReturn($responseHeaders);
        $response->shouldReceive('setStatusCode')
            ->with(302)
            ->once()
            ->globally()
            ->ordered();
        $response->shouldReceive('sendHeaders')
            ->withNoArgs()
            ->once()
            ->globally()
            ->ordered();

        $event = m::mock(MvcEvent::class);
        $event->shouldReceive('getRequest')
            ->withNoArgs()
            ->andReturn($request);
        $event->shouldReceive('getResponse')
            ->withNoArgs()
            ->andReturn($response);
        $event->shouldReceive('getRouteMatch')
            ->withNoArgs()
            ->andReturn($routeMatch);

        $this->sut->onRoute($event);
    }

    public function testOnRouteDisplayConfirmation()
    {
        $request = m::mock(Request::class);
        $request->shouldReceive('getQuery')
            ->with('acceptAllCookies')
            ->andReturn(null);
        $request->shouldReceive('getQuery')
            ->with('acceptedAllCookiesConfirmation')
            ->andReturn('true');

        $container = m::mock(AbstractContainer::class);
        $container->shouldReceive('set')
            ->with('confirmation')
            ->once();

        $this->placeholder->shouldReceive('getContainer')
            ->with('cookieBannerMode')
            ->andReturn($container);

        $event = m::mock(MvcEvent::class);
        $event->shouldReceive('getRequest')
            ->withNoArgs()
            ->andReturn($request);

        $this->sut->onRoute($event);
    }

    /**
     * @dataProvider dpOnRouteDisplayBanner
     */
    public function testOnRouteDisplayBanner($bannerVisible, $expectedCookieBannerMode)
    {
        $request = m::mock(Request::class);
        $request->shouldReceive('getQuery')
            ->with('acceptAllCookies')
            ->andReturn(null);
        $request->shouldReceive('getQuery')
            ->with('acceptedAllCookiesConfirmation')
            ->andReturn(null);

        $event = m::mock(MvcEvent::class);
        $event->shouldReceive('getRequest')
            ->withNoArgs()
            ->andReturn($request);

        $this->bannerVisibilityProvider->shouldReceive('shouldDisplay')
            ->with($event)
            ->andReturn($bannerVisible);

        $container = m::mock(AbstractContainer::class);
        $container->shouldReceive('set')
            ->with($expectedCookieBannerMode)
            ->once();

        $this->placeholder->shouldReceive('getContainer')
            ->with('cookieBannerMode')
            ->andReturn($container);

        $this->sut->onRoute($event);
    }

    public function dpOnRouteDisplayBanner()
    {
        return [
            [true, 'banner'],
            [false, ''],
        ];
    }
}
