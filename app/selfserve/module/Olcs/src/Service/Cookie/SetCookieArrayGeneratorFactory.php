<?php

namespace Olcs\Service\Cookie;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class SetCookieArrayGeneratorFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return SetCookieArrayGenerator
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) : SetCookieArrayGenerator
    {
        return new SetCookieArrayGenerator(
            $container->get('CookieDeleteCookieNamesProvider'),
            $container->get('CookiePreferencesSetCookieGenerator'),
            $container->get('CookieDeleteSetCookieGenerator')
        );
    }
}
