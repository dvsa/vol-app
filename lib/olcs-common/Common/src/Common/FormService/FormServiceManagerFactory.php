<?php

namespace Common\FormService;

use Laminas\Mvc\Service\AbstractPluginManagerFactory;
use Laminas\ServiceManager\Config;
use Psr\Container\ContainerInterface;

/**
 * Form Service Manager Factory
 */
class FormServiceManagerFactory extends AbstractPluginManagerFactory
{
    public const PLUGIN_MANAGER_CLASS = FormServiceManager::class;

    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('Config')['form_service_manager'] ?? [];
        return new FormServiceManager($container, $config);
    }
}
