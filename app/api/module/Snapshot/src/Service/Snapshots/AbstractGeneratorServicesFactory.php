<?php

namespace Dvsa\Olcs\Snapshot\Service\Snapshots;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class AbstractGeneratorServicesFactory implements FactoryInterface
{
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new AbstractGeneratorServices(
            $container->get('ViewRenderer')
        );
    }
}
