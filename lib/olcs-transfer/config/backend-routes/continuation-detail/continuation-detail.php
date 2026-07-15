<?php

use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Transfer\Query;
use Dvsa\Olcs\Transfer\Router\CommandConfig;
use Dvsa\Olcs\Transfer\Router\QueryConfig;
use Dvsa\Olcs\Transfer\Router\RouteConfig;

return [
    'continuation-detail' => RouteConfig::getRouteConfig(
        'continuation-detail',
        [
            'single' => RouteConfig::getSingleConfig(
                [
                    'licence-checklist' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => 'licence-checklist[/]'
                        ],
                        'may_terminate' => false,
                        'child_routes' => [
                            'GET' => QueryConfig::getConfig(Query\ContinuationDetail\LicenceChecklist::class),
                        ]
                    ],
                    'review' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => 'review[/]'
                        ],
                        'may_terminate' => false,
                        'child_routes' => [
                            'GET' => QueryConfig::getConfig(Query\ContinuationDetail\Review::class),
                        ]
                    ],
                    'finances' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => 'finances[/]'
                        ],
                        'may_terminate' => false,
                        'child_routes' => [
                            'PUT' => CommandConfig::getPutConfig(Command\ContinuationDetail\UpdateFinances::class),
                        ]
                    ],
                    'insufficient-finances' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => 'insufficient-finances[/]'
                        ],
                        'may_terminate' => false,
                        'child_routes' => [
                            'PUT' => CommandConfig::getPutConfig(
                                Command\ContinuationDetail\UpdateInsufficientFinances::class
                            ),
                        ]
                    ],
                    'submit' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => 'submit[/]'
                        ],
                        'may_terminate' => false,
                        'child_routes' => [
                            'PUT' => CommandConfig::getPutConfig(Command\ContinuationDetail\Submit::class),
                        ]
                    ],
                    'PUT' => CommandConfig::getPutConfig(Command\ContinuationDetail\Update::class),
                    'GET' => QueryConfig::getConfig(Query\ContinuationDetail\Get::class),
                ]
            ),
            'checklist-reminders' => RouteConfig::getRouteConfig(
                'checklist-reminders',
                [
                    'GET' => QueryConfig::getConfig(Query\ContinuationDetail\ChecklistReminders::class)
                ]
            ),
            'queue' =>  RouteConfig::getRouteConfig(
                'queue',
                [
                    'POST' => CommandConfig::getPostConfig(Command\ContinuationDetail\Queue::class)
                ]
            ),
            'prepare-continuations' =>  RouteConfig::getRouteConfig(
                'prepare-continuations',
                [
                    'POST' => CommandConfig::getPostConfig(Command\ContinuationDetail\PrepareContinuations::class)
                ]
            ),
            'list' => RouteConfig::getRouteConfig(
                'list',
                [
                    'GET' => QueryConfig::getConfig(Query\ContinuationDetail\GetList::class)
                ]
            ),
        ]
    )
];
