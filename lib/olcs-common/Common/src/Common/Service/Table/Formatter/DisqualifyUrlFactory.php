<?php

namespace Common\Service\Table\Formatter;

use Common\Rbac\Service\Permission;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class DisqualifyUrlFactory implements FactoryInterface
{
    /**
     * @param  $requestedName
     * @param  array|null         $options
     * @return DisqualifyUrl
     */
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $urlHelper = $container->get('Helper\Url');
        $router = $container->get('Router');
        $request = $container->get('Request');
        $permissionService = $container->get(Permission::class);
        return new DisqualifyUrl($urlHelper, $router, $request, $permissionService);
    }
}
