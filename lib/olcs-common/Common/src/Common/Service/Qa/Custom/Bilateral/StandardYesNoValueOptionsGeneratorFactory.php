<?php

namespace Common\Service\Qa\Custom\Bilateral;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class StandardYesNoValueOptionsGeneratorFactory implements FactoryInterface
{
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): StandardYesNoValueOptionsGenerator
    {
        return new StandardYesNoValueOptionsGenerator(
            $container->get('QaBilateralYesNoValueOptionsGenerator')
        );
    }
}
