<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Service\Letter\SectionRenderer;

use Laminas\Mvc\Service\AbstractPluginManagerFactory;

/**
 * Factory for creating the SectionRendererPluginManager
 */
class SectionRendererPluginManagerFactory extends AbstractPluginManagerFactory
{
    public const PLUGIN_MANAGER_CLASS = SectionRendererPluginManager::class;
}
