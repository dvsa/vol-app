<?php

namespace Common\Service\Data;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

/**
 * StaticListFactory
 */
class StaticListFactory implements FactoryInterface
{
    /**
     * @param $requestedName
     * @param array|null $options
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): StaticList
    {
        return new StaticList(
            $container->get(AbstractDataServiceServices::class),
            $container->get('Config')
        );
    }
}
