<?php

namespace Olcs\Controller\Factory;

use Interop\Container\ContainerInterface;
use Laminas\EventManager\EventManagerInterface;
use Olcs\Listener\HeaderSearch;
use Olcs\Listener\NavigationToggle;
use Olcs\Listener\RouteParams;

trait AttachListenersTrait
{
    public function attachListeners(ContainerInterface $container, EventManagerInterface $eventManager, $controllerInstance)
    {
        $config = $container->get('Config');

        // Attach default listeners
        $eventManager->attach($container->get(RouteParams::class));
        $eventManager->attach($container->get(HeaderSearch::class));
        $eventManager->attach($container->get(NavigationToggle::class));

        // Attach listeners based on the controller's interface
        foreach ($config['route_param_listeners'] as $interface => $listeners) {
            if ($controllerInstance instanceof $interface) {
                foreach ($listeners as $listener) {
                    $listenerInstance = $container->get($listener);
                    $eventManager->attach($listenerInstance);
                }
            }
        }
    }
}
