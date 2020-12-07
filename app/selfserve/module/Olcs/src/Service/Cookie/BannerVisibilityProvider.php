<?php

namespace Olcs\Service\Cookie;

use Laminas\Mvc\MvcEvent;

class BannerVisibilityProvider
{
    /** @var CookieReader */
    private $cookieReader;

    /**
     * Create service instance
     *
     * @param CookieReader $cookieReader
     *
     * @return BannerVisibilityProvider
     */
    public function __construct(CookieReader $cookieReader)
    {
        $this->cookieReader = $cookieReader;
    }

    /**
     * Whether the cookie banner needs to be displayed
     *
     * @param MvcEvent $e
     *
     * @return bool
     */
    public function shouldDisplay(MvcEvent $e)
    {
        $exemptRouteNames = [
            'cookies/settings',
        ];

        $matchedRouteName = $e->getRouteMatch()->getMatchedRouteName();

        if (in_array($matchedRouteName, $exemptRouteNames)) {
            return false;
        }

        $cookieState = $this->cookieReader->getState(
            $e->getRequest()->getCookie()
        );

        return !$cookieState->isValid();
    }
}
