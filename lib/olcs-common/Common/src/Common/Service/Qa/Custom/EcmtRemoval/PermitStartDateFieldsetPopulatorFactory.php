<?php

namespace Common\Service\Qa\Custom\EcmtRemoval;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class PermitStartDateFieldsetPopulatorFactory implements FactoryInterface
{
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): PermitStartDateFieldsetPopulator
    {
        return new PermitStartDateFieldsetPopulator(
            $container->get('Helper\Translation'),
            $container->get('QaCommonHtmlAdder')
        );
    }
}
