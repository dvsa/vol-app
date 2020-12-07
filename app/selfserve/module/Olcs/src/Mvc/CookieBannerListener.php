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
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\View\Helper\Placeholder;

/**
 * Cookie Banner Listener
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class CookieBannerListener implements ListenerAggregateInterface
{
    use ListenerAggregateTrait;

    /** @var AcceptAllSetCookieGenerator */
    private $acceptAllSetCookieGenerator;

    /** @var BannerVisibilityProvider */
    private $bannerVisibilityProvider;

    /** @var Placeholder */
    private $placeholder;

    /** @var UrlHelperService */
    private $urlHelper;

    /**
     * Create service instance
     *
     * @param AcceptAllSetCookieGenerator $acceptAllSetCookieGenerator
     * @param BannerVisibilityProvider $bannerVisibilityProvider
     * @param Placeholder $placeholder
     * @param UrlHelperService $urlHelper
     *
     * @return CookieBannerListener
     */
    public function __construct(
        AcceptAllSetCookieGenerator $acceptAllSetCookieGenerator,
        BannerVisibilityProvider $bannerVisibilityProvider,
        Placeholder $placeholder,
        UrlHelperService $urlHelper
    ) {
        $this->acceptAllSetCookieGenerator = $acceptAllSetCookieGenerator;
        $this->bannerVisibilityProvider = $bannerVisibilityProvider;
        $this->placeholder = $placeholder;
        $this->urlHelper = $urlHelper;
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
