<?php

use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Transfer\Query;
use Dvsa\Olcs\Transfer\Router\CommandConfig;
use Dvsa\Olcs\Transfer\Router\QueryConfig;
use Dvsa\Olcs\Transfer\Router\RouteConfig;

return [
    'opposition' => [
        'type' => 'Segment',
        'options' => [
            'route' => 'opposition[/]',
        ],
        'may_terminate' => false,
        'child_routes' => [
            'GET' => QueryConfig::getConfig(Query\Opposition\OppositionList::class),
            'single' => RouteConfig::getSingleConfig(
                [
                    'GET' => QueryConfig::getConfig(Query\Opposition\Opposition::class),
                    'PUT' => CommandConfig::getPutConfig(Command\Opposition\UpdateOpposition::class),
                    'DELETE' => CommandConfig::getDeleteConfig(
                        Command\Opposition\DeleteOpposition::class
                    )
                ]
            ),
            'POST' => CommandConfig::getPostConfig(Command\Opposition\CreateOpposition::class)
        ]
    ],
];
