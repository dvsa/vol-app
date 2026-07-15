<?php

use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Transfer\Query;
use Dvsa\Olcs\Transfer\Router\CommandConfig;
use Dvsa\Olcs\Transfer\Router\QueryConfig;
use Dvsa\Olcs\Transfer\Router\RouteConfig;

return [
    'user' => [
        'type' => 'Segment',
        'options' => [
            'route' => 'user[/]',
        ],
        'may_terminate' => false,
        'child_routes' => [
            'roles' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'roles[/]',
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'GET' => QueryConfig::getConfig(Query\User\RoleList::class),
                ]
            ],
            'login' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'login[/]',
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'update-last-login' => RouteConfig::getRouteConfig(
                        'update-last-login',
                        [
                            'POST' => CommandConfig::getPostConfig(Command\User\UpdateUserLastLoginAt::class),
                        ]
                    )
                ]
            ],
            'internal' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'internal[/]',
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'single' => RouteConfig::getSingleConfig(
                        [
                            'GET' => QueryConfig::getConfig(Query\User\User::class),
                            'PUT' => CommandConfig::getPutConfig(Command\User\UpdateUser::class),
                            'DELETE' => CommandConfig::getDeleteConfig(Command\User\DeleteUser::class),
                        ]
                    ),
                    'GET' => QueryConfig::getConfig(Query\User\UserList::class),
                    'POST' => CommandConfig::getPostConfig(Command\User\CreateUser::class),
                ]
            ],
            /*
             * This route has been introduced to spefically address OLCS-11676. Ideally all references to the internal
             * route need to use a single DTO
             */
            'internal-only' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'internal-only[/]',
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'GET' => QueryConfig::getConfig(Query\User\UserListInternal::class),
                ]
            ],
            'selfserve' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'selfserve[/]',
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'register' => RouteConfig::getRouteConfig(
                        'register',
                        [
                            'POST' => CommandConfig::getPostConfig(Command\User\RegisterUserSelfserve::class),
                            'register-consultant-operator' => RouteConfig::getRouteConfig(
                                'register-consultant-operator',
                                [
                                    'POST' => CommandConfig::getPostConfig(Command\User\RegisterConsultantAndOperator::class),
                                ]
                            ),
                        ]
                    ),
                    'remind-username' => RouteConfig::getRouteConfig(
                        'remind-username',
                        [
                            'POST' => CommandConfig::getPostConfig(Command\User\RemindUsernameSelfserve::class),
                        ]
                    ),
                    'agree-terms' => RouteConfig::getRouteConfig(
                        'agree-terms',
                        [
                            'POST' => CommandConfig::getPostConfig(Command\User\AgreeTerms::class),
                        ]
                    ),
                    'single' => RouteConfig::getSingleConfig(
                        [
                            'GET' => QueryConfig::getConfig(Query\User\UserSelfserve::class),
                            'PUT' => CommandConfig::getPutConfig(Command\User\UpdateUserSelfserve::class),
                            'DELETE' => CommandConfig::getDeleteConfig(Command\User\DeleteUserSelfserve::class),
                        ]
                    ),
                    'GET' => QueryConfig::getConfig(Query\User\UserListSelfserve::class),
                    'POST' => CommandConfig::getPostConfig(Command\User\CreateUserSelfserve::class),
                ]
            ],
            'operator-admin-for-organisation-has-logged-in' => RouteConfig::getRouteConfig(
                'operator-admin-for-organisation-has-logged-in',
                [
                    'GET' => QueryConfig::getConfig(Query\User\OperatorAdminForOrganisationHasLoggedIn::class),
                ]
            ),
            'pid' => RouteConfig::getRouteConfig(
                'pid',
                [
                    'single' => RouteConfig::getSingleConfig(
                        [
                            'GET' => QueryConfig::getConfig(Query\User\Pid::class),
                        ]
                    ),
                ]
            ),
        ]
    ],
];
