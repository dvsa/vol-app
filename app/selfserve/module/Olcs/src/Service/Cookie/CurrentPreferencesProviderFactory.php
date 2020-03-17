<?php

namespace Olcs\Service\Cookie;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

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
