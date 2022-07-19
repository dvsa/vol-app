<?php

namespace Olcs\Service\Processing;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

class DashboardProcessingServiceFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     *
     * @return DashboardProcessingService
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): DashboardProcessingService
    {
        return new DashboardProcessingService(
            $container->get('Table')
        );
    }

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $services
     *
     * @return DashboardProcessingService
     */
    public function createService(ServiceLocatorInterface $services)
    {
        return $this($services, DashboardProcessingService::class);
    }
}
