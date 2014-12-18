<?php
namespace Olcs\Controller;

use Zend\ServiceManager\InitializerInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class RouteParamInitializer implements InitializerInterface
{
    /**
     * Initialize
     *
     * @param $instance
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function initialize($instance, ServiceLocatorInterface $serviceLocator)
    {
        $serviceLocator = $serviceLocator->getServiceLocator();

        $config =  $serviceLocator->get('Config');

        $listener = $serviceLocator->get('RouteParamsListener');

        $instance->getEventManager()->attach($listener);

        foreach ($config['route_param_listeners'] as $interface => $listeners) {
            if ($instance instanceof $interface) {
                foreach ($listeners as $paramListener) {
                    $listenerInstance = $serviceLocator->get($paramListener);
                    $listener->getEventManager()->attach($listenerInstance);
                }
            }
        }
    }

}