<?php

use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Transfer\Query;
use Dvsa\Olcs\Transfer\Router\CommandConfig;
use Dvsa\Olcs\Transfer\Router\QueryConfig;
use Dvsa\Olcs\Transfer\Router\RouteConfig;

return [
    'companies-house' => RouteConfig::getRouteConfig(
        'companies-house',
        [
            'insolvency-practitioner' => RouteConfig::getRouteConfig(
                'insolvency-practitioner',
                [
                    'GET' => QueryConfig::getConfig(Query\CompaniesHouse\InsolvencyPractitioner::class)
                ]
            ),
            'number' => RouteConfig::getRouteConfig(
                'number',
                [
                    'GET' => QueryConfig::getConfig(Query\CompaniesHouse\ByNumber::class)
                ]
            )
        ]
    ),
];
