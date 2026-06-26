<?php

namespace Common\Service\Qa\Custom\Ecmt;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class RestrictedCountriesFieldsetPopulatorFactory implements FactoryInterface
{
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): RestrictedCountriesFieldsetPopulator
    {
        return new RestrictedCountriesFieldsetPopulator(
            $container->get('QaEcmtYesNoRadioFactory'),
            $container->get('QaEcmtRestrictedCountriesMultiCheckboxFactory')
        );
    }
}
