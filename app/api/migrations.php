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

$migrationsConfig = $config['doctrine']['migrations']['orm_default'];

return [
    'table_storage' => [
        'table_name' => $migrationsConfig['table'],
        'version_column_name' => $migrationsConfig['column'],
    ],
    'migrations_paths' => [
        $migrationsConfig['namespace'] => __DIR__ . '/' . $migrationsConfig['directory'],
    ],
    'all_or_nothing' => $migrationsConfig['all_or_nothing'],
    'check_database_platform' => $migrationsConfig['check_database_platform'],
];
