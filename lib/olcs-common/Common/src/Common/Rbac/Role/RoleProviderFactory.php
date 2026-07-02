<?php

namespace Common\Rbac\Role;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class RoleProviderFactory implements FactoryInterface
{
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): RoleProvider
    {
        return new RoleProvider($container->get('QuerySender'));
    }
}
