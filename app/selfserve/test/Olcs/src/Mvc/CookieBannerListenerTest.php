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
use Laminas\Router\Http\RouteMatch;
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

    public function testAttach(): void
    {
        $em = m::mock(EventManagerInterface::class);
        $em->expects('attach')->with(MvcEvent::EVENT_ROUTE, [$this->sut, 'onRoute'], 1);

        $this->sut->attach($em);
    }

    public function testOnRouteNonHttp(): void
    {
        $request = m::mock();

        $event = m::mock(MvcEvent::class);
        $event->expects('getRequest')->andReturns($request);

        $this->sut->onRoute($event);
    }

    public function testOnRouteAcceptAllCookiesRedirect(): void
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
        $routeMatch->allows('getMatchedRouteName')
            ->withNoArgs()
            ->andReturns($routeName);
        $routeMatch->allows('getParams')
            ->withNoArgs()
            ->andReturns($routeParams);

        $this->urlHelper->expects('fromRoute')
            ->with($routeName, $routeParams, $expectedRedirectOptions)
            ->andReturns($redirectUrl);

        $request = m::mock(Request::class);
        $request->allows('getQuery')
            ->with('acceptAllCookies')
            ->andReturns('true');

        $setCookie = m::mock(SetCookie::class);

        $this->acceptAllSetCookieGenerator->allows('generate')
            ->withNoArgs()
            ->andReturns($setCookie);

        $responseHeaders = m::mock(Headers::class);
        $responseHeaders->expects('addHeaderLine')
            ->with('Location', $redirectUrl)
            ->globally()
            ->ordered();
        $responseHeaders->expects('addHeader')
            ->with($setCookie)
            ->globally()
            ->ordered();

        $response = m::mock(Response::class);
        $response->allows('getHeaders')
            ->withNoArgs()
            ->andReturns($responseHeaders);
        $response->expects('setStatusCode')
            ->with(302)
            ->globally()
            ->ordered();
        $response->expects('sendHeaders')
            ->withNoArgs()
            ->globally()
            ->ordered();

        $event = m::mock(MvcEvent::class);
        $event->allows('getRequest')
            ->withNoArgs()
            ->andReturns($request);
        $event->allows('getResponse')
            ->withNoArgs()
            ->andReturns($response);
        $event->allows('getRouteMatch')
            ->withNoArgs()
            ->andReturns($routeMatch);

        $this->sut->onRoute($event);
    }

    public function testOnRouteDisplayConfirmation(): void
    {
        $request = m::mock(Request::class);
        $request->allows('getQuery')->with('acceptAllCookies')->andReturns(null);
        $request->allows('getQuery')->with('acceptedAllCookiesConfirmation')->andReturns('true');
        $request->allows('getQuery')->with('rejectedCookies')->andReturns(null);

        $event = m::mock(MvcEvent::class);
        $event->allows('getRequest')->andReturns($request);

        $routeMatch = m::mock(RouteMatch::class);
        $routeMatch->allows('getParams')->andReturns([]);
        $event->allows('getRouteMatch')->andReturns($routeMatch);

        $container = m::mock(AbstractContainer::class);
        $container->expects('set')->with('confirmation');

        $this->placeholder
            ->allows('getContainer')
            ->with('cookieBannerMode')
            ->andReturns($container);

        $this->sut->onRoute($event);
    }

    /**
     * @dataProvider provideBannerVisibilityScenarios
     */
    public function testOnRouteDisplayBanner(bool $bannerVisible, string $expectedMode): void
    {
        $request = m::mock(Request::class);
        $request->allows('getQuery')->with('acceptAllCookies')->andReturns(null);
        $request->allows('getQuery')->with('acceptedAllCookiesConfirmation')->andReturns(null);
        $request->allows('getQuery')->with('rejectedAllCookiesConfirmation')->andReturns(null);
        $request->allows('getQuery')->with('rejectedCookies')->andReturns(null);

        $event = m::mock(MvcEvent::class);
        $event->allows('getRequest')->andReturns($request);

        $routeMatch = m::mock(RouteMatch::class);
        $routeMatch->allows('getParams')->andReturns([]);
        $event->allows('getRouteMatch')->andReturns($routeMatch);

        $this->bannerVisibilityProvider
            ->allows('shouldDisplay')
            ->with($event)
            ->andReturns($bannerVisible);

        $container = m::mock(AbstractContainer::class);
        $container->expects('set')->with($expectedMode);

        $this->placeholder
            ->allows('getContainer')
            ->with('cookieBannerMode')
            ->andReturns($container);

        $this->sut->onRoute($event);
    }

    /**
     * @return array<string, array{bool, string}>
     */
    public function provideBannerVisibilityScenarios(): array
    {
        return [
            'banner visible' => [true, 'banner'],
            'banner hidden' => [false, ''],
        ];
    }

    public function testOnRouteRejectedCookiesRedirect(): void
    {
        $redirectUrl = '/redirect/url?rejectedAllCookiesConfirmation=true';

        $routeName = 'route/name';
        $routeParams = [
            'param1Name' => 'param1Value',
            'param2Name' => 'param2Value'
        ];

        $expectedRedirectOptions = [
            'query' => [
                'rejectedAllCookiesConfirmation' => 'true'
            ]
        ];

        $routeMatch = m::mock(RouteMatch::class);
        $routeMatch->allows('getMatchedRouteName')
            ->withNoArgs()
            ->andReturns($routeName);
        $routeMatch->allows('getParams')
            ->withNoArgs()
            ->andReturns($routeParams);

        $this->urlHelper->expects('fromRoute')
            ->with($routeName, $routeParams, $expectedRedirectOptions)
            ->andReturns($redirectUrl);

        $request = m::mock(Request::class);
        $request->allows('getQuery')
            ->with('acceptAllCookies')
            ->andReturns(null);
        $request->allows('getQuery')
            ->with('rejectedCookies')
            ->andReturns('false');

        $setCookie = m::mock(SetCookie::class);
        $this->acceptAllSetCookieGenerator->allows('generate')
            ->with(false)
            ->andReturns($setCookie);

        $responseHeaders = m::mock(Headers::class);
        $responseHeaders->expects('addHeaderLine')
            ->with('Location', $redirectUrl)
            ->globally()
            ->ordered();
        $responseHeaders->expects('addHeader')
            ->with($setCookie)
            ->globally()
            ->ordered();

        $response = m::mock(Response::class);
        $response->shouldReceive('getHeaders')
            ->andReturn($responseHeaders);
        $response->shouldReceive('setStatusCode')
            ->with(302)
            ->once()
            ->globally()
            ->ordered();
        $response->expects('sendHeaders')
            ->globally()
            ->ordered();

        $event = m::mock(MvcEvent::class);
        $event->allows('getRequest')->andReturns($request);
        $event->allows('getResponse')->andReturns($response);
        $event->allows('getRouteMatch')->andReturns($routeMatch);

        $this->sut->onRoute($event);
    }
}
