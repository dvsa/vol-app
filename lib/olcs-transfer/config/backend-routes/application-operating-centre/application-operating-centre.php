<?php

use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Transfer\Query;
use Dvsa\Olcs\Transfer\Router\CommandConfig;
use Dvsa\Olcs\Transfer\Router\QueryConfig;
use Dvsa\Olcs\Transfer\Router\RouteConfig;

return [
    'application-operating-centre' => RouteConfig::getRouteConfig(
        'application-operating-centre',
        [
            'single' => RouteConfig::getSingleConfig(
                [
                    'GET' => QueryConfig::getConfig(
                        Query\ApplicationOperatingCentre\ApplicationOperatingCentre::class
                    ),
                    'PUT' => CommandConfig::getPutConfig(
                        Command\ApplicationOperatingCentre\Update::class
                    ),
                ]
            ),
        ]
    )
];
