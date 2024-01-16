<?php

namespace Olcs\Service\Cookie;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class PreferencesSetCookieGeneratorFactory implements FactoryInterface
{
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
