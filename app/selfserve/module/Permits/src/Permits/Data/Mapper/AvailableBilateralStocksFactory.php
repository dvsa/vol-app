<?php

namespace Permits\Data\Mapper;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

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
