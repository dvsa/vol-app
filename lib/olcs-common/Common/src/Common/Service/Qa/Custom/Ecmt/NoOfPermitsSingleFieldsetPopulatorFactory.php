<?php

namespace Common\Service\Qa\Custom\Ecmt;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class NoOfPermitsSingleFieldsetPopulatorFactory implements FactoryInterface
{
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): NoOfPermitsSingleFieldsetPopulator
    {
        return new NoOfPermitsSingleFieldsetPopulator(
            $container->get('Helper\Translation'),
            $container->get('QaEcmtNoOfPermitsBaseInsetTextGenerator'),
            $container->get('QaCommonHtmlAdder')
        );
    }
}
