<?php

namespace Common\Service\Qa\DataTransformer;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class DataTransformerProviderFactory implements FactoryInterface
{
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): DataTransformerProvider
    {
        $dataTransformerProvider = new DataTransformerProvider();

        $dataTransformerProvider->registerTransformer(
            'number-of-permits-either',
            $container->get('QaEcmtNoOfPermitsSingleDataTransformer')
        );

        $dataTransformerProvider->registerTransformer(
            'number-of-permits-both',
            $container->get('QaEcmtNoOfPermitsSingleDataTransformer')
        );

        return $dataTransformerProvider;
    }
}
