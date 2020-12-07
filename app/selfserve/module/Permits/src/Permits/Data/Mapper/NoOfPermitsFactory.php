<?php

namespace Permits\Data\Mapper;

use Common\Data\Mapper\Permits\NoOfPermits as CommonNoOfPermits;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

class NoOfPermitsFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return NoOfPermits
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new NoOfPermits(
            $serviceLocator->get(CommonNoOfPermits::class)
        );
    }
}
