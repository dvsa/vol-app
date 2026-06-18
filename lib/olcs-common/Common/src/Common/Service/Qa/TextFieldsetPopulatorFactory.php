<?php

namespace Common\Service\Qa;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class TextFieldsetPopulatorFactory implements FactoryInterface
{
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): TextFieldsetPopulator
    {
        return new TextFieldsetPopulator(
            $container->get('QaTextFactory'),
            $container->get('QaTranslateableTextHandler')
        );
    }
}
