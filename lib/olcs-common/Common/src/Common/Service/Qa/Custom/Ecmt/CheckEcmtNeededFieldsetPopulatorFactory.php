<?php

namespace Common\Service\Qa\Custom\Ecmt;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class CheckEcmtNeededFieldsetPopulatorFactory implements FactoryInterface
{
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): CheckEcmtNeededFieldsetPopulator
    {
        return new CheckEcmtNeededFieldsetPopulator(
            $container->get('QaCheckboxFieldsetPopulator'),
            $container->get('QaEcmtInfoIconAdder')
        );
    }
}
