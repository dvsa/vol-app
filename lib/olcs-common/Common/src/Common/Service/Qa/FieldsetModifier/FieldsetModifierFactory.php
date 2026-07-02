<?php

namespace Common\Service\Qa\FieldsetModifier;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class FieldsetModifierFactory implements FactoryInterface
{
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): FieldsetModifier
    {
        $fieldsetModifier = new FieldsetModifier();

        $fieldsetModifier->registerModifier(
            $container->get('QaRoadWorthinessMakeAndModelFieldsetModifier')
        );

        return $fieldsetModifier;
    }
}
