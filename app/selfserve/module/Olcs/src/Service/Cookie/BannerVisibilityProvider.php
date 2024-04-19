<?php

namespace Olcs\Service\Cookie;

use Laminas\Mvc\MvcEvent;

class BannerVisibilityProvider
{
    /**
     * Create service instance
     *
     *
     * @return BannerVisibilityProvider
     */
    public function __construct(private CookieReader $cookieReader)
    {
    }

    /**
     * Whether the cookie banner needs to be displayed
     *
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
