<?php

namespace Common\Service\Qa\Custom\Ecmt;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class InternationalJourneysFieldsetPopulatorFactory implements FactoryInterface
{
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): InternationalJourneysFieldsetPopulator
    {
        return new InternationalJourneysFieldsetPopulator(
            $container->get('QaRadioFieldsetPopulator'),
            $container->get('QaEcmtNiWarningConditionalAdder')
        );
    }
}
