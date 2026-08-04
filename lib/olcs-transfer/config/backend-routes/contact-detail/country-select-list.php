<?php

use Dvsa\Olcs\Transfer\Query;
use Dvsa\Olcs\Transfer\Router\QueryConfig;
use Dvsa\Olcs\Transfer\Router\RouteConfig;

return [
    'country-select-list' => RouteConfig::getRouteConfig(
        'country-select-list',
        [
            'GET' => QueryConfig::getConfig(Query\ContactDetail\CountrySelectList::class),
        ]
    )
];
