<?php

/**
 * Module
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
namespace Admin;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;

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
        if (!defined('DATE_FORMAT')) {
            define('DATE_FORMAT', 'd/m/Y');
        }
        if (!defined('DATETIME_FORMAT')) {
            define('DATETIME_FORMAT', 'd/m/Y H:i');
        }
        if (!defined('DATETIMESEC_FORMAT')) {
            define('DATETIMESEC_FORMAT', 'd/m/Y H:i:s');
        }
    }

    public function getConfig()
    {
        $base = include __DIR__ . '/config/module.config.php';
        return $base;
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/',
                ),
            ),
        );
    }
}
