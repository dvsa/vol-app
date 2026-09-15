<?php

use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Transfer\Query;
use Dvsa\Olcs\Transfer\Router\CommandConfig;
use Dvsa\Olcs\Transfer\Router\QueryConfig;
use Dvsa\Olcs\Transfer\Router\RouteConfig;

return [
    'task-alpha-split' => RouteConfig::getRouteConfig(
        'task-alpha-split',
        [
            'GET' => QueryConfig::getConfig(Query\TaskAlphaSplit\GetList::class),
            'DELETE' => CommandConfig::getDeleteConfig(Command\TaskAlphaSplit\DeleteList::class),
            'POST' => CommandConfig::getPostConfig(Command\TaskAlphaSplit\Create::class),
            'single' => RouteConfig::getSingleConfig(
                [
                    'GET' => QueryConfig::getConfig(Query\TaskAlphaSplit\Get::class),
                    'PUT' => CommandConfig::getPutConfig(Command\TaskAlphaSplit\Update::class),
                    'DELETE' => CommandConfig::getDeleteConfig(Command\TaskAlphaSplit\Delete::class),
                ]
            ),
        ]
    ),
];
