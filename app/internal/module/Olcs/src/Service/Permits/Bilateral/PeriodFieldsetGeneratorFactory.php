<?php

namespace Olcs\Service\Permits\Bilateral;

use Psr\Container\ContainerInterface;
use Laminas\Form\Factory as FormFactory;
use Laminas\ServiceManager\Factory\FactoryInterface;

class PeriodFieldsetGeneratorFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return PeriodFieldsetGenerator
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): PeriodFieldsetGenerator
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
