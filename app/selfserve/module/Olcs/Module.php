<?php

namespace Olcs;

use Zend\Http\PhpEnvironment\Response;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\Session\Config\SessionConfig;
use Zend\Session\Container;
use Zend\Session\SessionManager;

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

        $this->initSession(
            [
                'remember_me_seconds' => 86400,
                'use_cookies' => true,
                'cookie_httponly' => true
            ]
        );

        $this->onFatalError();
    }

    /**
     * Catch fatal error
     *
     * @return void
     */
    public function onFatalError()
    {
        // Handle fatal errors //
        register_shutdown_function(
            function () {
                // get error
                $error = error_get_last();
                // check and allow only errors
                if (null === $error) {
                    return null;
                }

                // clean any previous output from buffer
                while (ob_get_level() > 0) {
                    ob_end_clean();
                }

                /** @var Response $response */
                $response = new Response();
                $response->getHeaders()->addHeaderLine('Location', '/error');
                $response->setStatusCode(Response::STATUS_CODE_302);
                $response->sendHeaders();

                return $response;
            }
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
