<?php

namespace Common\Service\Qa\Custom\Bilateral;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class CabotageOnlyFieldsetPopulatorFactory implements FactoryInterface
{
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): CabotageOnlyFieldsetPopulator
    {
        return new CabotageOnlyFieldsetPopulator(
            $container->get('Helper\Translation'),
            $container->get('QaBilateralYesNoWithMarkupForNoPopulator'),
            $container->get('QaBilateralStandardYesNoValueOptionsGenerator')
        );
    }
}
