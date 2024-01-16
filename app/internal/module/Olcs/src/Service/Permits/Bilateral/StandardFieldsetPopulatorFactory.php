<?php

namespace Olcs\Service\Permits\Bilateral;

use Interop\Container\Containerinterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class StandardFieldsetPopulatorFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): StandardFieldsetPopulator
    {
        return new StandardFieldsetPopulator(
            $container->get(NoOfPermitsElementGenerator::class)
        );
    }
}
