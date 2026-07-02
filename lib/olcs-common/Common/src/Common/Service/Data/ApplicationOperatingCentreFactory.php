<?php

namespace Common\Service\Data;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class ApplicationOperatingCentreFactory implements FactoryInterface
{
    /**
     * @param $requestedName
     * @param array|null $options
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): ApplicationOperatingCentre
    {
        return new ApplicationOperatingCentre(
            $container->get('DataServiceManager')->get(AbstractDataServiceServices::class),
            $container->get('DataServiceManager')->get(Application::class)
        );
    }
}
