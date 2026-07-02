<?php

declare(strict_types=1);

namespace Common\Controller\Lva\Factories\Adapter;

use Common\Controller\Lva\Adapters\GenericBusinessTypeAdapter;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class GenericBusinessTypeAdapterFactory implements FactoryInterface
{
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): GenericBusinessTypeAdapter
    {
        return new GenericBusinessTypeAdapter($container);
    }
}
