<?php

namespace Olcs\Controller\Cookie;

use Interop\Container\ContainerInterface;
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
    public function createService(ServiceLocatorInterface $serviceLocator) : SettingsController
    {
        return $this->__invoke($serviceLocator, SettingsController::class);
    }

    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array $options
     * @return SettingsController
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) : SettingsController
    {
        if (method_exists($container, 'getServiceLocator') && $container->getServiceLocator()) {
            $container = $container->getServiceLocator();
        }
        return new SettingsController(
            $container->get('CookieCurrentPreferencesProvider'),
            $container->get('CookieSetCookieArrayGenerator'),
            $container->get('CookiePreferencesFactory')
        );
    }
}
