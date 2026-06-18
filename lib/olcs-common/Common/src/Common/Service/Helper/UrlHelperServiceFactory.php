<?php

namespace Common\Service\Helper;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class UrlHelperServiceFactory implements FactoryInterface
{
    /**
     * @param $requestedName
     * @param array|null $options
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): UrlHelperService
    {
        return new UrlHelperService(
            $container->get('ViewHelperManager'),
            $container->get('config')
        );
    }
}
