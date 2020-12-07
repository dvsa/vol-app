<?php

namespace Olcs\Service\Permits\Bilateral;

use Laminas\Form\Factory as FormFactory;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

class CountryFieldsetGeneratorFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return CountryFieldsetGenerator
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new CountryFieldsetGenerator(
            $serviceLocator->get('Helper\Translation'),
            new FormFactory(),
            $serviceLocator->get(PeriodFieldsetGenerator::class)
        );
    }
}
