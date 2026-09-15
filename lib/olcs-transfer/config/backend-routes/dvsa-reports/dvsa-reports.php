<?php

use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Transfer\Router\CommandConfig;
use Dvsa\Olcs\Transfer\Router\RouteConfig;

return [
    'dvsa-reports' => RouteConfig::getRouteConfig(
        'dvsa-reports',
        [
            'get-redirect' => RouteConfig::getRouteConfig(
                'get-redirect',
                [
                    'POST' => CommandConfig::getPostConfig(Command\DvsaReports\GetRedirect::class),
                ]
            ),
        ]
    )
];
