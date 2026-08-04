<?php

use Dvsa\Olcs\Transfer\Query;
use Dvsa\Olcs\Transfer\Router\QueryConfig;
use Dvsa\Olcs\Transfer\Router\RouteConfig;

return [
    'organisation-permits' => RouteConfig::getRouteConfig(
        'organisation-permits',
        [
            'single' => RouteConfig::getSingleConfig(
                [
                    'GET' => QueryConfig::getConfig(Query\Organisation\OrganisationAvailableLicences::class),

                ]
            ),
        ]
    ),
];
