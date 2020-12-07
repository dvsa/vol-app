<?php

namespace Olcs\Service\Cookie;

use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

class CurrentPreferencesProviderFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return CurrentPreferencesProvider
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new CurrentPreferencesProvider(
            $serviceLocator->get('CookieCookieReader'),
            $serviceLocator->get('CookiePreferencesFactory')
        );
    }
}
