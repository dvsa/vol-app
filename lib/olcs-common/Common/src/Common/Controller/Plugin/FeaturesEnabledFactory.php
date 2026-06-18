<?php

namespace Common\Controller\Plugin;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class FeaturesEnabledFactory implements FactoryInterface
{
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): FeaturesEnabled
    {
        return new FeaturesEnabled(
            $container->get('QuerySender')
        );
    }
}
