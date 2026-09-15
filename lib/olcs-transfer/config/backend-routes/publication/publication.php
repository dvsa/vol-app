<?php

use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Transfer\Query;
use Dvsa\Olcs\Transfer\Router\CommandConfig;
use Dvsa\Olcs\Transfer\Router\QueryConfig;
use Dvsa\Olcs\Transfer\Router\RouteConfig;

return [
    'publication' => [
        'type' => 'Segment',
        'options' => [
            'route' => 'publication[/]',
        ],
        'may_terminate' => false,
        'child_routes' => [
            'single' => RouteConfig::getSingleConfig(
                [
                    'generate' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => 'generate[/]',
                        ],
                        'may_terminate' => false,
                        'child_routes' => [
                            'PUT' => CommandConfig::getPutConfig(Command\Publication\Generate::class),
                        ]
                    ],
                    'publish' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => 'publish[/]',
                        ],
                        'may_terminate' => false,
                        'child_routes' => [
                            'PUT' => CommandConfig::getPutConfig(Command\Publication\Publish::class),
                        ]
                    ],
                ]
            ),
            'recipient' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'recipient[/]',
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'single' => RouteConfig::getSingleConfig(
                        [
                            'GET' => QueryConfig::getConfig(Query\Publication\Recipient::class),
                            'PUT' => CommandConfig::getPutConfig(Command\Publication\UpdateRecipient::class),
                        ]
                    ),
                    'GET' => QueryConfig::getConfig(Query\Publication\RecipientList::class),
                    'POST' => CommandConfig::getPostConfig(Command\Publication\CreateRecipient::class),
                    'DELETE' => CommandConfig::getDeleteConfig(Command\Publication\DeleteRecipient::class),
                ]
            ],
            'bus' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'bus[/]',
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'POST' => CommandConfig::getPostConfig(Command\Publication\Bus::class),
                ]
            ],
            'application' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'application[/]',
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'POST' => CommandConfig::getPostConfig(Command\Publication\Application::class),
                ]
            ],
            'pending-list' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'pending-list[/]',
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'GET' => QueryConfig::getConfig(Query\Publication\PendingList::class),
                ]
            ],
            'published-list' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'published-list[/]',
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'GET' => QueryConfig::getConfig(Query\Publication\PublishedList::class),
                ]
            ],
            'link' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'link[/]',
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'single' => RouteConfig::getSingleConfig(
                        [
                            'GET' => QueryConfig::getConfig(Query\Publication\PublicationLink::class),
                            'PUT' => CommandConfig::getPutConfig(Command\Publication\UpdatePublicationLink::class)
                        ]
                    ),
                    'tm-list' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => 'tm-list[/]',
                        ],
                        'may_terminate' => false,
                        'child_routes' => [
                            'GET' => QueryConfig::getConfig(Query\Publication\PublicationLinkTmList::class),
                        ]
                    ],
                    'GET' => QueryConfig::getConfig(Query\Publication\PublicationLinkList::class),
                    'DELETE' => CommandConfig::getDeleteConfig(Command\Publication\DeletePublicationLink::class),
                ]
            ],
        ],
    ],
];
