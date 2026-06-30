<?php

namespace Common\Service\Qa\Custom\Ecmt;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class NoOfPermitsBaseInsetTextGeneratorFactory implements FactoryInterface
{
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): NoOfPermitsBaseInsetTextGenerator
    {
        return new NoOfPermitsBaseInsetTextGenerator(
            $container->get('Helper\Translation'),
            $container->get('ViewHelperManager')->get('currencyFormatter')
        );
    }
}
