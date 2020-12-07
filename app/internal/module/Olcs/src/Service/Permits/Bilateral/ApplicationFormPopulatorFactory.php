<?php

namespace Olcs\Service\Permits\Bilateral;

use Laminas\Form\Factory as FormFactory;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

class ApplicationFormPopulatorFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return ApplicationFormPopulator
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new ApplicationFormPopulator(
            new FormFactory(),
            $serviceLocator->get(CountryFieldsetGenerator::class)
        );
    }
}
