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
        $periodFieldsetGenerator = new PeriodFieldsetGenerator(
            new FormFactory()
        );

        $periodFieldsetGenerator->registerFieldsetPopulator(
            'standard',
            $serviceLocator->get(StandardFieldsetPopulator::class)
        );

        $periodFieldsetGenerator->registerFieldsetPopulator(
            'morocco',
            $serviceLocator->get(MoroccoFieldsetPopulator::class)
        );

        return $periodFieldsetGenerator;
    }
}
