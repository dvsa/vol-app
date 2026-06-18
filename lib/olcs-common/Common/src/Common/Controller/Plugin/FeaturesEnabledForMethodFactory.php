<?php

namespace Common\Controller\Plugin;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class FeaturesEnabledForMethodFactory implements FactoryInterface
{
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): FeaturesEnabledForMethod
    {
        return new FeaturesEnabledForMethod(
            $container->get('QuerySender')
        );
    }
}
