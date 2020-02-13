<?php

namespace Olcs\Service\Cookie;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class PreferencesSetCookieGeneratorFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return PreferencesSetCookieGenerator
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new PreferencesSetCookieGenerator(
            $serviceLocator->get('CookieSetCookieFactory'),
            $serviceLocator->get('CookieCookieExpiryGenerator')
        );
    }
}
