<?php

return array(
    'application-name' => 'internal',
    'router' => array(
        'routes' => array(
            'dashboard' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => '/',
                    'defaults' => array(
                        'controller' => 'IndexController',
                        'action' => 'index',
                    )
                )
            ),
        ),
    ),
    'tables' => array(
        'config' => array(
            __DIR__ . '/../src/Table/Tables/'
        )
    ),
    'controllers' => array(
        'invokables' => array(
            'DefaultController' => 'Olcs\Olcs\Placeholder\Controller\DefaultController',

        )
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            'admin/view' => dirname(__DIR__) . '/view',
        )
    ),
    'local_forms_path' => __DIR__ . '/../src/Form/Forms/',
    //-------- Start navigation -----------------
    'navigation' => array(
        'default' => array(
            include __DIR__ . '/navigation.config.php'
        )
    ),
    //-------- End navigation -----------------
    'local_scripts_path' => __DIR__ . '/../assets/js/inline/',
);