<?php

namespace Dvsa\Olcs\Api\Service\Publication\Process;

use Laminas\ServiceManager\AbstractPluginManager;

/**
 * @template-extends AbstractPluginManager<ProcessInterface>
 */
class PluginManager extends AbstractPluginManager
{
    protected $instanceOf = ProcessInterface::class;
}
