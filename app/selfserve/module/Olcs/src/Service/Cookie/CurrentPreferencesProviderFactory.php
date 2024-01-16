<?php

namespace Olcs\Service\Cookie;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class CurrentPreferencesProviderFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return CurrentPreferencesProvider
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) : CurrentPreferencesProvider
    {
        return new CurrentPreferencesProvider(
            $container->get('CookieCookieReader'),
            $container->get('CookiePreferencesFactory')
        );
    }
}
