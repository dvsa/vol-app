<?php

namespace Permits\Data\Mapper;

use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

class EcmtNoOfPermitsFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return EcmtNoOfPermits
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new EcmtNoOfPermits(
            $serviceLocator->get('Helper\Translation')
        );
    }
}
