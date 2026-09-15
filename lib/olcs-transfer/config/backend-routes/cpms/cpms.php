<?php

use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Transfer\Query;
use Dvsa\Olcs\Transfer\Router\CommandConfig;
use Dvsa\Olcs\Transfer\Router\QueryConfig;
use Dvsa\Olcs\Transfer\Router\RouteConfig;

return [
    'cpms' => RouteConfig::getRouteConfig(
        'cpms',
        [
            'report' => RouteConfig::getRouteConfig(
                'report',
                [
                    'GET' => QueryConfig::getConfig(Query\Cpms\ReportList::class),
                    'POST' => CommandConfig::getPostConfig(Command\Cpms\RequestReport::class),
                    'named-single' => RouteConfig::getNamedSingleConfig(
                        'reference',
                        [
                            'GET' => QueryConfig::getConfig(Query\Cpms\ReportStatus::class),
                            'PUT' => CommandConfig::getPutConfig(Command\Cpms\DownloadReport::class),
                        ]
                    ),
                ]
            ),
        ]
    )
];
