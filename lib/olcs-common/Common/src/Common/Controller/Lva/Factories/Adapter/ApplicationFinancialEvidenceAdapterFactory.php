<?php

declare(strict_types=1);

namespace Common\Controller\Lva\Factories\Adapter;

use Common\Controller\Lva\Adapters\ApplicationFinancialEvidenceAdapter;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class ApplicationFinancialEvidenceAdapterFactory implements FactoryInterface
{
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): ApplicationFinancialEvidenceAdapter
    {
        return new ApplicationFinancialEvidenceAdapter($container);
    }
}
