<?php

namespace Olcs\Data\Mapper;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Olcs\Service\Permits\Bilateral\ApplicationFormPopulator;

class BilateralApplicationValidationModifierFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return BilateralApplicationValidationModifier
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new BilateralApplicationValidationModifier(
            $serviceLocator->get(ApplicationFormPopulator::class)
        );
    }
}
