<?php

namespace Common\Service;

use Psr\Container\ContainerInterface;
use Laminas\Navigation\Navigation;
use Laminas\Navigation\Service\ConstructedNavigationFactory;

class NavigationFactory
{
    public function __construct(private ContainerInterface $container)
    {
    }

    public function getNavigation(array $config): Navigation
    {
        $factory = new ConstructedNavigationFactory($config);
        return $factory->__invoke($this->container, Navigation::class);
    }
}
