<?php

namespace Olcs\Service\Cookie;

use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

class BannerVisibilityProviderFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return BannerVisibilityProvider
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new BannerVisibilityProvider(
            $serviceLocator->get('CookieCookieReader')
        );
    }
}
