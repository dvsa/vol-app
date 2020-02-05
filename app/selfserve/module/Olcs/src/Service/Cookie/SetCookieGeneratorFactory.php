<?php

namespace Olcs\Service\Cookie;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class SetCookieGeneratorFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return SetCookieGenerator
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new SetCookieGenerator(
            $serviceLocator->get('CookieSetCookieFactory'),
            $serviceLocator->get('CookieCookieExpiryGenerator')
        );
    }
}
