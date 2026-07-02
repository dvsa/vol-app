<?php

namespace Common\Service\Helper;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class GuidanceHelperServiceFactory implements FactoryInterface
{
    /**
     * @param $requestedName
     * @param array|null $options
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): GuidanceHelperService
    {
        return new GuidanceHelperService(
            $container->get('ViewHelperManager')->get('placeholder')
        );
    }
}
