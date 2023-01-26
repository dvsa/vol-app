<?php

namespace Olcs\Service\Permits\Bilateral;

use Interop\Container\ContainerInterface;
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
    public function createService(ServiceLocatorInterface $serviceLocator) : PeriodFieldsetGenerator
    {
        return $this->__invoke($serviceLocator, PeriodFieldsetGenerator::class);
    }

    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return PeriodFieldsetGenerator
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) : PeriodFieldsetGenerator
    {
        $periodFieldsetGenerator = new PeriodFieldsetGenerator(
            new FormFactory()
        );
        $periodFieldsetGenerator->registerFieldsetPopulator(
            'standard',
            $container->get(StandardFieldsetPopulator::class)
        );
        $periodFieldsetGenerator->registerFieldsetPopulator(
            'morocco',
            $container->get(MoroccoFieldsetPopulator::class)
        );
        return $periodFieldsetGenerator;
    }
}
