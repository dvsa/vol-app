<?php

declare(strict_types=1);

namespace Common\Rbac\Service;

use Laminas\ServiceManager\Factory\FactoryInterface;
use LmcRbacMvc\Service\AuthorizationService;
use Psr\Container\ContainerInterface;

class PermissionFactory implements FactoryInterface
{
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): Permission
    {
        return new Permission(
            $container->get(AuthorizationService::class)
        );
    }
}
