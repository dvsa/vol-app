<?php

use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Transfer\Query;
use Dvsa\Olcs\Transfer\Router\CommandConfig;
use Dvsa\Olcs\Transfer\Router\QueryConfig;
use Dvsa\Olcs\Transfer\Router\RouteConfig;

return [
    'licence-operating-centre' => RouteConfig::getRouteConfig(
        'licence-operating-centre',
        [
            'single' => RouteConfig::getSingleConfig(
                [
                    'GET' => QueryConfig::getConfig(
                        Query\LicenceOperatingCentre\LicenceOperatingCentre::class
                    ),
                    'PUT' => CommandConfig::getPutConfig(
                        Command\LicenceOperatingCentre\Update::class
                    ),
                ]
            ),
        ]
    )
];
