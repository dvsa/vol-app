<?php

namespace Olcs\Mvc;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class CookieBannerListenerFactory implements FactoryInterface
{
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
