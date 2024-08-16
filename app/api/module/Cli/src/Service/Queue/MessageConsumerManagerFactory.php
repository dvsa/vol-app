<?php

namespace Dvsa\Olcs\Cli\Service\Queue;

use Dvsa\Olcs\Api\Service\AbstractServiceManagerFactory;

class MessageConsumerManagerFactory extends AbstractServiceManagerFactory
{
    public const PLUGIN_MANAGER_CLASS = MessageConsumerManager::class;
    public const CONFIG_KEY = 'message_consumer_manager';
}
