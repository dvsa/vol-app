<?php

use Dvsa\Olcs\Transfer\Query;
use Dvsa\Olcs\Transfer\Router\QueryConfig;

return [
    'preview-record' => [
        'type' => 'Segment',
        'options' => [
            'route' => 'preview-record[/]',
        ],
        'may_terminate' => false,
        'child_routes' => [
            'lookup' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'lookup',
                    'defaults' => [
                        'controller' => 'Api\Generic'
                    ]
                ],
                'child_routes' => [
                    'GET' => QueryConfig::getConfig(Query\Letter\PreviewRecord\Lookup::class),
                ]
            ],
        ]
    ],
];
