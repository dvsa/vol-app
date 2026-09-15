<?php

use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Transfer\Router\CommandConfig;
use Dvsa\Olcs\Transfer\Router\RouteConfig;

return [
    'private-hire-licence' => RouteConfig::getRouteConfig(
        'private-hire-licence',
        [
            'DELETE' => CommandConfig::getDeleteConfig(Command\PrivateHireLicence\DeleteList::class),
            'POST' => CommandConfig::getPostConfig(Command\PrivateHireLicence\Create::class),
            'single' => RouteConfig::getSingleConfig(
                [
                    'PUT' => CommandConfig::getPutConfig(Command\PrivateHireLicence\Update::class),
                ]
            ),
        ]
    )
];
