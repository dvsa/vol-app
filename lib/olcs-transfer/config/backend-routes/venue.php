<?php

use Dvsa\Olcs\Transfer\Query;
use Dvsa\Olcs\Transfer\Router\QueryConfig;

return [
    'venue-list' => [
        'type' => 'Segment',
        'options' => [
            'route' => 'venue-list[/]',
        ],
        'may_terminate' => false,
        'child_routes' => [
            'GET' => QueryConfig::getConfig(Query\Venue\VenueList::class),
        ]
    ]
];
