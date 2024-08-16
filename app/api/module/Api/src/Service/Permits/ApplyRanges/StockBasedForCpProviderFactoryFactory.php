<?php

namespace Dvsa\Olcs\Api\Service\Permits\ApplyRanges;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class StockBasedForCpProviderFactoryFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return StockBasedForCpProviderFactory
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): StockBasedForCpProviderFactory
    {
        return new StockBasedForCpProviderFactory(
            $container->get('PermitsCommonStockBasedRestrictedCountryIdsProvider'),
            $container->get('PermitsApplyRangesForCpProviderFactory')
        );
    }
}
