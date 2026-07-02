<?php

namespace Common\Service\Qa\Custom\Bilateral;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class YesNoWithMarkupForNoPopulatorFactory implements FactoryInterface
{
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): YesNoWithMarkupForNoPopulator
    {
        return new YesNoWithMarkupForNoPopulator(
            $container->get('QaRadioFactory'),
            $container->get('QaBilateralYesNoRadioOptionsApplier'),
            $container->get('QaCommonHtmlAdder')
        );
    }
}
