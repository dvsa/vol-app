<?php

use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Transfer\Query;
use Dvsa\Olcs\Transfer\Router\CommandConfig;
use Dvsa\Olcs\Transfer\Router\QueryConfig;
use Dvsa\Olcs\Transfer\Router\RouteConfig;

return [
    'master-template' => [
        'type' => 'Segment',
        'options' => [
            'route' => 'master-template[/]',
        ],
        'may_terminate' => false,
        'child_routes' => [
            'single' => RouteConfig::getSingleConfig(
                [
                    'GET' => QueryConfig::getConfig(Query\Letter\MasterTemplate\Get::class),
                    'PUT' => CommandConfig::getPutConfig(Command\Letter\MasterTemplate\Update::class),
                    'DELETE' => CommandConfig::getDeleteConfig(Command\Letter\MasterTemplate\Delete::class),
                ]
            ),
            'GET' => QueryConfig::getConfig(Query\Letter\MasterTemplate\GetList::class),
            'POST' => CommandConfig::getPostConfig(Command\Letter\MasterTemplate\Create::class),
        ]
    ],
];
