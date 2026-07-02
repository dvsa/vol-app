<?php

declare(strict_types=1);

namespace Common\Controller\Lva\Factories\Adapter;

use Common\Controller\Lva\Adapters\LicenceConditionsUndertakingsAdapter;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class LicenceConditionsUndertakingsAdapterFactory implements FactoryInterface
{
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): LicenceConditionsUndertakingsAdapter
    {
        return new LicenceConditionsUndertakingsAdapter($container);
    }
}
