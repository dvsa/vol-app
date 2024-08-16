<?php

namespace Dvsa\Olcs\Api\Service\Submission\Sections;

use Laminas\Mvc\Service\AbstractPluginManagerFactory;

/**
 * Class SectionGeneratorPluginManagerFactory
 * @package Dvsa\Olcs\Api\Service\Submission\Sections
 */
class SectionGeneratorPluginManagerFactory extends AbstractPluginManagerFactory
{
    public const PLUGIN_MANAGER_CLASS = SectionGeneratorPluginManager::class;
}
