<?php

namespace OlcsTest;

use Common\Service\Translator\TranslationLoader;
use Olcs\Logging\Log\Logger;
use Laminas\I18n\Translator\LoaderPluginManager;
use Laminas\Mvc\Service\ServiceManagerConfig;
use Laminas\ServiceManager\ServiceManager;
use Laminas\Di\Di;
use Mockery as m;

error_reporting(E_ALL & ~E_USER_DEPRECATED);
chdir(dirname(__DIR__));
//ini_set("display_errors", 1);
ini_set('intl.default_locale', 'en_GB');
date_default_timezone_set('Europe/London');

/**
 * Test bootstrap, for setting up autoloading
 */
class Bootstrap
{
    protected static $config = array();
    protected static $di;

    public static function init()
    {
        ini_set('memory_limit', '1500M');
        // Setup the autloader
        $loader = static::initAutoloader();
        $loader->addPsr4('OlcsTest\\', __DIR__ . '/Olcs/src');

        // Grab the application config
        $config = include dirname(__DIR__) . '/config/application.config.php';

        self::$config = $config;

        // call this once to load module config
        self::getRealServiceManager();

        // Setup Di
        $di = new Di();

        $di->instanceManager()->addTypePreference(
            'Laminas\ServiceManager\ServiceLocatorInterface',
            'Laminas\ServiceManager\ServiceManager'
        );
        $di->instanceManager()->addTypePreference(
            'Laminas\EventManager\EventManagerInterface',
            'Laminas\EventManager\EventManager'
        );
        $di->instanceManager()->addTypePreference(
            'Laminas\EventManager\SharedEventManagerInterface',
            'Laminas\EventManager\SharedEventManager'
        );

        self::$di = $di;

        self::setupLogger();
    }

    public static function setupLogger()
    {
        $logWriter = new \Laminas\Log\Writer\Mock();
        $logger = new \Laminas\Log\Logger();
        $logger->addWriter($logWriter);

        Logger::setLogger($logger);
    }

    /**
     * Changed this method to return a mock
     *
     * @return \Laminas\ServiceManager\ServiceManager
     */
    public static function getServiceManager()
    {
        $sm = m::mock('\Laminas\ServiceManager\ServiceManager')
            ->makePartial()
            ->setAllowOverride(true);

        // inject a real string helper
        $sm->setService('Helper\String', new \Common\Service\Helper\StringHelperService());

        return $sm;
    }

    /**
     * Added this method for backwards compatibility
     *
     * @return \Laminas\ServiceManager\ServiceManager
     */
    public static function getRealServiceManager()
    {
        $serviceManager = new ServiceManager(new ServiceManagerConfig());
        $serviceManager->setService('ApplicationConfig', self::$config);
        $serviceManager->get('ModuleManager')->loadModules();
        $serviceManager->setAllowOverride(true);

        $mockTranslationLoader = m::mock(TranslationLoader::class);
        $mockTranslationLoader->shouldReceive('load')->andReturn(['default' => ['en_GB' => []]]);
        $mockTranslationLoader->shouldReceive('loadReplacements')->andReturn([]);
        $serviceManager->setService(TranslationLoader::class, $mockTranslationLoader);

        $pluginManager = new LoaderPluginManager($serviceManager);
        $pluginManager->setService(TranslationLoader::class, $mockTranslationLoader);
        $serviceManager->setService('TranslatorPluginManager', $pluginManager);

        // Mess up the backend, so any real rest calls will fail
        $config = $serviceManager->get('Config');
        $config['service_api_mapping']['endpoints']['backend'] = 'http://some-fake-backend/';
        $serviceManager->setService('Config', $config);

        return $serviceManager;
    }

    protected static function initAutoloader()
    {
        require('init_autoloader.php');

        return $loader;
    }

    public static function getDi()
    {
        return self::$di;
    }
}

Bootstrap::init();
