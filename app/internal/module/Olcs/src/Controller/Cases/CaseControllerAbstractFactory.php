<?php

namespace Olcs\Controller\Cases;

use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Mvc\MvcEvent;

class CaseControllerAbstractFactory implements AbstractFactoryInterface
{
    /**
     * Determine if we can create a service with name
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @param $name
     * @param $requestedName
     * @return bool
     */
    public function canCreateServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        $config =  $serviceLocator->getServiceLocator()->get('Config');
        return isset($config['controllers']['case_controllers'][$requestedName]);
    }

    /**
     * Create service with name
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @param $name
     * @param $requestedName
     * @return mixed
     */
    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        $serviceLocator = $serviceLocator->getServiceLocator();

        $config =  $serviceLocator->get('Config');
        $class = $config['controllers']['case_controllers'][$requestedName];

        $listener = $serviceLocator->get('RouteParamsListener');

        $controller = new $class;
        $controller->getEventManager()->attach($listener);

        if (isset($config['route_param_listeners']['case_controllers'])) {
            foreach ($config['route_param_listeners']['case_controllers'] as $paramListener) {
                $listenerInstance = $serviceLocator->get($paramListener);
                $listener->getEventManager()->attach($listenerInstance);
            }
        }

        return $controller;
    }
}
