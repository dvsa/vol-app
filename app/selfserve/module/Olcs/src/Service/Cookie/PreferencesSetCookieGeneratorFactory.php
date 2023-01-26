<?php

namespace Olcs\Service\Cookie;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

class PreferencesSetCookieGeneratorFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return PreferencesSetCookieGenerator
     */
    public function createService(ServiceLocatorInterface $serviceLocator) : PreferencesSetCookieGenerator
    {
        return $this->__invoke($serviceLocator, PreferencesSetCookieGenerator::class);
    }

    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return PreferencesSetCookieGenerator
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) : PreferencesSetCookieGenerator
    {
        return new PreferencesSetCookieGenerator(
            $container->get('CookieSetCookieFactory'),
            $container->get('CookieCookieExpiryGenerator')
        );
    }
}
