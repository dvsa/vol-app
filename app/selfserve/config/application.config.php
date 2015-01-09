<?php

return array(
    'modules' => array(
        //'Olcs\Logging',
        'Dvsa\Jackrabbit',
        'Common',
        'DoctrineModule',
        'DoctrineORMModule',
        'Olcs',
        'Dvsa\Jackrabbit'
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
