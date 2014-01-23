<?php
return array(
    'modules' => array(
        'Olcs',
        'OlcsCommon',
        'VosaPaymentToken',
        'DoctrineModule',
        'DoctrineORMModule',
    ),
    'module_listener_options' => array(
        'config_glob_paths'    => array(
            __DIR__ . '/src/config/autoload/{,*.}{global,local}.php',
        ),
        'module_paths' => array(
            __DIR__ . '/src/module',
            __DIR__ . '/src/vendor',
        ),
    ),
);
