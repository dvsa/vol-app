<?php

use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Transfer\Query;
use Dvsa\Olcs\Transfer\Router\CommandConfig;
use Dvsa\Olcs\Transfer\Router\QueryConfig;
use Dvsa\Olcs\Transfer\Router\RouteConfig;

return [
    'condition-undertaking' => RouteConfig::getRouteConfig(
        'condition-undertaking',
        [
            'GET' => QueryConfig::getConfig(Query\ConditionUndertaking\GetList::class),
            'DELETE' => CommandConfig::getDeleteConfig(Command\ConditionUndertaking\DeleteList::class),
            'POST' => CommandConfig::getPostConfig(Command\ConditionUndertaking\Create::class),
            'single' => RouteConfig::getSingleConfig(
                [
                    'PUT' => CommandConfig::getPutConfig(Command\ConditionUndertaking\Update::class),
                    'GET' => QueryConfig::getConfig(Query\ConditionUndertaking\Get::class),
                    'DELETE' => CommandConfig::getDeleteConfig(Command\ConditionUndertaking\Delete::class),
                ]
            ),
        ]
    )
];
