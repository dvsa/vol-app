<?php

namespace Common\Service\Lva;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class VariationLvaServiceFactory implements FactoryInterface
{
    /**
     * @param $requestedName
     * @param array|null $options
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): VariationLvaService
    {
        return new VariationLvaService(
            $container->get('Helper\Translation'),
            $container->get('Helper\Guidance'),
            $container->get('Helper\Url')
        );
    }
}
