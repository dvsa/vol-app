<?php

/**
 * Module.php
 *
 * @author Someone <someone@somewhere.com>
 */

namespace Olcs;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;

/**
 * Module.php
 *
 * @author Someone <someone@somewhere.com>
 */
class Module
{
    /**
     * Get the module config
     *
     * @return array
     */
    public function getConfig()
    {
        return include(__DIR__ . '/config/module.config.php');
    }

    /**
     * Get the autoloader config
     *
     * @return array
     */
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

    /**
     * @param MvcEvent $e
     */
    public function onBootstrap(MvcEvent $e)
    {
        $application = $e->getApplication();
        $sm = $application->getServiceManager();

        $eventManager = $application->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);

        $cookieBannerListener = $sm->get('CookieBannerListener');
        $cookieBannerListener->attach($eventManager, 1);
    }
}
