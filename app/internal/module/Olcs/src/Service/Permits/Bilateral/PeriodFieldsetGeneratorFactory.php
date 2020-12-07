<?php

namespace Olcs\Service\Permits\Bilateral;

use Laminas\Form\Factory as FormFactory;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

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
