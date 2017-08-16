<?php
namespace Olcs\Controller;

use Olcs\Controller\Interfaces\CaseControllerInterface;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\ServiceManager\InitializerInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class RouteParamInitializer
 * @package Olcs\Controller
 */
class RouteParamInitializer implements InitializerInterface
{
    /**
     * Initialize
     *
     * @param AbstractActionController $instance       Controller
     * @param ServiceLocatorInterface  $serviceLocator Service locator
     *
     * @return mixed
     */
    public function initialize($instance, ServiceLocatorInterface $serviceLocator)
    {
        $serviceLocator = $serviceLocator->getServiceLocator();

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
}
