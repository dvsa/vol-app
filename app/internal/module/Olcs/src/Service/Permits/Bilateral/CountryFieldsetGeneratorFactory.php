<?php

namespace Olcs\Service\Permits\Bilateral;

use Interop\Container\ContainerInterface;
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
    public function createService(ServiceLocatorInterface $serviceLocator) : CountryFieldsetGenerator
    {
        return $this->__invoke($serviceLocator, CountryFieldsetGenerator::class);
    }

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
