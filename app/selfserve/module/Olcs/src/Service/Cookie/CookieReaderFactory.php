<?php

namespace Olcs\Service\Cookie;

use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

class CookieReaderFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return CookieReader
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new CookieReader(
            $serviceLocator->get('CookieCookieStateFactory'),
            $serviceLocator->get('CookiePreferencesFactory')
        );
    }
}
