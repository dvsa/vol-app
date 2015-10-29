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

    public function getConfig()
    {
        $config = array();
        $configFiles = array(
            include __DIR__ . '/config/module.config.php',
        );
        foreach ($configFiles as $file) {
            $config = \Zend\Stdlib\ArrayUtils::merge($config, $file);
        }
        return $config;
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

        if (!defined('DATETIME_FORMAT')) {
            define('DATETIME_FORMAT', 'd M Y H:i');
        }
        if (!defined('DATETIMESEC_FORMAT')) {
            define('DATETIMESEC_FORMAT', 'd M Y H:i:s');
        }
        if (!defined('DATE_FORMAT')) {
            define('DATE_FORMAT', 'd M Y');
        }
    }
}
