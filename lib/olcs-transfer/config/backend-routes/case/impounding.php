<?php

use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Transfer\Query;
use Dvsa\Olcs\Transfer\Router\CommandConfig;
use Dvsa\Olcs\Transfer\Router\QueryConfig;
use Dvsa\Olcs\Transfer\Router\RouteConfig;

return [
    'impounding' => [
        'type' => 'Segment',
        'options' => [
            'route' => 'impounding[/]',
        ],
        'may_terminate' => false,
        'child_routes' => [
            'GET' => QueryConfig::getConfig(Query\Cases\Impounding\ImpoundingList::class),
            'single' => RouteConfig::getSingleConfig(
                [
                    'GET' => QueryConfig::getConfig(Query\Cases\Impounding\Impounding::class),
                    'PUT' => CommandConfig::getPutConfig(Command\Cases\Impounding\UpdateImpounding::class),
                    'DELETE' => CommandConfig::getDeleteConfig(
                        Command\Cases\Impounding\DeleteImpounding::class
                    )
                ]
            ),
            'POST' => CommandConfig::getPostConfig(Command\Cases\Impounding\CreateImpounding::class),
        ]
    ]
];
