<?php

namespace Olcs\Service\Permits\Bilateral;

use Interop\Container\ContainerInterface;
use Laminas\Form\Factory as FormFactory;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

class ApplicationFormPopulatorFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return ApplicationFormPopulator
     */
    public function createService(ServiceLocatorInterface $serviceLocator) : ApplicationFormPopulator
    {
        return $this->__invoke($serviceLocator, ApplicationFormPopulator::class);
    }

    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return ApplicationFormPopulator
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) : ApplicationFormPopulator
    {
        return new ApplicationFormPopulator(
            new FormFactory(),
            $container->get(CountryFieldsetGenerator::class)
        );
    }
}
