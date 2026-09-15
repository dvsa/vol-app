<?php

use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Transfer\Query;
use Dvsa\Olcs\Transfer\Router\CommandConfig;
use Dvsa\Olcs\Transfer\Router\QueryConfig;
use Dvsa\Olcs\Transfer\Router\RouteConfig;

return [
    //allows to check permit eligibility based on the logged in user
    //to do this using the organisation id, see the corresponding route below

    'organisation' => RouteConfig::getRouteConfig(
        'organisation',
        [
            'single' => RouteConfig::getSingleConfig(
                [
                    'GET' => QueryConfig::getConfig(Query\Organisation\Organisation::class),
                    'business-type' => RouteConfig::getRouteConfig(
                        'business-type',
                        [
                            'PUT' => CommandConfig::getPutConfig(Command\Organisation\UpdateBusinessType::class),
                        ]
                    ),
                    'business-details' => RouteConfig::getRouteConfig(
                        'business-details',
                        [
                            'GET' => QueryConfig::getConfig(Query\Organisation\BusinessDetails::class),
                        ]
                    ),
                    'outstanding-fees' => RouteConfig::getRouteConfig(
                        'outstanding-fees',
                        [
                            'GET' => QueryConfig::getConfig(Query\Organisation\OutstandingFees::class),
                        ]
                    ),
                    'dashboard' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => 'dashboard[/]',
                        ],
                        'may_terminate' => false,
                        'child_routes' => [
                            'GET' => QueryConfig::getConfig(Query\Organisation\Dashboard::class),
                        ],
                    ],
                    'people' => RouteConfig::getRouteConfig(
                        'people',
                        [
                            'GET' => QueryConfig::getConfig(Query\Organisation\People::class),
                        ]
                    ),
                    'unlicensed-cases' => RouteConfig::getRouteConfig(
                        'unlicensed-cases',
                        [
                            'GET' => QueryConfig::getConfig(Query\Organisation\UnlicensedCases::class),
                        ]
                    ),
                    'transfer' => RouteConfig::getRouteConfig(
                        'transfer',
                        [
                            'PUT' => CommandConfig::getPutConfig(Command\Organisation\TransferTo::class),
                        ]
                    ),
                ]
            ),
            'business-details' => RouteConfig::getRouteConfig(
                'business-details',
                [
                    'licence' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => 'licence/:id[/]',
                        ],
                        'may_terminate' => false,
                        'child_routes' => [
                            'GET' => QueryConfig::getConfig(Query\Licence\BusinessDetails::class),
                            'PUT' => CommandConfig::getPutConfig(Command\Licence\UpdateBusinessDetails::class),
                        ]
                    ],
                    'application' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => 'application/:id[/]',
                        ],
                        'may_terminate' => false,
                        'child_routes' => [
                            'PUT' => CommandConfig::getPutConfig(Command\Application\UpdateBusinessDetails::class),
                        ]
                    ]
                ]
            ),
            'cpid' => RouteConfig::getRouteConfig(
                'cpid',
                [
                    'POST' => CommandConfig::getPostConfig(Command\Organisation\CpidOrganisationExport::class),
                    'GET' => QueryConfig::getConfig(Query\Organisation\CpidOrganisation::class),
                ]
            ),
            'generate-name' => RouteConfig::getRouteConfig(
                'generate-name',
                [
                    'POST' => CommandConfig::getPostConfig(Command\Organisation\GenerateName::class),
                ]
            ),
        ]
    )
];
