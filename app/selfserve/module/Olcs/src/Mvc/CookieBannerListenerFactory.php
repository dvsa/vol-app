<?php

namespace Olcs\Mvc;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

class CookieBannerListenerFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return CookieBannerListener
     */
    public function createService(ServiceLocatorInterface $serviceLocator) : CookieBannerListener
    {
        return $this->__invoke($serviceLocator, CookieBannerListener::class);
    }

    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return CookieBannerListener
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) : CookieBannerListener
    {
        return new CookieBannerListener(
            $container->get('CookieAcceptAllSetCookieGenerator'),
            $container->get('CookieBannerVisibilityProvider'),
            $container->get('ViewHelperManager')->get('Placeholder'),
            $container->get('Helper\Url')
        );
    }
}
