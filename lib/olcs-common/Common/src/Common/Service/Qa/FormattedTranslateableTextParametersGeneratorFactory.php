<?php

namespace Common\Service\Qa;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class FormattedTranslateableTextParametersGeneratorFactory implements FactoryInterface
{
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): FormattedTranslateableTextParametersGenerator
    {
        return new FormattedTranslateableTextParametersGenerator(
            $container->get('QaTranslateableTextParameterHandler')
        );
    }
}
