<?php

namespace Olcs\Controller\Factory;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Olcs\Controller\StaticAssetsController;

class StaticAssetsControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): StaticAssetsController
    {
        $config = $container->get('Config');
        return new StaticAssetsController($config);
    }
}
