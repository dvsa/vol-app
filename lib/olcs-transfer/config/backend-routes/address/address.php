<?php

use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Transfer\Query;
use Dvsa\Olcs\Transfer\Router\CommandConfig;
use Dvsa\Olcs\Transfer\Router\QueryConfig;
use Dvsa\Olcs\Transfer\Router\RouteConfig;

return [
    'address' => RouteConfig::getRouteConfig(
        'address',
        [
            'details' => RouteConfig::getRouteConfig(
                'details',
                [
                    'GET' => QueryConfig::getConfig(Query\Address\GetAddress::class),
                ]
            ),
            'list' => RouteConfig::getRouteConfig(
                'list',
                [
                    'GET' => QueryConfig::getConfig(Query\Address\GetList::class),
                ]
            ),
        ]
    )

];
