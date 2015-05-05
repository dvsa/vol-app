<?php

/**
 * Message Consumer Manager Factory
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Cli\Service\Queue;

use Zend\ServiceManager\Config;
use Zend\Mvc\Service\AbstractPluginManagerFactory;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Message Consumer Manager Factory
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class MessageConsumerManagerFactory extends AbstractPluginManagerFactory
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('Config');
        $configObject = new Config($config['message_consumer_manager']);

        $plugins = new MessageConsumerManager($configObject);
        $plugins->setServiceLocator($serviceLocator);

        return $plugins;
    }
}
