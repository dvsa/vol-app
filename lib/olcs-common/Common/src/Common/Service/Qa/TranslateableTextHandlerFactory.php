<?php

namespace Common\Service\Qa;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class TranslateableTextHandlerFactory implements FactoryInterface
{
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): TranslateableTextHandler
    {
        return new TranslateableTextHandler(
            $container->get('QaFormattedTranslateableTextParametersGenerator'),
            $container->get('Helper\Translation')
        );
    }
}
