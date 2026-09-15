<?php

use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Transfer\Query;
use Dvsa\Olcs\Transfer\Router\CommandConfig;
use Dvsa\Olcs\Transfer\Router\QueryConfig;
use Dvsa\Olcs\Transfer\Router\RouteConfig;

return [
    'person' => [
        'type' => 'Segment',
        'options' => [
            'route' => 'person[/]',
        ],
        'may_terminate' => false,
        'child_routes' => [
            'single' => RouteConfig::getSingleConfig(
                [
                    'GET' => QueryConfig::getConfig(Query\Person\Person::class),
                    'PUT' => CommandConfig::getPutConfig(Command\Person\Update::class),
                ]
            ),
        ]
    ],
];
