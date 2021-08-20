<?php
declare(strict_types=1);

use Laminas\Mvc\Router\Http\Method;
use Laminas\Mvc\Router\Http\Segment;

return [
    [
        'auth' => [
            'type' => Segment::class,
            'options' => [
                'route' => '/auth[/]'
            ],
            'may_terminate' => false,
            'child_routes' => [
                'login' => [
                    'may_terminate' => false,
                    'type' => Segment::class,
                    'options' => [
                        'route' => 'login[/]',
                        'defaults' => [
                            'controller' => \Olcs\Controller\Auth\LoginController::class,
                        ]
                    ],
                    'child_routes' => [
                        'GET' => [
                            'may_terminate' => true,
                            'type' => Method::class,
                            'options' => [
                                'verb' => 'GET',
                                'defaults' => [
                                    'action' => 'index',
                                ],
                            ],
                        ],
                        'POST' => [
                            'may_terminate' => true,
                            'type' => Method::class,
                            'options' => [
                                'verb' => 'POST',
                                'defaults' => [
                                    'action' => 'post',
                                ],
                            ],
                        ],
                    ],
                ],
                'expired-password' => [
                    'type' => 'segment',
                    'options' => [
                        'route' => 'expired-password/:authId[/]',
                        'defaults' => [
                            'controller' => 'Auth\ExpiredPasswordController',
                            'action' => 'index'
                        ]
                    ]
                ],
                'forgot-password' => [
                    'type' => 'segment',
                    'options' => [
                        'route' => 'forgot-password[/]',
                        'defaults' => [
                            'controller' => 'Auth\ForgotPasswordController',
                            'action' => 'index'
                        ],
                    ],
                ],
                'reset-password' => [
                    'type' => 'segment',
                    'options' => [
                        'route' => 'reset-password[/]',
                        'defaults' => [
                            'controller' => 'Auth\ResetPasswordController',
                            'action' => 'index'
                        ],
                    ],
                ],
                'logout' => [
                    'type' => 'segment',
                    'options' => [
                        'route' => 'logout[/]',
                        'defaults' => [
                            'controller' => 'Auth\LogoutController',
                            'action' => 'index'
                        ]
                    ],
                ],
                'validate' => [
                    'type' => \Laminas\Mvc\Router\Http\Segment::class,
                    'options' => [
                        'route' => 'validate[/]',
                        'defaults' => [
                            'controller' => \Dvsa\Olcs\Auth\Controller\ValidateController::class,
                            'action' => 'index',
                        ]
                    ],
                ],
            ]
        ],
        'change-password' => [
            'type' => 'segment',
            'options' => [
                'route' => '/change-password[/]',
                'defaults' => [
                    'controller' => 'Auth\ChangePasswordController',
                    'action' => 'index'
                ],
            ],
        ],
    ]
];
