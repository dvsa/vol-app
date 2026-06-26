<?php

namespace Common\Service\Qa\Custom\Bilateral;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class StandardAndCabotageFieldsetPopulatorFactory implements FactoryInterface
{
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): StandardAndCabotageFieldsetPopulator
    {
        return new StandardAndCabotageFieldsetPopulator(
            $container->get('QaBilateralRadioFactory'),
            $container->get('QaBilateralStandardAndCabotageYesNoRadioFactory'),
            $container->get('QaBilateralYesNoRadioOptionsApplier'),
            $container->get('QaBilateralStandardYesNoValueOptionsGenerator')
        );
    }
}
