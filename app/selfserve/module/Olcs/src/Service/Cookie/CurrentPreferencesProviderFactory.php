<?php

namespace Olcs\Service\Cookie;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

class CurrentPreferencesProviderFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return CurrentPreferencesProvider
     */
    public function createService(ServiceLocatorInterface $serviceLocator) : CurrentPreferencesProvider
    {
        return $this->__invoke($serviceLocator, CurrentPreferencesProvider::class);
    }

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
