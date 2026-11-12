<?php

use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Transfer\Router\CommandConfig;
use Dvsa\Olcs\Transfer\Router\RouteConfig;

return [
    'notify' => RouteConfig::getRouteConfig(
        'notify',
        [
            'delivery-status' => RouteConfig::getRouteConfig(
                'delivery-status',
                [
                    'POST' => CommandConfig::getPostConfig(Command\Notify\UpdateDeliveryStatus::class),
                ]
            ),
        ]
    )
];
