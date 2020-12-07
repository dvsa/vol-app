<?php

namespace Permits\Data\Mapper;

use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

class AvailableBilateralStocksFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return AvailableBilateralStocks
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new AvailableBilateralStocks(
            $serviceLocator->get('Helper\Translation')
        );
    }
}
