<?php

use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Transfer\Query;
use Dvsa\Olcs\Transfer\Router\CommandConfig;
use Dvsa\Olcs\Transfer\Router\QueryConfig;
use Dvsa\Olcs\Transfer\Router\RouteConfig;

return [
    'submission' => [
        'type' => 'Segment',
        'options' => [
            'route' => 'submission[/]',
        ],
        'may_terminate' => false,
        'child_routes' => [
            'GET' => QueryConfig::getConfig(Query\Submission\SubmissionList::class),
            'single' => RouteConfig::getSingleConfig(
                [
                    'GET' => QueryConfig::getConfig(Query\Submission\Submission::class),
                    'PUT' => CommandConfig::getPutConfig(Command\Submission\UpdateSubmission::class),
                    'DELETE' => CommandConfig::getDeleteConfig(
                        Command\Submission\DeleteSubmission::class
                    ),
                    'close' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => 'close[/]',
                        ],
                        'may_terminate' => false,
                        'child_routes' => [
                            'PUT' => CommandConfig::getPutConfig(Command\Submission\CloseSubmission::class),
                        ]
                    ],
                    'reopen' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => 'reopen[/]',
                        ],
                        'may_terminate' => false,
                        'child_routes' => [
                            'PUT' => CommandConfig::getPutConfig(Command\Submission\ReopenSubmission::class),
                        ]
                    ],
                    'store' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => 'store[/]',
                        ],
                        'may_terminate' => false,
                        'child_routes' => [
                            'POST' => CommandConfig::getPostConfig(Command\Submission\StoreSubmissionSnapshot::class),
                        ]
                    ],
                ]
            ),
            'refresh' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'refresh[/]',
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'PUT' => CommandConfig::getPutConfig(Command\Submission\RefreshSubmissionSections::class),
                ]
            ],
            'filter' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'filter[/]',
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'PUT' => CommandConfig::getPutConfig(Command\Submission\FilterSubmissionSections::class),
                ]
            ],
            'assign' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'assign[/]',
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'PUT' => CommandConfig::getPutConfig(Command\Submission\AssignSubmission::class),
                ]
            ],
            'information-complete' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'information-complete[/]',
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'PUT' => CommandConfig::getPutConfig(Command\Submission\InformationCompleteSubmission::class),
                ]
            ],
            'POST' => CommandConfig::getPostConfig(Command\Submission\CreateSubmission::class),
        ]
    ]
];
