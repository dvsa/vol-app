<?php

namespace Permits\Data\Mapper;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class FeeListFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return FeeList
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new FeeList(
            $serviceLocator->get('Helper\Translation'),
            $serviceLocator->get('ViewHelperManager')->get('currencyFormatter'),
            $serviceLocator->get(EcmtNoOfPermits::class)
        );
    }
}
