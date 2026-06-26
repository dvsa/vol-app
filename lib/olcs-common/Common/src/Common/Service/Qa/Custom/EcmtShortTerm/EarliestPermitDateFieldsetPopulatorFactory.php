<?php

namespace Common\Service\Qa\Custom\EcmtShortTerm;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class EarliestPermitDateFieldsetPopulatorFactory implements FactoryInterface
{
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): EarliestPermitDateFieldsetPopulator
    {
        return new EarliestPermitDateFieldsetPopulator(
            $container->get('Helper\Translation'),
            $container->get('QaCommonHtmlAdder')
        );
    }
}
