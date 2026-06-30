<?php

declare(strict_types=1);

namespace Common\Controller\Lva\Factories\Adapter;

use Common\Controller\Lva\Adapters\VariationConditionsUndertakingsAdapter;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class VariationConditionsUndertakingsAdapterFactory implements FactoryInterface
{
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): VariationConditionsUndertakingsAdapter
    {
        return new VariationConditionsUndertakingsAdapter($container);
    }
}
