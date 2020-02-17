<?php

namespace Olcs\Service\Cookie;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class SetCookieArrayGeneratorFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return SetCookieArrayGenerator
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new SetCookieArrayGenerator(
            $serviceLocator->get('CookieDeleteCookieNamesProvider'),
            $serviceLocator->get('CookiePreferencesSetCookieGenerator'),
            $serviceLocator->get('CookieDeleteSetCookieGenerator')
        );
    }
}
