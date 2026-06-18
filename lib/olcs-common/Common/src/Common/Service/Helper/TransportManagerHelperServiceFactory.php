<?php

namespace Common\Service\Helper;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class TransportManagerHelperServiceFactory implements FactoryInterface
{
    /**
     * @param $requestedName
     * @param array|null $options
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): TransportManagerHelperService
    {
        return new TransportManagerHelperService(
            $container->get('TransferAnnotationBuilder'),
            $container->get('QueryService'),
            $container->get('Helper\Form'),
            $container->get('Helper\Date'),
            $container->get('Helper\Translation'),
            $container->get('Helper\Url'),
            $container->get('Table')
        );
    }
}
