<?php

namespace Common\Service\Qa\Custom\Ecmt;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class NiWarningConditionalAdderFactory implements FactoryInterface
{
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): NiWarningConditionalAdder
    {
        return new NiWarningConditionalAdder(
            $container->get('QaCommonWarningAdder')
        );
    }
}
