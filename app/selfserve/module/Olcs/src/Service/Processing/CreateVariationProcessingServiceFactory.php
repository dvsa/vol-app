<?php

namespace Olcs\Service\Processing;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class CreateVariationProcessingServiceFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     *
     * @return CreateVariationProcessingService
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): CreateVariationProcessingService
    {
        return new CreateVariationProcessingService(
            $container->get('Helper\Form'),
            $container->get('TransferAnnotationBuilder'),
            $container->get('CommandService')
        );
    }
}
