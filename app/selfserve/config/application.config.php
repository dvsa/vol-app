<?php

$config = array(
    'modules' => array(
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

if (file_exists(__DIR__ . '/../vendor/laminas/laminas-developer-tools/src/Module.php')) {
    $config['modules'][] = 'Laminas\DeveloperTools';

    if (file_exists(__DIR__ . '/../vendor/san/san-session-toolbar/src/Module.php')) {
        $config['modules'][] = 'SanSessionToolbar';
    }
}

return $config;
