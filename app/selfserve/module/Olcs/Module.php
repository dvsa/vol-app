<?php

/**
 * Module.php
 *
 * @author Someone <someone@somewhere.com>
 */

namespace Olcs;

use Dvsa\Olcs\Utils\Auth\AuthHelper;
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
        $config = include(__DIR__ . '/config/module.config.php');

        if (AuthHelper::isOpenAm() === false) {
            $config['service_manager']['aliases']['Zend\Authentication\AuthenticationService']
                = 'zfcuser_auth_service';

            $routeGuards = [
                'zfcuser/login' => ['*'],
                'login' => ['selfserve-user'],
                'zfcuser/logout' => ['*'],
                'logout' => ['*']
            ];

            $config['zfc_rbac']['guards']['ZfcRbac\Guard\RoutePermissionsGuard'] = array_merge(
                $routeGuards,
                $config['zfc_rbac']['guards']['ZfcRbac\Guard\RoutePermissionsGuard']
            );
        }

        return $config;
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
