<?php

namespace Dvsa\Olcs\Api\Service\Permits\Availability;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class StockLicenceMaxPermittedCounterFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return StockLicenceMaxPermittedCounter
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): StockLicenceMaxPermittedCounter
    {
        return new StockLicenceMaxPermittedCounter(
            $container->get('RepositoryServiceManager')->get('IrhpPermit')
        );
    }
}
