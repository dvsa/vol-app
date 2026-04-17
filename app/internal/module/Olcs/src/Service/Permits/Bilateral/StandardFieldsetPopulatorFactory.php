<?php

namespace Olcs\Service\Permits\Bilateral;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class StandardFieldsetPopulatorFactory implements FactoryInterface
{
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): StandardFieldsetPopulator
    {
        return new StandardFieldsetPopulator(
            $container->get(NoOfPermitsElementGenerator::class)
        );
    }
}
