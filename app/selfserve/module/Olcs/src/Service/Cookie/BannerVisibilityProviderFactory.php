<?php

namespace Olcs\Service\Cookie;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

class BannerVisibilityProviderFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return BannerVisibilityProvider
     */
    public function createService(ServiceLocatorInterface $serviceLocator) : BannerVisibilityProvider
    {
        return $this->__invoke($serviceLocator, BannerVisibilityProvider::class);
    }

    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return BannerVisibilityProvider
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) : BannerVisibilityProvider
    {
        return new BannerVisibilityProvider(
            $container->get('CookieCookieReader')
        );
    }
}
