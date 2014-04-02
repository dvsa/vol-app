<?php

/**
 * OLCS doc API Configuration
 *
 * @package OlcsDoc
 * @author  Mike Cooper
 */
$routes = [];

foreach (array_map(function ($file) {
    return include $file;
}, glob(__DIR__ . '/routes/*.routes.php')) as $rs) {
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
            'SelfServe\LicenceType\Index' => 'SelfServe\Controller\LicenceType\IndexController',
            'SelfServe\Business\Index' => 'SelfServe\Controller\Business\IndexController',
            'SelfServe\Finance\Index' => 'SelfServe\Controller\Finance\IndexController'
        ),
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
