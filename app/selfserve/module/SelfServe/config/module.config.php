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
    'local_scripts_path' => [__DIR__ . '/../assets/js/inline/'],
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
        'display_not_found_reason' => true,
        'display_exceptions' => true,
        'doctype' => 'HTML5',
        'not_found_template' => 'error/404',
        'exception_template' => 'error/index',
        'template_map' => array(
            'layout/layout'           => __DIR__ . '/../view/self-serve/layout/base.phtml',
            'error/404'               => __DIR__ . '/../view/self-serve/error/404.phtml',
            'error/index'             => __DIR__ . '/../view/self-serve/error/index.phtml'
        ),
        'template_path_stack' => array(
            __DIR__ . '/../../../vendor/olcs/OlcsCommon/Common/view',
            __DIR__ . '/../view',
            __DIR__ . '/../view/self-serve'
        )
    ),
    'asset_path' => '//dvsa-static.olcsdv-ap01.olcs.npm'
);
