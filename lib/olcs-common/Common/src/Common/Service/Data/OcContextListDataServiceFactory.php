<?php

namespace Common\Service\Data;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class OcContextListDataServiceFactory implements FactoryInterface
{
    /**
     * @param $requestedName
     * @param array|null $options
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): OcContextListDataService
    {
        return new OcContextListDataService(
            $container->get('DataServiceManager')->get(LicenceOperatingCentre::class),
            $container->get('DataServiceManager')->get(ApplicationOperatingCentre::class)
        );
    }
}
