<?php

use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Transfer\Router\CommandConfig;
use Dvsa\Olcs\Transfer\Router\QueryConfig;
use Dvsa\Olcs\Transfer\Router\RouteConfig;
use Dvsa\Olcs\Transfer\Query;

return [
    'tm-responsibilities' => [
        'type' => 'Segment',
        'options' => [
            'route' => 'tm-responsibilities/',
        ],
        'may_terminate' => false,
        'child_routes' => [
            'transport-manager' => RouteConfig::getRouteConfig(
                'transport-manager',
                [
                    'named-single' => RouteConfig::getNamedSingleConfig(
                        'transportManager',
                        [
                            'GET' => QueryConfig::getConfig(Query\TmResponsibilities\TmResponsibilitiesList::class),
                        ]
                    )
                ]
            ),
            'transport-manager-application' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'transport-manager-application[/]',
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'single' => RouteConfig::getSingleConfig(
                        [
                            'GET' => QueryConfig::getConfig(
                                Query\TransportManagerApplication\GetForResponsibilities::class
                            ),
                            'PUT' => CommandConfig::getPutConfig(
                                Command\TransportManagerApplication\UpdateForResponsibilities::class
                            ),
                        ]
                    ),
                    'POST' =>
                        CommandConfig::getPostConfig(
                            Command\TransportManagerApplication\CreateForResponsibilities::class
                        ),
                    'DELETE' => CommandConfig::getDeleteConfig(
                        Command\TransportManagerApplication\DeleteForResponsibilities::class
                    ),
                ]
            ],
            'transport-manager-licence' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'transport-manager-licence[/]',
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'single' => RouteConfig::getSingleConfig(
                        [
                            'GET' => QueryConfig::getConfig(
                                Query\TransportManagerLicence\GetForResponsibilities::class
                            ),
                            'PUT' => CommandConfig::getPutConfig(
                                Command\TransportManagerLicence\UpdateForResponsibilities::class
                            ),
                        ]
                    ),
                    'DELETE' => CommandConfig::getDeleteConfig(
                        Command\TransportManagerLicence\DeleteForResponsibilities::class
                    ),
                ]
            ],
            'documents' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'documents[/]',
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'GET' => QueryConfig::getConfig(
                        Query\TmResponsibilities\GetDocumentsForResponsibilities::class
                    )
                ]
            ],
        ],
    ],
];
