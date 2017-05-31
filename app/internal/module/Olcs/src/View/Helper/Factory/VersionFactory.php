<?php

namespace Olcs\View\Helper\Factory;

use Olcs\View\Helper\Version;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class VersionFactory
 * @package Olcs\View\Helper\Factory
 */
class VersionFactory implements FactoryInterface
{
    const DEFAULT_VERSION = 'Not specified';

    /**
     * Create version helper service
     *
     * @param ServiceLocatorInterface $serviceLocator Service Locator
     *
     * @return Version
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->getServiceLocator()->get('config');

        $version = $this->getVersion($config);

        $helper = new Version();
        $helper->setVersion($version);

        return $helper;
    }

    /**
     * Get version number or default string from config
     *
     * @param array $config Config from service locator
     *
     * @return string
     */
    private function getVersion(array $config)
    {
        if (empty($config['application_version'])) {
            return self::DEFAULT_VERSION;
        }

        if (! is_string($config['application_version'])) {
            return self::DEFAULT_VERSION;
        }

        return $config['application_version'];
    }
}
