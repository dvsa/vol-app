<?php

namespace Dvsa\Olcs\Api\Service\EditorJs;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

/**
 * Factory for EditorJS Converter Service
 */
class ConverterServiceFactory implements FactoryInterface
{
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): ConverterService
    {
        return new ConverterService();
    }
}
