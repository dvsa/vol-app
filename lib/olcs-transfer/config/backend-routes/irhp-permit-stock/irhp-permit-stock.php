<?php

use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Transfer\Query;
use Dvsa\Olcs\Transfer\Router\CommandConfig;
use Dvsa\Olcs\Transfer\Router\QueryConfig;
use Dvsa\Olcs\Transfer\Router\RouteConfig;

return [
    'irhp-permit-stock' => [
        'type' => 'Segment',
        'options' => [
            'route' => 'irhp-permit-stock[/]',
        ],
        'may_terminate' => false,
        'child_routes' => [
            'single' => RouteConfig::getSingleConfig(
                [
                    'GET' => QueryConfig::getConfig(Query\IrhpPermitStock\ById::class),
                    'PUT' => CommandConfig::getPutConfig(Command\IrhpPermitStock\Update::class),
                    'DELETE' => CommandConfig::getDeleteConfig(Command\IrhpPermitStock\Delete::class),
                ]
            ),
            'GET' => QueryConfig::getConfig(Query\IrhpPermitStock\GetList::class),
            'POST' => CommandConfig::getPostConfig(Command\IrhpPermitStock\Create::class),
            'available-countries' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'available-countries[/]',
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'GET' => QueryConfig::getConfig(Query\IrhpPermitStock\AvailableCountries::class),
                ]
            ],
            'available-bilateral' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'available-bilateral[/]',
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'GET' => QueryConfig::getConfig(Query\IrhpPermitStock\AvailableBilateral::class),
                ]
            ],
        ]
    ],
];
