<?php

namespace Olcs\Service\Cookie;

use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

class AcceptAllSetCookieGeneratorFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return AcceptAllSetCookieGenerator
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new AcceptAllSetCookieGenerator(
            $serviceLocator->get('CookiePreferencesSetCookieGenerator'),
            $serviceLocator->get('CookiePreferencesFactory')
        );
    }
}
