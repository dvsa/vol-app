<?php

use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Transfer\Query;
use Dvsa\Olcs\Transfer\Router\CommandConfig;
use Dvsa\Olcs\Transfer\Router\QueryConfig;
use Dvsa\Olcs\Transfer\Router\RouteConfig;

return [
    'variation-operating-centre' => RouteConfig::getRouteConfig(
        'variation-operating-centre',
        [
            'single' => RouteConfig::getSingleConfig(
                [
                    'GET' => QueryConfig::getConfig(
                        Query\VariationOperatingCentre\VariationOperatingCentre::class
                    )
                ]
            ),
        ]
    )
];
