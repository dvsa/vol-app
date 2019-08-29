<?php

namespace Permits\Data\Mapper;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class IrhpCheckAnswersFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return IrhpCheckAnswers
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new IrhpCheckAnswers(
            $serviceLocator->get('Helper\Translation'),
            $serviceLocator->get(EcmtNoOfPermits::class)
        );
    }
}
