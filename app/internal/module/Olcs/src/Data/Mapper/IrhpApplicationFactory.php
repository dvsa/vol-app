<?php

namespace Olcs\Data\Mapper;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class IrhpApplicationFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return IrhpApplication
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new IrhpApplication(
            $serviceLocator->get('QaApplicationStepsPostDataTransformer')
        );
    }
}
