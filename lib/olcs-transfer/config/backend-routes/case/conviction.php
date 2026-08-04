<?php

use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Transfer\Query;
use Dvsa\Olcs\Transfer\Router\CommandConfig;
use Dvsa\Olcs\Transfer\Router\QueryConfig;
use Dvsa\Olcs\Transfer\Router\RouteConfig;

return [
    'conviction' => [
        'type' => 'Segment',
        'options' => [
            'route' => 'conviction[/]',
        ],
        'may_terminate' => false,
        'child_routes' => [
            'GET'    => QueryConfig::getConfig(Query\Cases\Conviction\ConvictionList::class),
            'POST'   => CommandConfig::getPostConfig(Command\Cases\Conviction\Create::class),
            'single' => RouteConfig::getSingleConfig(
                [
                    'GET'    => QueryConfig::getConfig(Query\Cases\Conviction\Conviction::class),
                    'PUT'    => CommandConfig::getPutConfig(Command\Cases\Conviction\Update::class),
                    'DELETE' => CommandConfig::getDeleteConfig(Command\Cases\Conviction\Delete::class),
                ]
            )
        ]
    ]
];
