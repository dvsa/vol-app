<?php

namespace Dvsa\Olcs\Api\Service\Publication\Process;

use Laminas\Mvc\Service\AbstractPluginManagerFactory;

/**
 * Class PluginManagerFactory
 * @package Dvsa\Olcs\Api\Service\Publication\Process
 */
class PluginManagerFactory extends AbstractPluginManagerFactory
{
    public const PLUGIN_MANAGER_CLASS = PluginManager::class;
}
