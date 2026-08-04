<?php

use Dvsa\Olcs\Transfer\Query;
use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Transfer\Router\CommandConfig;
use Dvsa\Olcs\Transfer\Router\QueryConfig;
use Dvsa\Olcs\Transfer\Router\RouteConfig;

return [
    'irhp-permits' => [
        'type' => 'Segment',
        'options' => [
            'route' => 'irhp-permits[/]',
        ],
        'may_terminate' => false,
        'child_routes' => [
            'single' => RouteConfig::getSingleConfig(
                [
                    'GET' => QueryConfig::getConfig(Query\IrhpPermit\ById::class),
                    'replace' => RouteConfig::getRouteConfig(
                        'replace',
                        ['POST' => CommandConfig::getPostConfig(Command\IrhpPermit\Replace::class)]
                    ),
                    'terminate' => RouteConfig::getRouteConfig(
                        'terminate',
                        ['POST' => CommandConfig::getPostConfig(Command\IrhpPermit\Terminate::class)]
                    )
                ]
            ),
            'by-irhp-id' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'by-irhp-id[/]',
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'GET' => QueryConfig::getConfig(Query\IrhpPermit\GetListByIrhpId::class),
                ]
            ],
            'by-licence' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'by-licence[/]',
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'GET' => QueryConfig::getConfig(Query\IrhpPermit\GetListByLicence::class),
                ]
            ],
            'unique-countries-by-licence' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'unique-countries-by-licence[/]',
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'GET' => QueryConfig::getConfig(Query\IrhpPermit\UniqueCountriesByLicence::class),
                ]
            ],
        ]
    ],
];
