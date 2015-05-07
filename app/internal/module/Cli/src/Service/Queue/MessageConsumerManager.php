<?php

/**
 * Message Consumer Manager
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Cli\Service\Queue;

use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\Exception\RuntimeException;
use Zend\ServiceManager\ConfigInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Cli\Service\Queue\Consumer\MessageConsumerInterface;

/**
 * Message Consumer Manager
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class MessageConsumerManager extends AbstractPluginManager
{
    public function __construct(ConfigInterface $config = null)
    {
        if ($config) {
            $config->configureServiceManager($this);
        }

        $this->addInitializer(array($this, 'initialize'));
    }

    public function initialize($instance)
    {
        if ($instance instanceof ServiceLocatorAwareInterface) {
            $instance->setServiceLocator($this->getServiceLocator());
        }
    }

    public function validatePlugin($plugin)
    {
        if (!$plugin instanceof MessageConsumerInterface) {
            throw new RuntimeException('Message consumer service does not implement MessageConsumerInterface');
        }
    }
}
