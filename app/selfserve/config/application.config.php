<?php

$config = [
    'modules' => [
        'Dvsa\LaminasConfigCloudParameters',
        'Laminas\Cache\Module',
        'Laminas\Cache\Storage\Adapter\Redis',
        'Laminas\Log',
        'Olcs\Logging',
        'Laminas\I18n',
        'Laminas\Mvc\Plugin\FlashMessenger',
        'Laminas\Filter',
        'Laminas\Validator',
        'Laminas\Navigation',
        'Laminas\Form',
        'Dvsa\Olcs\Auth',
        'Laminas\Router',
        'Dvsa\Olcs\Utils',
        'Dvsa\Olcs\Transfer',
        // Required for annotation parsing
        'DoctrineModule',
        'Common',
        'Application',
        'Olcs',
        'LmcRbacMvc',
        'Permits',
    ],
    // These are various options for the listeners attached to the ModuleManager
    'module_listener_options' => [
        // This should be an array of paths in which modules reside.
        // If a string key is provided, the listener will consider that a module
        // namespace, the value of that key the specific path to that module's
        // Module class.
        'module_paths' => [
            __DIR__ . '/../module',
            __DIR__ . '/../vendor'
        ],

        // An array of paths from which to glob configuration files after
        // modules are loaded. These effectively override configuration
        // provided by modules themselves. Paths may use GLOB_BRACE notation.
        'config_glob_paths' => [
            realpath(__DIR__) . '/autoload/{{,*.}global,{,*.}local}.php',
        ],

        // Whether or not to enable a configuration cache.
        // If enabled, the merged configuration will be cached and used in
        // subsequent requests.
        'config_cache_enabled' => true,

        // The key used to create the configuration cache file name.
        'config_cache_key' => 'application.config.cache',

        // Whether or not to enable a module class map cache.
        // If enabled, creates a module class map cache which will be used
        // by in future requests, to reduce the autoloading process.
        'module_map_cache_enabled' => true,

        // The key used to create the class map cache file name.
        //'module_map_cache_key' => $stringKey,

        // The path in which to cache merged configuration.
        'cache_dir' => 'data/cache/',

        // Whether or not to enable modules dependency checking.
        // Enabled by default, prevents usage of modules that depend on other modules
        // that weren't loaded.
        // 'check_dependencies' => true,
    ],
];

if (file_exists(__DIR__ . '/../vendor/laminas/laminas-developer-tools/src/Module.php')) {
    $config['modules'][] = 'Laminas\DeveloperTools';

    if (file_exists(__DIR__ . '/../vendor/san/san-session-toolbar/src/Module.php')) {
        $config['modules'][] = 'SanSessionToolbar';
    }
}

return $config;
