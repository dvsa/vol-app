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

if (file_exists(__DIR__ . '/../vendor/laminas/laminas-developer-tools/src/Module.php')) {
    $config['modules'][] = 'Laminas\DeveloperTools';

    if (file_exists(__DIR__ . '/../vendor/san/san-session-toolbar/src/Module.php')) {
        /** @todo once we're on Laminas 3, this line will be uncommented by VOL-3749  */
        //$config['modules'][] = 'SanSessionToolbar';
    }
}

return $config;
