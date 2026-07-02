<?php

namespace Common\Service\Data;

use Psr\Container\ContainerInterface;
use Laminas\Mvc\Service\AbstractPluginManagerFactory;

/**
 * PluginManagerFactory
 */
class PluginManagerFactory extends AbstractPluginManagerFactory
{
    public const PLUGIN_MANAGER_CLASS = 'Common\Service\Data\PluginManager';

    #[\Override]
    public function __invoke(ContainerInterface $container, $name, array $options = null)
    {
        $config = $container->get('Config');

        return parent::__invoke($container, $name, $config['data_services']);
    }
}
