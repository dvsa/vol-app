<?php

namespace Olcs\Service\Cookie;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

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
