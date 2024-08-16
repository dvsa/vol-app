<?php

namespace Dvsa\Olcs\Api\Service\Permits\Common;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class TypeBasedPermitTypeConfigProviderFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return TypeBasedPermitTypeConfigProvider
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): TypeBasedPermitTypeConfigProvider
    {
        return new TypeBasedPermitTypeConfigProvider(
            $container->get('config')
        );
    }
}
