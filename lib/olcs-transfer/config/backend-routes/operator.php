<?php

use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Transfer\Query;
use Dvsa\Olcs\Transfer\Router\CommandConfig;
use Dvsa\Olcs\Transfer\Router\RouteConfig;
use Dvsa\Olcs\Transfer\Router\QueryConfig;

return [
    'operator' => [
        'type' => 'Segment',
        'options' => [
            'route' => 'operator[/]',
        ],
        'may_terminate' => false,
        'child_routes' => [
            'single' => RouteConfig::getSingleConfig(
                [
                    'GET' => QueryConfig::getConfig(Query\Operator\BusinessDetails::class),
                    'PUT' => CommandConfig::getPutConfig(Command\Operator\Update::class),
                ]
            ),
            'POST' => CommandConfig::getPostConfig(Command\Operator\Create::class)
        ]
    ]
];
