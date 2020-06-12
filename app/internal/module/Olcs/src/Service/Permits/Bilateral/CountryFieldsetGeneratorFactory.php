<?php

namespace Olcs\Service\Permits\Bilateral;

use Zend\Form\Factory as FormFactory;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

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
