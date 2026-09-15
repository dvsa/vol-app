<?php

use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Transfer\Query;
use Dvsa\Olcs\Transfer\Router\CommandConfig;
use Dvsa\Olcs\Transfer\Router\QueryConfig;
use Dvsa\Olcs\Transfer\Router\RouteConfig;

return [
    'companies-house-alert' => RouteConfig::getRouteConfig(
        'companies-house-alert',
        [
            'GET' => QueryConfig::getConfig(Query\CompaniesHouse\AlertList::class),
            'close' => RouteConfig::getRouteConfig(
                'close',
                [
                    'POST' => CommandConfig::getPostConfig(Command\CompaniesHouse\CloseAlerts::class),
                ]
            ),
        ]
    ),
];
