<?php

use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Transfer\Query;
use Dvsa\Olcs\Transfer\Router\CommandConfig;
use Dvsa\Olcs\Transfer\Router\QueryConfig;
use Dvsa\Olcs\Transfer\Router\RouteConfig;

return [
    'community-lic' => [
        'type' => 'Segment',
        'options' => [
            'route' => 'community-lic[/]',
        ],
        'may_terminate' => false,
        'child_routes' => [
            'single' => RouteConfig::getSingleConfig(
                [
                    'GET' => QueryConfig::getConfig(Query\CommunityLic\CommunityLicence::class),
                ]
            ),
            'list' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'list[/]',
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'GET' => QueryConfig::getConfig(Query\CommunityLic\CommunityLicences::class),
                ]
            ],
            'application' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'application/',
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'create' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => 'create[/]',
                        ],
                        'may_terminate' => false,
                        'child_routes' => [
                            'POST' =>
                                CommandConfig::getPostConfig(
                                    Command\CommunityLic\Application\Create::class
                                ),
                        ]
                    ],
                    'create-office-copy' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => 'create-office-copy[/]',
                        ],
                        'may_terminate' => false,
                        'child_routes' => [
                            'POST' =>
                                CommandConfig::getPostConfig(
                                    Command\CommunityLic\Application\CreateOfficeCopy::class
                                ),
                        ]
                    ],
                ]
            ],
            'licence' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'licence/',
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'create' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => 'create[/]',
                        ],
                        'may_terminate' => false,
                        'child_routes' => [
                            'POST' =>
                                CommandConfig::getPostConfig(
                                    Command\CommunityLic\Licence\Create::class
                                ),
                        ]
                    ],
                    'create-office-copy' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => 'create-office-copy[/]',
                        ],
                        'may_terminate' => false,
                        'child_routes' => [
                            'POST' =>
                                CommandConfig::getPostConfig(
                                    Command\CommunityLic\Licence\CreateOfficeCopy::class
                                ),
                        ]
                    ],
                ]
            ],
            'annul' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'annul[/]',
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'POST' =>
                        CommandConfig::getPostConfig(
                            Command\CommunityLic\Annul::class
                        ),
                ]
            ],
            'restore' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'restore[/]',
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'POST' =>
                        CommandConfig::getPostConfig(
                            Command\CommunityLic\Restore::class
                        ),
                ]
            ],
            'stop' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'stop[/]',
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'POST' =>
                        CommandConfig::getPostConfig(
                            Command\CommunityLic\Stop::class
                        ),
                ]
            ],
            'reprint' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'reprint[/]',
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'POST' =>
                        CommandConfig::getPostConfig(
                            Command\CommunityLic\Reprint::class
                        ),
                ]
            ],
            'edit-suspension' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'edit-suspension[/]',
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'single' => RouteConfig::getSingleConfig(
                        [
                            'PUT' => CommandConfig::getPutConfig(Command\CommunityLic\EditSuspension::class),
                        ]
                    ),
                ]
            ],
        ],
    ],
];
