<?php
namespace Olcs\Controller;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\InitializerInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

/**
 * Class RouteParamInitializer
 * @package Olcs\Controller
 */
class RouteParamInitializer implements InitializerInterface
{
    /**
     * {@inheritdoc}
     */
    public function __invoke(ContainerInterface $container, $instance)
    {
        $serviceLocator = $container->getServiceLocator();

        $config =  $serviceLocator->get('Config');

        $listener = $serviceLocator->get('RouteParamsListener');
        $instance->getEventManager()->attach($listener);

        $headerSearchListener = $serviceLocator->get('HeaderSearchListener');
        $instance->getEventManager()->attach($headerSearchListener);

        $navigationToggleListener = $serviceLocator->get('NavigationToggleListener');
        $instance->getEventManager()->attach($navigationToggleListener);

        foreach ($config['route_param_listeners'] as $interface => $listeners) {
            if ($instance instanceof $interface) {
                foreach ($listeners as $paramListener) {
                    $listenerInstance = $serviceLocator->get($paramListener);
                    $listener->getEventManager()->attach($listenerInstance);
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     * @todo OLCS-28149
     */
    public function initialize($instance, ServiceLocatorInterface $serviceLocator)
    {
        return $this($serviceLocator, $instance);
    }
}
