<?php

namespace Dvsa\Olcs\Api\Service\Permits\Common;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class StockBasedPermitTypeConfigProviderFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return StockBasedPermitTypeConfigProvider
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): StockBasedPermitTypeConfigProvider
    {
        return new StockBasedPermitTypeConfigProvider(
            $container->get('RepositoryServiceManager')->get('IrhpPermitStock'),
            $container->get('PermitsCommonTypeBasedPermitTypeConfigProvider')
        );
    }
}
