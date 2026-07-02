<?php

namespace Common\Service\Data;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class LicenceOperatingCentreFactory implements FactoryInterface
{
    /**
     * @param $requestedName
     * @param array|null $options
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): LicenceOperatingCentre
    {
        return new LicenceOperatingCentre(
            $container->get('DataServiceManager')->get(AbstractDataServiceServices::class),
            $container->get('DataServiceManager')->get(Licence::class)
        );
    }
}
