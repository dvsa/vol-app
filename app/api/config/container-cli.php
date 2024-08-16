<?php

use Laminas\Mvc\Application;
use Laminas\Stdlib\ArrayUtils;

// Retrieve configuration
$appConfig = require __DIR__ . '/application.config.php';
if (file_exists(__DIR__ . '/development.config.php')) {
    $appConfig = ArrayUtils::merge($appConfig, require __DIR__ . '/development.config.php');
}

$appConfig = ArrayUtils::merge($appConfig, [
    'modules' => [
        'Dvsa\Olcs\Cli'
    ],
    'module_listener_options' => [
        'module_map_cache_key' => 'cli.module.cache',
        'config_cache_key' => 'cli.config.cache',
    ],
]);

return Application::init($appConfig)
    ->getServiceManager();
