<?php

namespace Olcs\Service\Permits\Bilateral;

use Zend\Form\Factory as FormFactory;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class PeriodFieldsetGeneratorFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return PeriodFieldsetGenerator
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new PeriodFieldsetGenerator(
            new FormFactory(),
            $serviceLocator->get(NoOfPermitsElementGenerator::class)
        );
    }
}
