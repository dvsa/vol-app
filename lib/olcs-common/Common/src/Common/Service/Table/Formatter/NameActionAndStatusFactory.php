<?php

namespace Common\Service\Table\Formatter;

use Common\Rbac\Service\Permission;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class NameActionAndStatusFactory implements FactoryInterface
{
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): NameActionAndStatus
    {
        return new NameActionAndStatus($container->get(Permission::class));
    }
}
