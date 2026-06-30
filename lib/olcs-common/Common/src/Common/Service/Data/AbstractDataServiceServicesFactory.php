<?php

namespace Common\Service\Data;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class AbstractDataServiceServicesFactory implements FactoryInterface
{
    /**
     * @param $requestedName
     * @param array|null $options
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): AbstractDataServiceServices
    {
        return new AbstractDataServiceServices(
            $container->get('TransferAnnotationBuilder'),
            $container->get('QueryService'),
            $container->get('CommandService')
        );
    }
}
