<?php

namespace Permits\Data\Mapper;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class AvailableYearsFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return AvailableYears
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new AvailableYears(
            $serviceLocator->get('Helper\Translation')
        );
    }
}
