<?php

namespace SelfServe\Test;

use Zend\Mvc\Service\ServiceManagerConfig;
use Zend\ServiceManager\ServiceManager;
use Zend\Di\Di;

error_reporting(E_ALL | E_STRICT);
chdir(dirname(__DIR__));

/**
 * Test bootstrap, for setting up autoloading
 */
class Bootstrap
{

    protected static $serviceManager;
    protected static $di;

    public static function init()
    {
        // Setup the autloader
        $loader = static::initAutoloader();
        $loader->addPsr4('SelfServe\Test\\', __dir__ . '/SelfServe');

        // Grab the application config
        $config = include dirname(__DIR__) . '/config/application.config.php';

        $serviceManager = new ServiceManager(new ServiceManagerConfig());
        $serviceManager->setService('ApplicationConfig', $config);
        $serviceManager->get('ModuleManager')->loadModules();
        static::$serviceManager = $serviceManager;

        // Setup Di
        $di = new Di();

        $di->instanceManager()->addTypePreference('Zend\ServiceManager\ServiceLocatorInterface', 'Zend\ServiceManager\ServiceManager');
        $di->instanceManager()->addTypePreference('Zend\EventManager\EventManagerInterface', 'Zend\EventManager\EventManager');
        $di->instanceManager()->addTypePreference('Zend\EventManager\SharedEventManagerInterface', 'Zend\EventManager\SharedEventManager');

        self::$di = $di;
    }

    public static function getServiceManager()
    {
        return static::$serviceManager;
    }

    protected static function initAutoloader()
    {
        return require('vendor/autoload.php');
    }

    static public function getDi()
    {
        return self::$di;
    }
}

Bootstrap::init();
