<?php

namespace Olcs\Controller\Cookie;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class SettingsControllerFactory implements FactoryInterface
{
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

        return new SettingsController(
            $container->get('CookieCurrentPreferencesProvider'),
            $container->get('CookieSetCookieArrayGenerator'),
            $container->get('CookiePreferencesFactory')
        );
    }
}
