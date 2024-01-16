<?php

namespace Olcs\Service\Marker;

use Psr\Container\ContainerInterface;
use Laminas\Mvc\Service\AbstractPluginManagerFactory;

/**
 * MarkerPluginManagerFactory
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class MarkerPluginManagerFactory extends AbstractPluginManagerFactory
{
    const CONFIG_KEY = 'marker_plugins';

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): MarkerPluginManager
    {
        $config = $container->get('Config');

        return new MarkerPluginManager($container, $config[self::CONFIG_KEY]);
    }
}
