<?php

use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Transfer\Query;
use Dvsa\Olcs\Transfer\Router\CommandConfig;
use Dvsa\Olcs\Transfer\Router\QueryConfig;
use Dvsa\Olcs\Transfer\Router\RouteConfig;

return [
    'si-penalty-applied' => [
        'type' => 'Segment',
        'options' => [
            'route' => 'si-penalty-applied[/]',
        ],
        'may_terminate' => false,
        'child_routes' => [
            'single' => RouteConfig::getSingleConfig(
                [
                    'GET' => QueryConfig::getConfig(Query\Cases\Si\Applied\Penalty::class),
                    'PUT' => CommandConfig::getPutConfig(Command\Cases\Si\Applied\Update::class),
                    'DELETE' => CommandConfig::getDeleteConfig(Command\Cases\Si\Applied\Delete::class)
                ]
            ),
            'POST' => CommandConfig::getPostConfig(Command\Cases\Si\Applied\Create::class),
        ]
    ]
];
