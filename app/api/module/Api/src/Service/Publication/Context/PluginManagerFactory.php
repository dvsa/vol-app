<?php

namespace Dvsa\Olcs\Api\Service\Publication\Context;

use Laminas\Mvc\Service\AbstractPluginManagerFactory;

/**
 * Class PluginManagerFactory
 * @package Dvsa\Olcs\Api\Service\Publication\Context
 */
class PluginManagerFactory extends AbstractPluginManagerFactory
{
    public const PLUGIN_MANAGER_CLASS = PluginManager::class;
}
