<?php

namespace Permits\Data\Mapper;

use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

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
