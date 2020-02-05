<?php

namespace Olcs\Mvc;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class CookieBannerListenerFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return CookieBannerListener
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new CookieBannerListener(
            $serviceLocator->get('CookieAcceptAllSetCookieGenerator'),
            $serviceLocator->get('CookieBannerVisibilityProvider'),
            $serviceLocator->get('ViewHelperManager')->get('Placeholder'),
            $serviceLocator->get('Helper\Url')
        );
    }
}
