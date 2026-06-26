<?php

namespace Common\Service\Qa\Custom\Common;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class WarningAdderFactory implements FactoryInterface
{
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): WarningAdder
    {
        return new WarningAdder(
            $container->get('ViewHelperManager')->get('partial'),
            $container->get('QaCommonHtmlAdder')
        );
    }
}
