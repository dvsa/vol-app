<?php

namespace Common\Service\Qa;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class FieldsetPopulatorFactory implements FactoryInterface
{
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): FieldsetPopulator
    {
        return new FieldsetPopulator(
            $container->get('QaFieldsetAdder'),
            $container->get('QaValidatorsAdder')
        );
    }
}
