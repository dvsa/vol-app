<?php

namespace Common\Service\Data;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

/**
 * RefDataFactory
 */
class RefDataFactory implements FactoryInterface
{
    /**
     * @param $requestedName
     * @param array|null $options
     *
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new $requestedName(
            $container->get(RefDataServices::class)
        );
    }
}
