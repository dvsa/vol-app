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
        $this->listeners[] = $events->attach(MvcEvent::EVENT_ROUTE, [$this, 'onRoute'], $priority);
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

        if ($request->getQuery('acceptAllCookies') === 'true') {
            $routeMatch = $e->getRouteMatch();
            $redirectUrl = $this->urlHelper->fromRoute(
                $routeMatch->getMatchedRouteName(),
                $routeMatch->getParams(),
                [
                    'query' => [
                        'acceptedAllCookiesConfirmation' => 'true'
                    ]
                ]
            );

            $response = $e->getResponse();
            $responseHeaders = $response->getHeaders();

            $responseHeaders->addHeaderLine('Location', $redirectUrl);
            $responseHeaders->addHeader(
                $this->acceptAllSetCookieGenerator->generate()
            );

            $response->setStatusCode(302);
            $response->sendHeaders();

            return;
        }

        $cookieBannerMode = '';

        if ($request->getQuery('acceptedAllCookiesConfirmation') === 'true') {
            $cookieBannerMode = 'confirmation';
        } elseif ($this->bannerVisibilityProvider->shouldDisplay($e)) {
            $cookieBannerMode = 'banner';
        }

        $this->placeholder->getContainer('cookieBannerMode')->set($cookieBannerMode);
    }
}
