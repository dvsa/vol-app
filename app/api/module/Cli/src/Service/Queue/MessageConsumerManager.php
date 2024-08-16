<?php

namespace Dvsa\Olcs\Cli\Service\Queue;

use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\ConfigInterface;
use Dvsa\Olcs\Cli\Service\Queue\Consumer\MessageConsumerInterface;

/**
 * @template-extends AbstractPluginManager<MessageConsumerInterface>
 */
class MessageConsumerManager extends AbstractPluginManager
{
    protected $instanceOf = MessageConsumerInterface::class;
}
