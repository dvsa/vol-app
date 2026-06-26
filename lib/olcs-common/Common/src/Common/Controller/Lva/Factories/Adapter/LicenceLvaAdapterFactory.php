<?php

declare(strict_types=1);

namespace Common\Controller\Lva\Factories\Adapter;

use Common\Controller\Lva\Adapters\LicenceLvaAdapter;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class LicenceLvaAdapterFactory implements FactoryInterface
{
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): LicenceLvaAdapter
    {
        return new LicenceLvaAdapter($container);
    }
}
