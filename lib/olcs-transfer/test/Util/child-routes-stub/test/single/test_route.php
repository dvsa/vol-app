<?php

use Dvsa\Olcs\Transfer\Router\RouteConfig;

return [
    'test_route' => RouteConfig::getRouteConfig(
        'test_route',
        [
            'POST' => '',
            'PUT' => '',
        ]
    ),
];
