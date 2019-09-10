<?php

namespace Permits\Data\Mapper;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class AcceptOrDeclinePermitsFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return AcceptOrDeclinePermits
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new AcceptOrDeclinePermits(
            $serviceLocator->get('Helper\Translation'),
            $serviceLocator->get('Helper\Url'),
            $serviceLocator->get(ApplicationFees::class),
            $serviceLocator->get('ViewHelperManager')->get('currencyFormatter'),
            $serviceLocator->get(EcmtNoOfPermits::class)
        );
    }
}
