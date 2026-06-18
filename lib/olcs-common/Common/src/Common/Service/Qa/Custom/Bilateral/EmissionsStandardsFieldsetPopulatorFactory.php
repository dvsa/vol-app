<?php

namespace Common\Service\Qa\Custom\Bilateral;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class EmissionsStandardsFieldsetPopulatorFactory implements FactoryInterface
{
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): EmissionsStandardsFieldsetPopulator
    {
        return new EmissionsStandardsFieldsetPopulator(
            $container->get('QaCommonWarningAdder'),
            $container->get('Helper\Translation'),
            $container->get('QaBilateralYesNoWithMarkupForNoPopulator'),
            $container->get('QaBilateralYesNoValueOptionsGenerator')
        );
    }
}
