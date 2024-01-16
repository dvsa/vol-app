<?php
declare(strict_types=1);

use Dvsa\Olcs\Auth\Controller\ChangePasswordController;
use Dvsa\Olcs\Auth\Controller\ExpiredPasswordController;
use Dvsa\Olcs\Auth\Controller\ForgotPasswordController;
use Dvsa\Olcs\Auth\Controller\LogoutController;
use Dvsa\Olcs\Auth\Controller\ResetPasswordController;
use Laminas\Router\Http\Method;
use Laminas\Router\Http\Segment;

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
                        'route' => 'expired-password/[:authId]',
                        'defaults' => [
                            'controller' => ExpiredPasswordController::class,
                            'action' => 'index'
                        ]
                    ]
                ],
                'forgot-password' => [
                    'type' => 'segment',
                    'options' => [
                        'route' => 'forgot-password[/]',
                        'defaults' => [
                            'controller' => ForgotPasswordController::class,
                            'action' => 'index'
                        ],
                    ],
                ],
                'reset-password' => [
                    'type' => 'segment',
                    'options' => [
                        'route' => 'reset-password[/]',
                        'defaults' => [
                            'controller' => ResetPasswordController::class,
                            'action' => 'index'
                        ],
                    ],
                ],
                'logout' => [
                    'type' => 'segment',
                    'options' => [
                        'route' => 'logout[/]',
                        'defaults' => [
                            'controller' => LogoutController::class,
                            'action' => 'index'
                        ]
                    ],
                ],
                'validate' => [
                    'type' => \Laminas\Router\Http\Segment::class,
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
                    'controller' => ChangePasswordController::class,
                    'action' => 'index'
                ],
            ],
        ],
    ]
];
