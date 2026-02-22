<?php

/**
 * Cookie Banner Listener
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Olcs\Mvc;

use Common\Service\Helper\UrlHelperService;
use Olcs\Service\Cookie\AcceptAllSetCookieGenerator;
use Olcs\Service\Cookie\BannerVisibilityProvider;
use Laminas\EventManager\EventManagerInterface;
use Laminas\EventManager\ListenerAggregateInterface;
use Laminas\EventManager\ListenerAggregateTrait;
use Laminas\Http\Request as HttpRequest;
use Laminas\Mvc\MvcEvent;
use Laminas\View\Helper\Placeholder;

/**
 * Cookie Banner Listener
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class CookieBannerListener implements ListenerAggregateInterface
{
    use ListenerAggregateTrait;

    /**
     * Create service instance
     *
     *
     * @return CookieBannerListener
     */
    public function __construct(private AcceptAllSetCookieGenerator $acceptAllSetCookieGenerator, private BannerVisibilityProvider $bannerVisibilityProvider, private Placeholder $placeholder, private UrlHelperService $urlHelper)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_ROUTE, $this->onRoute(...), $priority);
    }

    /**
     * {@inheritdoc}
     *
     * @return void
     */
    public function onRoute(MvcEvent $e)
    {
        $request = $e->getRequest();

        if (!($request instanceof HttpRequest)) {
            return;
        }

        $routeMatch = $e->getRouteMatch();
        $params     = $routeMatch->getParams();

        if ($request->getQuery('acceptAllCookies') === 'true') {
            $this->handleCookieResponse(
                $e,
                $routeMatch->getMatchedRouteName(),
                $params,
                ['acceptedAllCookiesConfirmation' => 'true'],
                $this->acceptAllSetCookieGenerator->generate()
            );
            return;
        }

        if ($request->getQuery('rejectedCookies') === 'false') {
            $this->handleCookieResponse(
                $e,
                $routeMatch->getMatchedRouteName(),
                $params,
                ['rejectedAllCookiesConfirmation' => 'true'],
                $this->acceptAllSetCookieGenerator->generate(false)
            );
            return;
        }

        // Determine which banner mode to show
        $cookieBannerMode = '';
        if ($request->getQuery('acceptedAllCookiesConfirmation') === 'true') {
            $cookieBannerMode = 'confirmation';
        } elseif ($request->getQuery('rejectedAllCookiesConfirmation') === 'true') {
            $cookieBannerMode = 'rejectedConfirmation';
        } elseif ($this->bannerVisibilityProvider->shouldDisplay($e)) {
            $cookieBannerMode = 'banner';
        }

        $this->placeholder->getContainer('cookieBannerMode')->set($cookieBannerMode);
    }

    /**
     * Handles redirect + cookie set logic for cookie preferences
     */
    private function handleCookieResponse(
        MvcEvent $e,
        string $routeName,
        array $params,
        array $queryParams,
        $setCookieHeader
    ): void {
        $redirectUrl = $this->urlHelper->fromRoute(
            $routeName,
            $params,
            ['query' => $queryParams]
        );

        $response = $e->getResponse();
        $response->getHeaders()->addHeaderLine('Location', $redirectUrl);
        $response->getHeaders()->addHeader($setCookieHeader);
        $response->setStatusCode(302);
        $response->sendHeaders();
    }
}
