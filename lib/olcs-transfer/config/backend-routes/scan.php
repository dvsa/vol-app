<?php

use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Transfer\Query;
use Dvsa\Olcs\Transfer\Router\CommandConfig;
use Dvsa\Olcs\Transfer\Router\QueryConfig;
use Dvsa\Olcs\Transfer\Router\RouteConfig;

return [
    'scan' => [
        'type' => 'Segment',
        'options' => [
            'route' => 'scan[/]',
        ],
        'may_terminate' => false,
        'child_routes' => [
            'separator-sheet' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'separator-sheet[/]',
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'POST' => CommandConfig::getPostConfig(Command\Scan\CreateSeparatorSheet::class),
                ],
            ],
            'continuation-separator-sheet' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'continuation-separator-sheet[/]',
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'POST' => CommandConfig::getPostConfig(
                        Command\Scan\CreateContinuationSeparatorSheet::class
                    ),
                ],
            ],
            'single' => RouteConfig::getSingleConfig(
                [
                    'GET'    => QueryConfig::getConfig(Query\Scan\GetSingle::class),
                ]
            ),
            'create-document' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'create-document[/]',
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'POST' => CommandConfig::getPostConfig(Command\Scan\CreateDocument::class),
                ]
            ],
        ]
    ],
];
