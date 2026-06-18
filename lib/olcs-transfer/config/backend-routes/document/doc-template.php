<?php

use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Transfer\Query;
use Dvsa\Olcs\Transfer\Router\CommandConfig;
use Dvsa\Olcs\Transfer\Router\QueryConfig;
use Dvsa\Olcs\Transfer\Router\RouteConfig;

return [
    'doc-template' => [
        'type' => 'Segment',
        'options' => [
            'route' => 'doc-template[/]',
        ],
        'may_terminate' => false,
        'child_routes' => [
            'single' => RouteConfig::getSingleConfig(
                [
                    'GET' => QueryConfig::getConfig(Query\DocTemplate\ById::class),
                    'POST' => CommandConfig::getPostConfig(Command\DocTemplate\Update::class),
                    'DELETE' => CommandConfig::getDeleteConfig(Command\DocTemplate\Delete::class),
                ]
            ),
            'GET' => QueryConfig::getConfig(Query\DocTemplate\GetList::class),
            'POST' => CommandConfig::getPostConfig(Command\DocTemplate\Create::class),
            'admin-list' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'admin-list[/]',
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'GET' => QueryConfig::getConfig(Query\DocTemplate\FullList::class),
                ]
            ],
        ]
    ],
];
