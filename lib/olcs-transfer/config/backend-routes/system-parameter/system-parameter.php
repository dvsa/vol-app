<?php

use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Transfer\Query;
use Dvsa\Olcs\Transfer\Router\CommandConfig;
use Dvsa\Olcs\Transfer\Router\QueryConfig;
use Dvsa\Olcs\Transfer\Router\RouteConfig;

return [
    'system-parameter' => [
        'type' => 'Segment',
        'options' => [
            'route' => 'system-parameter[/]',
        ],
        'may_terminate' => false,
        'child_routes' => [
            'single' => RouteConfig::getSingleConfig(
                [
                    'GET' => QueryConfig::getConfig(Query\SystemParameter\SystemParameter::class),
                    'PUT' => CommandConfig::getPutConfig(Command\SystemParameter\UpdateSystemParameter::class),
                    'DELETE' => CommandConfig::getDeleteConfig(Command\SystemParameter\DeleteSystemParameter::class),
                ]
            ),
            'GET' => QueryConfig::getConfig(Query\SystemParameter\SystemParameterList::class),
            'POST' => CommandConfig::getPostConfig(Command\SystemParameter\CreateSystemParameter::class),
        ]
    ],
];
