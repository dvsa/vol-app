<?php

use Laminas\Mvc\Application;
use Laminas\Stdlib\ArrayUtils;

// Bootstrap the application to get the full config
$appConfig = require __DIR__ . '/config/application.config.php';
if (file_exists(__DIR__ . '/config/development.config.php')) {
    $appConfig = ArrayUtils::merge($appConfig, require __DIR__ . '/config/development.config.php');
}

// Add CLI module
$appConfig = ArrayUtils::merge($appConfig, [
    'modules' => [
        'Dvsa\Olcs\Cli'
    ],
    'module_listener_options' => [
        'module_map_cache_key' => 'cli.module.cache',
        'config_cache_key' => 'cli.config.cache',
    ],
]);

// Initialize application and get config
$container = Application::init($appConfig)->getServiceManager();
$config = $container->get('config');

$doctrineConfig = $config['doctrine']['connection']['orm_default'];

return [
    'driver' => 'pdo_mysql',
    'host' => $doctrineConfig['params']['host'],
    'dbname' => $doctrineConfig['params']['dbname'],
    'user' => $doctrineConfig['params']['user'],
    'password' => $doctrineConfig['params']['password'],
    'charset' => 'utf8',
    'driverOptions' => [
        \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'"
    ]
];
