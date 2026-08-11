<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Service\Letter;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

/**
 * Factory for SectionVariantResolver.
 */
class SectionVariantResolverFactory implements FactoryInterface
{
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): SectionVariantResolver
    {
        return new SectionVariantResolver();
    }
}
