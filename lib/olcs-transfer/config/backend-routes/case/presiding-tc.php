<?php

use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Transfer\Query;
use Dvsa\Olcs\Transfer\Router\CommandConfig;
use Dvsa\Olcs\Transfer\Router\QueryConfig;
use Dvsa\Olcs\Transfer\Router\RouteConfig;

return [
    'presiding-tc' => [
        'type' => 'Segment',
        'options' => [
            'route' => 'presiding-tc[/]',
        ],
        'may_terminate' => false,
        'child_routes' => [
            'user-list' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'user-list[/]',
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'GET' => QueryConfig::getConfig(Query\Cases\PresidingTc\UserList::class),
                ]
            ],
            'GET' => QueryConfig::getConfig(Query\Cases\PresidingTc\GetList::class),
            'POST' => CommandConfig::getPostConfig(Command\Cases\PresidingTc\Create::class),
            'single' => RouteConfig::getSingleConfig(
                [
                    'GET' => QueryConfig::getConfig(Query\Cases\PresidingTc\ById::class),
                    'PUT' => CommandConfig::getPutConfig(Command\Cases\PresidingTc\Update::class),
                    'DELETE' => CommandConfig::getDeleteConfig(Command\Cases\PresidingTc\Delete::class),
                ]
            ),

        ]
    ]
];
