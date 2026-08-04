<?php

use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Transfer\Query;
use Dvsa\Olcs\Transfer\Router\CommandConfig;
use Dvsa\Olcs\Transfer\Router\QueryConfig;
use Dvsa\Olcs\Transfer\Router\RouteConfig;

return [
    'irhp-permit-window' => [
        'type' => 'Segment',
        'options' => [
            'route' => 'irhp-permit-window[/]',
        ],
        'may_terminate' => false,
        'child_routes' => [
            'single' => RouteConfig::getSingleConfig(
                [
                    'GET' => QueryConfig::getConfig(Query\IrhpPermitWindow\ById::class),
                    'PUT' => CommandConfig::getPutConfig(Command\IrhpPermitWindow\Update::class),
                    'DELETE' => CommandConfig::getDeleteConfig(Command\IrhpPermitWindow\Delete::class),
                ]
            ),
            'GET' => QueryConfig::getConfig(Query\IrhpPermitWindow\GetList::class),
            'POST' => CommandConfig::getPostConfig(Command\IrhpPermitWindow\Create::class),
            'open-by-country' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'open-by-country[/]',
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'GET' => QueryConfig::getConfig(Query\IrhpPermitWindow\OpenByCountry::class),
                ]
            ],
            'open-by-type' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'open-by-type[/]',
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'GET' => QueryConfig::getConfig(Query\IrhpPermitWindow\OpenByType::class),
                ]
            ],
        ]
    ],
];
