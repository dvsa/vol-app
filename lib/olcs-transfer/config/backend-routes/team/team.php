<?php

use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Transfer\Query;
use Dvsa\Olcs\Transfer\Router\CommandConfig;
use Dvsa\Olcs\Transfer\Router\QueryConfig;
use Dvsa\Olcs\Transfer\Router\RouteConfig;

return [
    'team' => [
        'type' => 'Segment',
        'options' => [
            'route' => 'team[/]',
        ],
        'may_terminate' => false,
        'child_routes' => [
            'single' => RouteConfig::getSingleConfig(
                [
                    'GET' => QueryConfig::getConfig(Query\Team\Team::class),
                    'PUT' => CommandConfig::getPutConfig(Command\Team\UpdateTeam::class),
                    'DELETE' => CommandConfig::getDeleteConfig(Command\Team\DeleteTeam::class),
                ]
            ),
            'list-data' => RouteConfig::getRouteConfig(
                'list-data',
                [
                    'GET' => QueryConfig::getConfig(Query\Team\TeamListData::class),
                ]
            ),
            'GET' => QueryConfig::getConfig(Query\Team\TeamList::class),
            'POST' => CommandConfig::getPostConfig(Command\Team\CreateTeam::class),
        ]
    ],
];
