<?php

/**
 * Module
 *
 * @author Someone <someone@valtech.co.uk>
 */
namespace Olcs;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;

/**
 * Module
 *
 * @author Someone <someone@valtech.co.uk>
 */
class Module
{

    public function onBootstrap(MvcEvent $e)
    {
        $eventManager = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);

        $e->getApplication()->getServiceManager()->get('ViewHelperManager')->get('navigation')
        ->getPluginManager()->setInvokableClass('menu', 'Olcs\View\Helper\Navigation\Menu');
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
