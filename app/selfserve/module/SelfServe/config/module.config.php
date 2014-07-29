<?php

list($allRoutes, $controllers, $journeys) = include(__DIR__ . '/journeys.config.php');

$invokeables = array_merge(
    $controllers, array(
    'SelfServe\Dashboard\Index' => 'SelfServe\Controller\Dashboard\IndexController',
    )
);

return array(
    'journeys' => $journeys,
    'router' => array(
        'routes' => $allRoutes
    ),
    'controllers' => array(
        'invokables' => $invokeables
    ),
    'local_forms_path' => __DIR__ . '/../src/SelfServe/Form/Forms/',
    'local_scripts_path' => __DIR__ . '/../assets/js/inline/',
    'tables' => array(
        'config' => array(
            __DIR__ . '/../src/SelfServe/Table/Tables/'
        )
    ),
    'service_manager' => array(
        'factories' => array()
    ),
    'controller_plugins' => array(
        'invokables' => array()
    ),
    'simple_date_format' => array(
        'default' => 'd-m-Y'
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            __DIR__ . '/../view'
        )
    ),
    'application-name' => 'selfserve',
    'asset_path' => '//olcs-frontend'
);
