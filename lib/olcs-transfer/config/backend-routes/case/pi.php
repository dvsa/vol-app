<?php

use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Transfer\Query;
use Dvsa\Olcs\Transfer\Router\CommandConfig;
use Dvsa\Olcs\Transfer\Router\QueryConfig;
use Dvsa\Olcs\Transfer\Router\RouteConfig;

return [
    'pi' => [
        'type' => 'Segment',
        'options' => [
            'route' => 'pi[/]',
        ],
        'may_terminate' => false,
        'child_routes' => [
            'single' => RouteConfig::getSingleConfig(
                [
                    'GET' => QueryConfig::getConfig(Query\Cases\Pi::class),
                    'agreed' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => 'agreed[/]'
                        ],
                        'may_terminate' => false,
                        'child_routes' => [
                            'PUT' => CommandConfig::getPutConfig(
                                Command\Cases\Pi\UpdateAgreedAndLegislation::class
                            ),
                        ]
                    ],
                    'close' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => 'close[/]'
                        ],
                        'may_terminate' => false,
                        'child_routes' => [
                            'PUT' => CommandConfig::getPutConfig(
                                Command\Cases\Pi\Close::class
                            ),
                        ]
                    ],
                    'decision' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => 'decision[/]'
                        ],
                        'may_terminate' => false,
                        'child_routes' => [
                            'PUT' => CommandConfig::getPutConfig(Command\Cases\Pi\UpdateDecision::class)
                        ]
                    ],
                    'tm-decision' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => 'tm-decision[/]'
                        ],
                        'may_terminate' => false,
                        'child_routes' => [
                            'PUT' => CommandConfig::getPutConfig(Command\Cases\Pi\UpdateTmDecision::class)
                        ]
                    ],
                    'reopen' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => 'reopen[/]'
                        ],
                        'may_terminate' => false,
                        'child_routes' => [
                            'PUT' => CommandConfig::getPutConfig(
                                Command\Cases\Pi\Reopen::class
                            ),
                        ]
                    ],
                    'sla' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => 'sla[/]'
                        ],
                        'may_terminate' => false,
                        'child_routes' => [
                            'PUT' => CommandConfig::getPutConfig(Command\Cases\Pi\UpdateSla::class)
                        ]
                    ],
                ]
            ),
            'hearing' =>         [
                'type' => 'Segment',
                'options' => [
                    'route' => 'hearing[/]',
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'GET' => QueryConfig::getConfig(Query\Cases\Pi\HearingList::class),
                    'single' => RouteConfig::getSingleConfig(
                        [
                            'GET' => QueryConfig::getConfig(Query\Cases\Pi\Hearing::class),
                            'PUT' => CommandConfig::getPutConfig(Command\Cases\Pi\UpdateHearing::class)
                        ]
                    ),
                    'POST' => CommandConfig::getPostConfig(Command\Cases\Pi\CreateHearing::class)
                ],
            ],
            'report' => RouteConfig::getRouteConfig(
                'report',
                [
                    'GET' => QueryConfig::getConfig(Query\Cases\Pi\ReportList::class),
                ]
            ),
            'definition' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'definition[/]',
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'GET' => QueryConfig::getConfig(Query\Cases\Pi\PiDefinitionList::class)
                ]
            ],
            'POST' => CommandConfig::getPostConfig(Command\Cases\Pi\CreateAgreedAndLegislation::class),
            'sla-exceptions' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'sla-exceptions[/]'
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'GET' => QueryConfig::getConfig(Query\Cases\Pi\SlaExceptionList::class),
                    'POST' => CommandConfig::getPostConfig(Command\Cases\Pi\CreatePiSlaException::class),
                ],
            ],
        ],
    ]
];
