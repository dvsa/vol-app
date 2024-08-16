<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\Element\Options;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

abstract class AbstractEcmtPermitUsageRefDataSourceFactory implements FactoryInterface
{
    // override in inheriting classes
    public const TRANSFORMATIONS = [];

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return EcmtPermitUsageRefDataSource
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): EcmtPermitUsageRefDataSource
    {
        return new EcmtPermitUsageRefDataSource(
            $container->get('QaRefDataOptionsSource'),
            static::TRANSFORMATIONS
        );
    }
}
