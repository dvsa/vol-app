<?php

use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Transfer\Query;
use Dvsa\Olcs\Transfer\Router\CommandConfig;
use Dvsa\Olcs\Transfer\Router\QueryConfig;
use Dvsa\Olcs\Transfer\Router\RouteConfig;

return [
    'goods-vehicles' => RouteConfig::getRouteConfig(
        'goods-vehicles',
        [
            'disc' => RouteConfig::getRouteConfig(
                'disc',
                [
                    'reprint' => RouteConfig::getRouteConfig(
                        'reprint',
                        [
                            'POST' => CommandConfig::getPostConfig(Command\Vehicle\ReprintDisc::class),
                        ]
                    )
                ]
            )
        ]
    )
];
