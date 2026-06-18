<?php

namespace Common\Service\Table\Formatter;

use Common\Rbac\Service\Permission;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class InternalLicencePermitReferenceFactory implements FactoryInterface
{
    /**
     * @param  $requestedName
     * @param  array|null         $options
     */
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): InternalLicencePermitReference
    {
        $urlHelper = $container->get('Helper\Url');
        $permissionService = $container->get(Permission::class);
        return new InternalLicencePermitReference($urlHelper, $permissionService);
    }
}
