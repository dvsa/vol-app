<?php

namespace Common\Service\Qa\Custom\Ecmt;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class AnnualTripsAbroadFieldsetPopulatorFactory implements FactoryInterface
{
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): AnnualTripsAbroadFieldsetPopulator
    {
        return new AnnualTripsAbroadFieldsetPopulator(
            $container->get('QaTextFieldsetPopulator'),
            $container->get('Helper\Translation'),
            $container->get('QaEcmtNiWarningConditionalAdder'),
            $container->get('QaCommonHtmlAdder')
        );
    }
}
