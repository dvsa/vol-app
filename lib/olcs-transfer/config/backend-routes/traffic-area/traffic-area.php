<?php

use Dvsa\Olcs\Transfer\Query;
use Dvsa\Olcs\Transfer\Router\QueryConfig;
use Dvsa\Olcs\Transfer\Router\RouteConfig;

return [
    'traffic-area' => [
        'type' => 'Segment',
        'options' => [
            'route' => 'traffic-area[/]',
        ],
        'may_terminate' => false,
        'child_routes' => [
            'GET' => QueryConfig::getConfig(Query\TrafficArea\TrafficAreaList::class),
            'single' => RouteConfig::getSingleConfig(
                ['GET' => QueryConfig::getConfig(Query\TrafficArea\Get::class)]
            ),
        ]
    ],
    'traffic-area-internal' => [
        'type' => 'Segment',
        'options' => [
            'route' => 'traffic-area-internal[/]',
        ],
        'may_terminate' => false,
        'child_routes' => [
            'GET' => QueryConfig::getConfig(Query\TrafficArea\TrafficAreaInternalList::class),
        ],
    ],
];
