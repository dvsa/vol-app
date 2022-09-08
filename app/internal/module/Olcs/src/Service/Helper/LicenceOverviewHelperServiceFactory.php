<?php

namespace Olcs\Service\Helper;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

/**
 * LicenceOverviewHelperServiceFactory
 */
class LicenceOverviewHelperServiceFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     *
     * @return LicenceOverviewHelperService
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): LicenceOverviewHelperService
    {
        return new LicenceOverviewHelperService(
            $container->get('Helper\Url')
        );
    }

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $services
     *
     * @return LicenceOverviewHelperService
     */
    public function createService(ServiceLocatorInterface $services): LicenceOverviewHelperService
    {
        return $this($services, LicenceOverviewHelperService::class);
    }
}
