<?php

declare(strict_types=1);

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

class CookieBannerListenerTest extends MockeryTestCase
{
    private $acceptAllSetCookieGenerator;
    private $bannerVisibilityProvider;
    private $placeholder;
    private $urlHelper;

    /** @var CookieBannerListener */
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
        $event->expects('getRequest')->andReturn($request);
        $this->sut->onRoute($event);
    }

    public function testOnRouteAcceptAllCookiesRedirect(): void
    {
        $redirectUrl = '/redirect/url?param1Name=param1Value&param2Name=param2Value';
        $routeName = 'route/name';
        $routeParams = ['param1Name' => 'param1Value', 'param2Name' => 'param2Value'];

        $routeMatch = m::mock(RouteMatch::class);
        $routeMatch->expects('getMatchedRouteName')->andReturn($routeName);
        $routeMatch->expects('getParams')->andReturn($routeParams);

        $this->urlHelper->expects('fromRoute')
            ->with($routeName, $routeParams, ['query' => ['acceptedAllCookiesConfirmation' => 'true']])
            ->andReturn($redirectUrl);

        $request = m::mock(Request::class);
        $request->expects('getQuery')->with('acceptAllCookies')->andReturn('true');

        $setCookie = m::mock(SetCookie::class);
        $this->acceptAllSetCookieGenerator->expects('generate')->andReturn($setCookie);

        $responseHeaders = m::mock(Headers::class);
        $responseHeaders->expects('addHeaderLine')->with('Location', $redirectUrl);
        $responseHeaders->expects('addHeader')->with($setCookie);

        $response = m::mock(Response::class);
        $response->expects('getHeaders')
            ->andReturns($responseHeaders)
            ->twice();

        $response->expects('setStatusCode')->with(302);
        $response->expects('sendHeaders');

        $event = m::mock(MvcEvent::class);
        $event->expects('getRequest')->andReturn($request);
        $event->expects('getResponse')->andReturn($response);
        $event->expects('getRouteMatch')->andReturn($routeMatch);

        $this->sut->onRoute($event);
    }

    public function testOnRouteRejectedCookiesRedirect(): void
    {
        $redirectUrl = '/redirect/url?rejectedAllCookiesConfirmation=true';
        $routeName = 'route/name';
        $routeParams = ['param1Name' => 'param1Value', 'param2Name' => 'param2Value'];

        $routeMatch = m::mock(RouteMatch::class);
        $routeMatch->expects('getMatchedRouteName')->andReturn($routeName);
        $routeMatch->expects('getParams')->andReturn($routeParams);

        $this->urlHelper->expects('fromRoute')
            ->with($routeName, $routeParams, ['query' => ['rejectedAllCookiesConfirmation' => 'true']])
            ->andReturn($redirectUrl);

        $request = m::mock(Request::class);
        $request->expects('getQuery')->with('acceptAllCookies')->andReturn(null);
        $request->expects('getQuery')->with('rejectedCookies')->andReturn('false');

        $setCookie = m::mock(SetCookie::class);
        $this->acceptAllSetCookieGenerator->expects('generate')->with(false)->andReturn($setCookie);

        $responseHeaders = m::mock(Headers::class);
        $responseHeaders->expects('addHeaderLine')->with('Location', $redirectUrl);
        $responseHeaders->expects('addHeader')->with($setCookie);

        $response = m::mock(Response::class);
        $response->expects('getHeaders')
            ->andReturns($responseHeaders)
            ->twice();
        $response->expects('setStatusCode')->with(302);
        $response->expects('sendHeaders');

        $event = m::mock(MvcEvent::class);
        $event->expects('getRequest')->andReturn($request);
        $event->expects('getResponse')->andReturn($response);
        $event->expects('getRouteMatch')->andReturn($routeMatch);

        $this->sut->onRoute($event);
    }

    public function testOnRouteDisplayConfirmation(): void
    {
        $request = m::mock(Request::class);
        $request->expects('getQuery')->with('acceptAllCookies')->andReturn(null);
        $request->expects('getQuery')->with('acceptedAllCookiesConfirmation')->andReturn('true');
        $request->expects('getQuery')->with('rejectedCookies')->andReturn(null);

        $routeMatch = m::mock(RouteMatch::class);
        $routeMatch->expects('getParams')->andReturn([]);

        $event = m::mock(MvcEvent::class);
        $event->expects('getRequest')->andReturn($request);
        $event->expects('getRouteMatch')->andReturn($routeMatch);

        $container = m::mock(AbstractContainer::class);
        $container->expects('set')->with('confirmation');

        $this->placeholder->expects('getContainer')->with('cookieBannerMode')->andReturn($container);

        $this->sut->onRoute($event);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideBannerVisibilityScenarios')]
    public function testOnRouteDisplayBanner(bool $bannerVisible, string $expectedMode): void
    {
        $request = m::mock(Request::class);
        $request->expects('getQuery')->with('acceptAllCookies')->andReturn(null);
        $request->expects('getQuery')->with('acceptedAllCookiesConfirmation')->andReturn(null);
        $request->expects('getQuery')->with('rejectedAllCookiesConfirmation')->andReturn(null);
        $request->expects('getQuery')->with('rejectedCookies')->andReturn(null);

        $routeMatch = m::mock(RouteMatch::class);
        $routeMatch->expects('getParams')->andReturn([]);

        $event = m::mock(MvcEvent::class);
        $event->expects('getRequest')->andReturn($request);
        $event->expects('getRouteMatch')->andReturn($routeMatch);

        $this->bannerVisibilityProvider->expects('shouldDisplay')->with($event)->andReturn($bannerVisible);

        $container = m::mock(AbstractContainer::class);
        $container->expects('set')->with($expectedMode);

        $this->placeholder->expects('getContainer')->with('cookieBannerMode')->andReturn($container);

        $this->sut->onRoute($event);
    }

    public static function provideBannerVisibilityScenarios(): array
    {
        return [
            'banner visible' => [true, 'banner'],
            'banner hidden' => [false, ''],
        ];
    }
}
