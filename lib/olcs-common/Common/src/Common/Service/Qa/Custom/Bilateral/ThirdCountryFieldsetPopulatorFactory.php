<?php

namespace Common\Service\Qa\Custom\Bilateral;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class ThirdCountryFieldsetPopulatorFactory implements FactoryInterface
{
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): ThirdCountryFieldsetPopulator
    {
        return new ThirdCountryFieldsetPopulator(
            $container->get('Helper\Translation'),
            $container->get('QaBilateralYesNoWithMarkupForNoPopulator'),
            $container->get('QaBilateralStandardYesNoValueOptionsGenerator')
        );
    }
}
