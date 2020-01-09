<?php

namespace Permits\Data\Mapper;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class CheckAnswersFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return CheckAnswers
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new CheckAnswers(
            $serviceLocator->get('Helper\Translation'),
            $serviceLocator->get(EcmtNoOfPermits::class)
        );
    }
}
