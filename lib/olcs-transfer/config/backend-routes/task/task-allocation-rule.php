<?php

use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Transfer\Query;
use Dvsa\Olcs\Transfer\Router\CommandConfig;
use Dvsa\Olcs\Transfer\Router\QueryConfig;
use Dvsa\Olcs\Transfer\Router\RouteConfig;

return [
    'task-allocation-rule' => RouteConfig::getRouteConfig(
        'task-allocation-rule',
        [
            'GET' => QueryConfig::getConfig(Query\TaskAllocationRule\GetList::class),
            'DELETE' => CommandConfig::getDeleteConfig(Command\TaskAllocationRule\DeleteList::class),
            'POST' => CommandConfig::getPostConfig(Command\TaskAllocationRule\Create::class),
            'single' => RouteConfig::getSingleConfig(
                [
                    'GET' => QueryConfig::getConfig(Query\TaskAllocationRule\Get::class),
                    'PUT' => CommandConfig::getPutConfig(Command\TaskAllocationRule\Update::class),
                ]
            ),
        ]
    ),
];
