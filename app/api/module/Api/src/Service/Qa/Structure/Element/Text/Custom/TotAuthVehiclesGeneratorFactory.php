<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\Element\Text\Custom;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class TotAuthVehiclesGeneratorFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return TotAuthVehiclesGenerator
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): TotAuthVehiclesGenerator
    {
        return new TotAuthVehiclesGenerator(
            $container->get('QaTextElementGenerator')
        );
    }
}
