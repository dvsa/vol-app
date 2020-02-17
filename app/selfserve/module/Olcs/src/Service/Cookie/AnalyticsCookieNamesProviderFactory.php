<?php

namespace Olcs\Service\Cookie;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class AnalyticsCookieNamesProviderFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return AnalyticsCookieNamesProvider
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $httpHost = $_SERVER['HTTP_HOST'];

        // modify hostname for vagrant
        if ($httpHost == 'olcs-selfserve') {
            $httpHost .= '.olcs.gov.uk';
        }

        return new AnalyticsCookieNamesProvider($httpHost);
    }
}
