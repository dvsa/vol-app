<?php

namespace Olcs\Service\Cookie;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class DeleteSetCookieGeneratorFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return DeleteSetCookieGenerator
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new DeleteSetCookieGenerator(
            $serviceLocator->get('CookieSetCookieFactory'),
            $serviceLocator->get('CookieCookieExpiryGenerator')
        );
    }
}
