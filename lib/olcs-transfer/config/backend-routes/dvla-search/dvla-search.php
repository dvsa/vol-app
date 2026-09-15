<?php

use Dvsa\Olcs\Transfer\Query\DvlaSearch\Vehicle;
use Dvsa\Olcs\Transfer\Router\QueryConfig;
use Dvsa\Olcs\Transfer\Router\RouteConfig;

return [
    'dvla-search' => [
        'type' => 'Segment',
        'options' => [
            'route' => 'dvla-search[/]',
        ],
        'may_terminate' => false,
        'child_routes' => [
            'vehicle' => RouteConfig::getRouteConfig(
                'vehicle[/]',
                [
                    'GET' => QueryConfig::getConfig(Vehicle::class)
                ]
            ),
        ]
    ],
];
