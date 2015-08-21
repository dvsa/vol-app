<?php

namespace Olcs\Service\Marker;

use Zend\ServiceManager\Config;
use Zend\Mvc\Service\AbstractPluginManagerFactory;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * MarkerPluginManagerFactory
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class MarkerPluginManagerFactory extends AbstractPluginManagerFactory
{
    const CONFIG_KEY = 'marker_plugins';

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('Config');
        $configObject = new Config(!empty($config[static::CONFIG_KEY]) ? $config[static::CONFIG_KEY] : null);

        return new MarkerPluginManager($configObject);
    }
}
