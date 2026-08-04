<?php

use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Transfer\Router\CommandConfig;
use Dvsa\Olcs\Transfer\Router\QueryConfig;
use Dvsa\Olcs\Transfer\Query;
use Dvsa\Olcs\Transfer\Router\RouteConfig;

return [
    'inspection-request' => [
        'type' => 'Segment',
        'options' => [
            'route' => 'inspection-request[/]',
        ],
        'may_terminate' => false,
        'child_routes' => [
            'single' => RouteConfig::getSingleConfig(
                [
                    'GET' => QueryConfig::getConfig(Query\InspectionRequest\InspectionRequest::class),
                    'DELETE' => CommandConfig::getDeleteConfig(Command\InspectionRequest\Delete::class),
                    'PUT' => CommandConfig::getPutConfig(Command\InspectionRequest\Update::class),
                ]
            ),
            'create' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'create[/]',
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'POST' =>
                        CommandConfig::getPostConfig(
                            Command\InspectionRequest\Create::class
                        ),
                ]
            ],
            'create-from-grant' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'create-from-grant[/]',
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'POST' =>
                        CommandConfig::getPostConfig(
                            Command\InspectionRequest\CreateFromGrant::class
                        ),
                ]
            ],
            'operating-centres' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'operating-centres[/]',
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'GET' => QueryConfig::getConfig(Query\InspectionRequest\OperatingCentres::class),
                ]
            ],
            'licence' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'licence[/]',
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'GET' => QueryConfig::getConfig(Query\InspectionRequest\LicenceInspectionRequestList::class),
                ]
            ],
            'application' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'application[/]',
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'GET' => QueryConfig::getConfig(Query\InspectionRequest\ApplicationInspectionRequestList::class),
                ]
            ],
        ],
    ],
];
