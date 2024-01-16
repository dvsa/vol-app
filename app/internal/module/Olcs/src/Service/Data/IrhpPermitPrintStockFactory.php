<?php

namespace Olcs\Service\Data;

use Common\Service\Data\AbstractDataServiceServices;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

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
}
