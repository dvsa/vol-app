<?php

namespace Olcs\Service\Qa;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class FieldsetPopulatorFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return FieldsetPopulator
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new FieldsetPopulator(
            $serviceLocator->get('QaFieldsetAdder')
        );
    }
}
