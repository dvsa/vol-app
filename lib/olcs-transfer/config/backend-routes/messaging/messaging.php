<?php

use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Transfer\Query;
use Dvsa\Olcs\Transfer\Router\CommandConfig;
use Dvsa\Olcs\Transfer\Router\QueryConfig;
use Dvsa\Olcs\Transfer\Router\RouteConfig;

return [
    'messaging' => RouteConfig::getRouteConfig(
        'messaging',
        [
            'documents' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'documents[/]',
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'GET' => QueryConfig::getConfig(Query\Messaging\Documents::class),
                ],
            ],
            'fileuploads' => RouteConfig::getRouteConfig(
                'fileuploads',
                [
                    'enable' => RouteConfig::getRouteConfig(
                        'enable',
                        [
                            'POST' => CommandConfig::getPostConfig(Command\Messaging\EnableFileUpload::class)
                        ]
                    ),
                    'disable' => RouteConfig::getRouteConfig(
                        'disable',
                        [
                            'POST' => CommandConfig::getPostConfig(Command\Messaging\DisableFileUpload::class)
                        ]
                    ),
                ],
            ),
            'conversations' => RouteConfig::getRouteConfig(
                'conversations',
                [
                    'POST' => CommandConfig::getPostConfig(Command\Messaging\Conversation\Create::class),
                    'by-licence' => RouteConfig::getRouteConfig(
                        'by-licence',
                        [
                            'GET' => QueryConfig::getConfig(Query\Messaging\Conversations\ByLicence::class),
                        ],
                    ),
                    'by-organisation' => RouteConfig::getRouteConfig(
                        'by-organisation',
                        [
                            'GET' => QueryConfig::getConfig(Query\Messaging\Conversations\ByOrganisation::class),
                        ],
                    ),
                    'by-case-to-licence' => RouteConfig::getRouteConfig(
                        'by-case-to-licence',
                        [
                            'GET' => QueryConfig::getConfig(Query\Messaging\Conversations\ByCaseToLicence::class),
                        ],
                    ),
                    'by-application-to-licence' => RouteConfig::getRouteConfig(
                        'by-application-to-licence',
                        [
                            'GET' => QueryConfig::getConfig(Query\Messaging\Conversations\ByApplicationToLicence::class),
                        ],
                    ),
                    'close' => RouteConfig::getRouteConfig(
                        'close',
                        [
                            'POST' => CommandConfig::getPostConfig(Command\Messaging\Conversation\Close::class)
                        ],
                    ),
                    'disable' => RouteConfig::getRouteConfig(
                        'disable',
                        [
                            'POST' => CommandConfig::getPostConfig(Command\Messaging\Conversation\Disable::class)
                        ],
                    ),
                    'enable' => RouteConfig::getRouteConfig(
                        'enable',
                        [
                            'POST' => CommandConfig::getPostConfig(Command\Messaging\Conversation\Enable::class)
                        ],
                    ),
                ],
            ),
            'messages' => RouteConfig::getRouteConfig(
                'messages',
                [
                    'POST' => CommandConfig::getPostConfig(Command\Messaging\Message\Create::class),
                    'by-conversation' => RouteConfig::getRouteConfig(
                        'by-conversation',
                        [
                            'GET' => QueryConfig::getConfig(Query\Messaging\Messages\ByConversation::class),
                        ],
                    ),
                    'unread-count-by-organisation-and-user' => RouteConfig::getRouteConfig(
                        'unread-count-by-organisation-and-user',
                        [
                            'GET' => QueryConfig::getConfig(Query\Messaging\Messages\UnreadCountByOrganisationAndUser::class),
                        ],
                    ),
                    'unread-count-by-licence-and-roles' => RouteConfig::getRouteConfig(
                        'unread-count-by-licence-and-roles',
                        [
                            'GET' => QueryConfig::getConfig(Query\Messaging\Messages\UnreadCountByLicenceAndRoles::class),
                        ],
                    ),
                ]
            ),
            'subjects' => RouteConfig::getRouteConfig(
                'subjects',
                [
                    'all' => RouteConfig::getRouteConfig(
                        'all',
                        [
                            'GET' => QueryConfig::getConfig(Query\Messaging\Subjects\All::class),
                        ],
                    ),
                ]
            ),
            'application-licence-list' => RouteConfig::getRouteConfig(
                'application-licence-list',
                [
                    'by-organisation' => RouteConfig::getRouteConfig(
                        'by-organisation',
                        [
                            'GET' => QueryConfig::getConfig(Query\Messaging\ApplicationLicenceList\ByOrganisation::class),
                        ],
                    ),
                    'by-licence-to-organisation' => RouteConfig::getRouteConfig(
                        'by-licence-to-organisation',
                        [
                            'GET' => QueryConfig::getConfig(Query\Messaging\ApplicationLicenceList\ByLicenceToOrganisation::class),
                        ],
                    ),
                    'by-application-to-organisation' => RouteConfig::getRouteConfig(
                        'by-application-to-organisation',
                        [
                            'GET' => QueryConfig::getConfig(Query\Messaging\ApplicationLicenceList\ByApplicationToOrganisation::class),
                        ],
                    ),
                    'by-case-to-organisation' => RouteConfig::getRouteConfig(
                        'by-case-to-organisation',
                        [
                            'GET' => QueryConfig::getConfig(Query\Messaging\ApplicationLicenceList\ByCaseToOrganisation::class),
                        ],
                    ),
                ]
            )
        ]
    )
];
