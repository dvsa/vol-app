<?php

namespace Olcs\Service\Data\Search;

use Zend\ServiceManager\Config as ServiceManagerConfig;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class SearchTypeManagerFactory
 * @package Olcs\Service\Data\Search
 */
class SearchTypeManagerFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $service = new SearchTypeManager();

        $config = $serviceLocator->get('Config');
        if (isset($config['search'])) {
            $configuration = new ServiceManagerConfig($config['search']);
            $configuration->configureServiceManager($service);
        }

        return $service;
    }
}
