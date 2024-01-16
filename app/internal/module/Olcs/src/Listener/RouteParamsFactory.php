<?php

namespace Olcs\Listener;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class RouteParamsFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('Config');
        $routeParamsConfig = $config['route_param_listeners'] ?? [];

        return new RouteParams($routeParamsConfig, $container);
    }
}
