<?php

$config = array(
    'modules' => array(
        'Dvsa\Olcs\Auth',
        'Dvsa\Olcs\Utils',
        'Dvsa\Olcs\Transfer',
        // Required for annotation parsing
        'DoctrineModule',
        'Olcs\Logging',
        'Common',
        'Application',
        'Olcs',
        'ZfcRbac',
        'Permits',
    ),
    'module_listener_options' => array(
        'module_paths' => array(
            __DIR__ . '/../module',
            __DIR__ . '/../vendor',
            __DIR__ . '/../vendor/olcs/olcs-common'
        ),
        'config_glob_paths' => array(
            'config/autoload/{,*.}{global,local}.php'
        )
    )
);

if (file_exists(__DIR__ . '/../vendor/laminas/laminas-developer-tools/Module.php')) {
    array_unshift($config['modules'], 'Laminas\DeveloperTools');
}

return $config;
