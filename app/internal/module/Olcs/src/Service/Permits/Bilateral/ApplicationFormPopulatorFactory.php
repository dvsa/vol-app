<?php

namespace Olcs\Service\Permits\Bilateral;

use Psr\Container\ContainerInterface;
use Laminas\Form\Factory as FormFactory;
use Laminas\ServiceManager\Factory\FactoryInterface;

class ApplicationFormPopulatorFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return ApplicationFormPopulator
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): ApplicationFormPopulator
    {
        return new ApplicationFormPopulator(
            new FormFactory(),
            $container->get(CountryFieldsetGenerator::class)
        );
    }
}
