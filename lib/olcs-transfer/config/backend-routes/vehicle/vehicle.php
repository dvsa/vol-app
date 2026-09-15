<?php

use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Transfer\Query;
use Dvsa\Olcs\Transfer\Router\CommandConfig;
use Dvsa\Olcs\Transfer\Router\QueryConfig;
use Dvsa\Olcs\Transfer\Router\RouteConfig;

return [
    'vehicle' => RouteConfig::getRouteConfig(
        'vehicle',
        [
            'section26' => RouteConfig::getRouteConfig(
                'section26',
                [
                    'PUT' => CommandConfig::getPutConfig(Command\Vehicle\UpdateSection26::class),
                ]
            )
        ]
    )
];
