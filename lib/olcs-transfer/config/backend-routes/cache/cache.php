<?php

use Dvsa\Olcs\Transfer\Query;
use Dvsa\Olcs\Transfer\Router\QueryConfig;

return [
    'cache' => [
        'type' => 'Segment',
        'options' => [
            'route' => 'cache[/]',
        ],
        'may_terminate' => false,
        'child_routes' => [
            'GET' => QueryConfig::getConfig(Query\Cache\ById::class),
        ],
    ],
];
