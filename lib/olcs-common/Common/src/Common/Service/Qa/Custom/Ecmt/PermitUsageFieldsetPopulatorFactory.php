<?php

namespace Common\Service\Qa\Custom\Ecmt;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class PermitUsageFieldsetPopulatorFactory implements FactoryInterface
{
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): PermitUsageFieldsetPopulator
    {
        return new PermitUsageFieldsetPopulator(
            $container->get('QaRadioFieldsetPopulator'),
            $container->get('QaEcmtInfoIconAdder')
        );
    }
}
