<?php

namespace Olcs\Service\Helper;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

/**
 * ApplicationOverviewHelperServiceFactory
 */
class ApplicationOverviewHelperServiceFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     *
     * @return ApplicationOverviewHelperService
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): ApplicationOverviewHelperService
    {
        return new ApplicationOverviewHelperService(
            $container->get('Helper\LicenceOverview'),
            $container->get('Helper\Url')
        );
    }

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $services
     *
     * @return ApplicationOverviewHelperService
     */
    public function createService(ServiceLocatorInterface $services): ApplicationOverviewHelperService
    {
        return $this($services, ApplicationOverviewHelperService::class);
    }
}
