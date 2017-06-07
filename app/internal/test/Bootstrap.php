<?php
namespace OlcsTest;

use Zend\Mvc\Service\ServiceManagerConfig;
use Zend\ServiceManager\ServiceManager;
use Zend\Loader\AutoloaderFactory;
use RuntimeException;

date_default_timezone_set('Europe/London');
error_reporting(E_ALL | E_STRICT);
chdir(__DIR__);

/**
 * Test bootstrap, for setting up autoloading
 */
class Bootstrap
{
    protected static $serviceManager;

    public static function init()
    {
        // Only really using less than 300mb now....
        ini_set('memory_limit', '300M');

        $zf2ModulePaths = array(dirname(dirname(__DIR__)));
        if (($path = static::findParentPath('vendor'))) {
            $zf2ModulePaths[] = $path;
        }
        if (($path = static::findParentPath('module')) !== $zf2ModulePaths[0]) {
            $zf2ModulePaths[] = $path;
        }

        static::initAutoloader();

        // use ModuleManager to load this module and it's dependencies
        $config = include __DIR__.'/../config/application.config.php';

        $serviceManager = new ServiceManager(new ServiceManagerConfig());
        $serviceManager->setService('ApplicationConfig', $config);
        $serviceManager->get('ModuleManager')->loadModules();

        // If we want to a mock a service, we can.  But default services apply.
        $serviceManager->setAllowOverride(true);

        static::$serviceManager = $serviceManager;
    }

    public static function chroot()
    {
        $rootPath = dirname(static::findParentPath('module'));
        chdir($rootPath);
    }

    public static function getServiceManager()
    {
        return static::$serviceManager;
    }

    public static function getRealServiceManager()
    {
        return static::$serviceManager;
    }

    protected static function initAutoloader()
    {
        $vendorPath = static::findParentPath('vendor');

        if (file_exists($vendorPath.'/autoload.php')) {
            include $vendorPath . '/autoload.php';
        }

        if (! class_exists('Zend\Loader\AutoloaderFactory')) {
            throw new RuntimeException(
                'Unable to load ZF2. Run `php composer.phar install`'
            );
        }

        AutoloaderFactory::factory(
            [
                'Zend\Loader\StandardAutoloader' => [
                    'autoregister_zf' => true,
                    'namespaces' => [
                        __NAMESPACE__ => __DIR__ . '/' . __NAMESPACE__,
                        'OlcsTest\\' => __DIR__ . '/Olcs/src',
                        'AdminTest\\' => __DIR__ . '/Admin/src',
                        'CommonTest\\' => __DIR__ . '/../vendor/olcs/OlcsCommon/test/Common/src/Common',
                        'OlcsComponentTest\\' => __DIR__ . '/Component'
                    ],
                ],
            ]
        );
    }

    protected static function findParentPath($path)
    {
        $dir = __DIR__;
        $previousDir = '.';
        while (!is_dir($dir . '/' . $path)) {
            $dir = dirname($dir);
            if ($previousDir === $dir) {
                return false;
            }
            $previousDir = $dir;
        }
        return $dir . '/' . $path;
    }
}

Bootstrap::init();
Bootstrap::chroot();
