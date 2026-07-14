<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Service\Letter;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

/**
 * Factory for MasterTemplateResolver (VOL-7305).
 */
class MasterTemplateResolverFactory implements FactoryInterface
{
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): MasterTemplateResolver
    {
        $repoManager = $container->get('RepositoryServiceManager');

        return new MasterTemplateResolver(
            $repoManager->get('MasterTemplate')
        );
    }
}
