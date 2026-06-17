<?php

use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Transfer\Query;
use Dvsa\Olcs\Transfer\Router\CommandConfig;
use Dvsa\Olcs\Transfer\Router\QueryConfig;
use Dvsa\Olcs\Transfer\Router\RouteConfig;

return [
    'country' => RouteConfig::getRouteConfig(
        'country',
        [
            'GET' => QueryConfig::getConfig(Query\ContactDetail\CountryList::class),
        ]
    )
];
