<?php

use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Transfer\Router\CommandConfig;
use Dvsa\Olcs\Transfer\Router\RouteConfig;

return [
    'report' => RouteConfig::getRouteConfig(
        'report',
        [
            'upload' => RouteConfig::getRouteConfig(
                'upload',
                [
                    'POST' => CommandConfig::getPostConfig(Command\Report\Upload::class),
                ]
            ),
        ]
    )
];
