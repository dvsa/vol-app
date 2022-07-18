<?php

namespace Olcs\Service\Processing;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

class CreateVariationProcessingServiceFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     *
     * @return CreateVariationProcessingService
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

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $services
     *
     * @return CreateVariationProcessingService
     */
    public function createService(ServiceLocatorInterface $services)
    {
        return $this($services, CreateVariationProcessingService::class);
    }
}
