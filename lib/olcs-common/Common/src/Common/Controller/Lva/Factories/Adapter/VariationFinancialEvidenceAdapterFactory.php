<?php

declare(strict_types=1);

namespace Common\Controller\Lva\Factories\Adapter;

use Common\Controller\Lva\Adapters\VariationFinancialEvidenceAdapter;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class VariationFinancialEvidenceAdapterFactory implements FactoryInterface
{
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): VariationFinancialEvidenceAdapter
    {
        return new VariationFinancialEvidenceAdapter($container);
    }
}
