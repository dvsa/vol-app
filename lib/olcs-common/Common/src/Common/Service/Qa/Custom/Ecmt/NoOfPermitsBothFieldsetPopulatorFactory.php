<?php

namespace Common\Service\Qa\Custom\Ecmt;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class NoOfPermitsBothFieldsetPopulatorFactory implements FactoryInterface
{
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): NoOfPermitsBothFieldsetPopulator
    {
        return new NoOfPermitsBothFieldsetPopulator(
            $container->get('Helper\Translation'),
            $container->get('QaEcmtNoOfPermitsBaseInsetTextGenerator'),
            $container->get('QaCommonHtmlAdder')
        );
    }
}
