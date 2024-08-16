<?php

namespace Dvsa\Olcs\Api\Domain;

use Dvsa\Olcs\Api\Service\AbstractServiceManagerFactory;

class CommandHandlerManagerFactory extends AbstractServiceManagerFactory
{
    public const CONFIG_KEY = 'command_handlers';
    public const PLUGIN_MANAGER_CLASS = CommandHandlerManager::class;
}
