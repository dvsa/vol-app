<?php

$config = array(
    'modules' => array(
        'Dvsa\Olcs\Utils',
        'Dvsa\Olcs\Transfer',
        'DoctrineModule',
        'DoctrineORMModule',
        'Olcs\Logging',
        'Common',
        'Olcs',
        'ZfcBase',
        'ZfcUser',
        'ZfcRbac',
    ),
    'module_listener_options' => array(
        'module_paths' => array(
            __DIR__ . '/../module',
            __DIR__ . '/../vendor',
            __DIR__ . '/../vendor/olcs/OlcsCommon'
        ),
        'config_glob_paths' => array(
            'config/autoload/{,*.}{global,local}.php'
        )
    )
);

if (file_exists(__DIR__ . '/../vendor/zendframework/zend-developer-tools/Module.php')) {
    array_unshift($config['modules'], 'ZendDeveloperTools');
}

return $config;
