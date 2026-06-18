<?php

use Dvsa\Olcs\Transfer\Query;
use Dvsa\Olcs\Transfer\Router\QueryConfig;
use Dvsa\Olcs\Transfer\Router\RouteConfig;

return [
    'data-service' => [
        'type' => \Laminas\Router\Http\Segment::class,
        'options' => [
            'route' => 'data-service/',
        ],
        'may_terminate' => false,
        'child_routes' => [
            'application' => RouteConfig::getRouteConfig(
                'application',
                [
                    'status' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => 'status[/]',
                        ],
                        'may_terminate' => false,
                        'child_routes' => [
                            'GET' => QueryConfig::getConfig(Query\DataService\ApplicationStatus::class),
                        ],
                    ],
                ]
            ),
        ],
    ],
];
