<?php

namespace Olcs\Service\Permits\Bilateral;

use Interop\Container\ContainerInterface;
use Laminas\Form\Factory as FormFactory;
use Laminas\ServiceManager\Factory\FactoryInterface;

class CountryFieldsetGeneratorFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return CountryFieldsetGenerator
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) : CountryFieldsetGenerator
    {
        return new CountryFieldsetGenerator(
            $container->get('Helper\Translation'),
            new FormFactory(),
            $container->get(PeriodFieldsetGenerator::class)
        );
    }
}
