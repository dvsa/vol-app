<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Service\Letter\SectionRenderer;

use Dvsa\Olcs\Api\Service\EditorJs\ConverterService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

/**
 * Factory for creating section renderer instances
 *
 * All renderers share the same dependencies (ConverterService),
 * so a single factory handles all renderer types.
 */
class SectionRendererFactory implements FactoryInterface
{
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        array $options = null
    ): SectionRendererInterface {
        $converterService = $container->get(ConverterService::class);

        return new $requestedName($converterService);
    }
}
