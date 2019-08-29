<?php

namespace Permits\Data\Mapper;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class IrhpApplicationFeeSummaryFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return IrhpApplicationFeeSummary
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $viewHelperManager = $serviceLocator->get('ViewHelperManager');

        return new IrhpApplicationFeeSummary(
            $serviceLocator->get('Helper\Translation'),
            $serviceLocator->get(EcmtNoOfPermits::class),
            $viewHelperManager->get('status'),
            $viewHelperManager->get('currencyFormatter')
        );
    }
}
