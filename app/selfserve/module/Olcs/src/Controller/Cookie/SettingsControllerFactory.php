<?php

namespace Olcs\Controller\Cookie;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class SettingsControllerFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return SettingsController
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $mainServiceLocator = $serviceLocator->getServiceLocator();

        return new SettingsController(
            $mainServiceLocator->get('CookieCurrentPreferencesProvider'),
            $mainServiceLocator->get('CookieSetCookieArrayGenerator'),
            $mainServiceLocator->get('CookiePreferencesFactory')
        );
    }
}
