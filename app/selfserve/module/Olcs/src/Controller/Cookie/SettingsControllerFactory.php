<?php

namespace Olcs\Controller\Cookie;

use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

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
