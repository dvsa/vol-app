<?php

use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Transfer\Query;
use Dvsa\Olcs\Transfer\Router\CommandConfig;
use Dvsa\Olcs\Transfer\Router\QueryConfig;
use Dvsa\Olcs\Transfer\Router\RouteConfig;

return [
    'replacement' => [
        'type' => 'Segment',
        'options' => [
            'route' => 'replacement[/]',
        ],
        'may_terminate' => false,
        'child_routes' => [
            'single' => RouteConfig::getSingleConfig(
                [
                    'GET' => QueryConfig::getConfig(Query\Replacement\ById::class),
                    'PUT' => CommandConfig::getPutConfig(Command\Replacement\Update::class),
                ]
            ),
            'GET' => QueryConfig::getConfig(Query\Replacement\GetList::class),
            'POST' => CommandConfig::getPostConfig(Command\Replacement\Create::class),
        ]
    ],
];
