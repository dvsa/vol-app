<?php

list($allRoutes, $controllers, $journeys) = include(
    __DIR__ . '/../../../vendor/olcs/OlcsCommon/Common/config/journeys.config.php'
);

$routes = [];

$routeArray = array_map(
    function ($file) {
        return include $file;
    },
    glob(__DIR__ . '/routes/*.routes.php')
);

foreach ($routeArray as $rs) {
    $routes += $rs;
}

$routes = array_merge($allRoutes, $routes);

return array(
    'router' => array(
        'routes' => $routes
    ),
    'controllers' => array(
        'invokables' => array(
            'SelfServe\Dashboard\Index' => 'SelfServe\Controller\Dashboard\IndexController',
        )
    ),
    'local_forms_path' => __DIR__ . '/../src/SelfServe/Form/Forms/',
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
    'asset_path' => '//dvsa-static.olcsdv-ap01.olcs.npm',
    'application_journey' => array(
        'templates' => array(
            'not-found' => 'self-serve/journey/not-found',
            'navigation' => 'self-serve/journey/application/navigation',
            'main' => 'self-serve/journey/application/main',
            'layout' => 'self-serve/journey/application/layout'
        )
    )
);
