<?php

/**
 * OLCS doc API Configuration
 *
 * @package OlcsDoc
 * @author  Mike Cooper
 */
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

/**
 * Module routes configuration
 */
return array(
    'router' => array(
        'routes' => array(
            'selfserve' => array(
                'type' => 'literal',
                'options' => array(
                    'route' => '/selfserve',
                ),
                'child_routes' => $routes
            ),
        ),
    ),
    'controllers' => array(
        'invokables' => array(
            'SelfServe\Dashboard\Index' => 'SelfServe\Controller\Dashboard\IndexController',
            'SelfServe\LicenceType\Index' => 'SelfServe\Controller\LicenceType\IndexController',
            'SelfServe\BusinessType\Index' => 'SelfServe\Controller\BusinessType\IndexController',
            'SelfServe\Finance\Index' => 'SelfServe\Controller\Finance\IndexController',
            'SelfServe\VehiclesSafety\Index' => 'SelfServe\Controller\VehiclesSafety\IndexController',
            'SelfServe\VehiclesSafety\Vehicle' => 'SelfServe\Controller\VehiclesSafety\VehicleController'
        ),
    ),
    'tables' => array(
        'config' => array(
            __DIR__ . '/../src/SelfServe/Table/Tables/'
        )
    ),
    'service_manager' => array(
        'factories' => array(
        ),
    ),
    'controller_plugins' => array(
        'invokables' => array(
        )
    ),
    'simple_date_format' => array(
        'default' => 'd-m-Y'
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
        'strategies' => array(
            'ViewJsonStrategy',
        ),
    ),
);