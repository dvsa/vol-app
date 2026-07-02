<?php

namespace Common\Service\Data;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

/**
 * AbstractListDataServiceServicesFactory
 */
class AbstractListDataServiceServicesFactory implements FactoryInterface
{
    /**
     * @param $requestedName
     * @param array|null $options
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): AbstractListDataServiceServices
    {
        return new AbstractListDataServiceServices(
            $container->get(AbstractDataServiceServices::class)
        );
    }
}
