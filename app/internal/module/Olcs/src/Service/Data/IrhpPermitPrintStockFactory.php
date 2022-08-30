<?php

namespace Olcs\Service\Data;

use Common\Service\Data\AbstractDataServiceServices;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

/**
 * IrhpPermitPrintStockFactory
 */
class IrhpPermitPrintStockFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     *
     * @return IrhpPermitPrintStock
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): IrhpPermitPrintStock
    {
        return new IrhpPermitPrintStock(
            $container->get(AbstractDataServiceServices::class),
            $container->get('Helper\Translation')
        );
    }

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $services
     *
     * @return IrhpPermitPrintStock
     */
    public function createService(ServiceLocatorInterface $services): IrhpPermitPrintStock
    {
        return $this($services, IrhpPermitPrintStock::class);
    }
}
