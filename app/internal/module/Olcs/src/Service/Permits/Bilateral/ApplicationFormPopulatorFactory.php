<?php

namespace Olcs\Service\Permits\Bilateral;

use Zend\Form\Factory as FormFactory;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

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
