<?php

use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Transfer\Query;
use Dvsa\Olcs\Transfer\Router\CommandConfig;
use Dvsa\Olcs\Transfer\Router\QueryConfig;
use Dvsa\Olcs\Transfer\Router\RouteConfig;

return [
    'feature-toggle' => [
        'type' => 'Segment',
        'options' => [
            'route' => 'feature-toggle[/]',
        ],
        'may_terminate' => false,
        'child_routes' => [
            'single' => RouteConfig::getSingleConfig(
                [
                    'GET' => QueryConfig::getConfig(Query\FeatureToggle\ById::class),
                    'PUT' => CommandConfig::getPutConfig(Command\FeatureToggle\Update::class),
                    'DELETE' => CommandConfig::getDeleteConfig(Command\FeatureToggle\Delete::class),
                ]
            ),
            'GET' => QueryConfig::getConfig(Query\FeatureToggle\GetList::class),
            'POST' => CommandConfig::getPostConfig(Command\FeatureToggle\Create::class),
            'check' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'check',
                    'defaults' => [
                        'controller' => 'Api\Generic'
                    ]
                ],
                'child_routes' => [
                    'GET' => QueryConfig::getConfig(Query\FeatureToggle\IsEnabled::class),
                ]
            ]
        ]
    ],
];
