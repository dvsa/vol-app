<?php

use Dvsa\Olcs\Transfer\Query;
use Dvsa\Olcs\Transfer\Router\QueryConfig;

return [
    'language' => [
        'type' => 'Segment',
        'options' => [
            'route' => 'language[/]',
        ],
        'may_terminate' => false,
        'child_routes' => [
            'GET' => QueryConfig::getConfig(Query\Language\GetList::class),
        ]
    ],
];
