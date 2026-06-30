<?php

namespace Common\Service\Qa\DataTransformer;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class ApplicationStepsPostDataTransformerFactory implements FactoryInterface
{
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): ApplicationStepsPostDataTransformer
    {
        return new ApplicationStepsPostDataTransformer(
            $container->get('QaDataTransformerProvider')
        );
    }
}
