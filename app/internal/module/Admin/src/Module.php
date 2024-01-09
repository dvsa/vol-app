<?php

/**
 * Module
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
namespace Admin;

use Laminas\Mvc\ModuleRouteListener;
use Laminas\Mvc\MvcEvent;

/**
 * Module
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class Module
{

    public function onBootstrap(MvcEvent $event)
    {
        $eventManager = $event->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
    }

    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }
}
