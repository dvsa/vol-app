<?php

namespace Olcs;

use Laminas\Mvc\ModuleRouteListener;
use Laminas\Mvc\MvcEvent;
use Laminas\Session\Config\SessionConfig;
use Laminas\Session\Container;
use Laminas\Session\SessionManager;
use Laminas\Validator\AbstractValidator;

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
            'Laminas\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/',
                ),
            ),
        );
    }

    /**
     * On Bootstrap (init)
     *
     * @param MvcEvent $e Event
     *
     * @return void
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

        $cookieListener = $sm->get('CookieListener');
        $cookieListener->attach($eventManager, 2);

        $this->initSession(
            [
                'remember_me_seconds' => 86400,
                'use_cookies' => true,
                'cookie_httponly' => true
            ]
        );
    }

    /**
     * Set up and configure Session Manager
     *
     * @param array $config Config
     *
     * @return void
     */
    public function initSession($config)
    {
        $sessionConfig = new SessionConfig();
        $sessionConfig->setOptions($config);
        $sessionManager = new SessionManager($sessionConfig);
        $sessionManager->start();
        Container::setDefaultManager($sessionManager);
    }
}
