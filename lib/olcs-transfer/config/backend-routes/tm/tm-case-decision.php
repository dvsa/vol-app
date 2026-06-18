<?php

use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Transfer\Router\CommandConfig;
use Dvsa\Olcs\Transfer\Router\QueryConfig;
use Dvsa\Olcs\Transfer\Router\RouteConfig;
use Dvsa\Olcs\Transfer\Query;

return [
    'tm-case-decision' => [
        'type' => 'Segment',
        'options' => [
            'route' => 'tm-case-decision[/]',
        ],
        'may_terminate' => false,
        'child_routes' => [
            'GET' => QueryConfig::getConfig(Query\TmCaseDecision\GetByCase::class),
            'single' => RouteConfig::getSingleConfig(
                [
                    'DELETE' => CommandConfig::getDeleteConfig(Command\TmCaseDecision\Delete::class),
                    'repute-not-lost' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => 'repute-not-lost[/]',
                        ],
                        'may_terminate' => false,
                        'child_routes' => [
                            'PUT' => CommandConfig::getPutConfig(Command\TmCaseDecision\UpdateReputeNotLost::class),
                        ]
                    ],
                    'declare-unfit' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => 'declare-unfit[/]',
                        ],
                        'may_terminate' => false,
                        'child_routes' => [
                            'PUT' => CommandConfig::getPutConfig(Command\TmCaseDecision\UpdateDeclareUnfit::class),
                        ]
                    ],
                    'no-further-action' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => 'no-further-action[/]',
                        ],
                        'may_terminate' => false,
                        'child_routes' => [
                            'PUT' => CommandConfig::getPutConfig(Command\TmCaseDecision\UpdateNoFurtherAction::class),
                        ]
                    ],
                ]
            ),
            'repute-not-lost' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'repute-not-lost[/]',
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'POST' => CommandConfig::getPostConfig(Command\TmCaseDecision\CreateReputeNotLost::class),
                ]
            ],
            'declare-unfit' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'declare-unfit[/]',
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'POST' => CommandConfig::getPostConfig(Command\TmCaseDecision\CreateDeclareUnfit::class),
                ]
            ],
            'no-further-action' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'no-further-action[/]',
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'POST' => CommandConfig::getPostConfig(Command\TmCaseDecision\CreateNoFurtherAction::class),
                ]
            ],
        ]
    ],
];
