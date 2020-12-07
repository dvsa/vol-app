<?php

namespace Olcs\Data\Mapper;

use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
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
