<?php

namespace Common\Service\Table;

use Common\Rbac\Service\Permission;
use Common\Service\Table\Formatter\FormatterPluginManager;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

/**
 * Laminas Framework Compatible Table Builder Factory. Creates an instance of
 * TableBuilder and passes in the main service locator
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
class TableBuilderFactory implements FactoryInterface
{
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new TableBuilder(
            $container,
            $container->get(Permission::class),
            $container->get('translator'),
            $container->get('Helper\Url'),
            $container->get('Config'),
            $container->get(FormatterPluginManager::class)
        );
    }
}
