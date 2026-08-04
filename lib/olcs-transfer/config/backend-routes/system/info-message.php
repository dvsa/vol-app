<?php

use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Transfer\Query;
use Dvsa\Olcs\Transfer\Router\CommandConfig;
use Dvsa\Olcs\Transfer\Router\QueryConfig;
use Dvsa\Olcs\Transfer\Router\RouteConfig;

return [
    'system-info-message' => [
        'type' => 'Segment',
        'options' => [
            'route' => 'system-info-message[/]',
        ],
        'may_terminate' => false,
        'child_routes' => [
            'single' => RouteConfig::getSingleConfig(
                [
                    'GET' => QueryConfig::getConfig(Query\System\InfoMessage\Get::class),
                    'PUT' => CommandConfig::getPutConfig(Command\System\InfoMessage\Update::class),
                    'DELETE' => CommandConfig::getDeleteConfig(Command\System\InfoMessage\Delete::class),
                ]
            ),
            'GET' => QueryConfig::getConfig(Query\System\InfoMessage\GetList::class),
            'POST' => CommandConfig::getPostConfig(Command\System\InfoMessage\Create::class),
            'active' => RouteConfig::getRouteConfig(
                'active',
                [
                    'GET' => QueryConfig::getConfig(Query\System\InfoMessage\GetListActive::class),
                ]
            ),
        ],
    ],
];
