<?php

use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Transfer\Query;
use Dvsa\Olcs\Transfer\Router\CommandConfig;
use Dvsa\Olcs\Transfer\Router\QueryConfig;
use Dvsa\Olcs\Transfer\Router\RouteConfig;

return [
    'disqualification' => RouteConfig::getRouteConfig(
        'disqualification',
        [
            'POST' => CommandConfig::getPostConfig(Command\Disqualification\Create::class),
            'single' => RouteConfig::getSingleConfig(
                [
                    'PUT' => CommandConfig::getPutConfig(Command\Disqualification\Update::class),
                    'DELETE' => CommandConfig::getDeleteConfig(Command\Disqualification\Delete::class)
                ]
            ),
        ]
    )
];
