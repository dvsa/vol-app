<?php

use Dvsa\Olcs\Auth\Controller\ChangePasswordController;
use Dvsa\Olcs\Auth\Controller\ExpiredPasswordController;
use Dvsa\Olcs\Auth\Controller\ForgotPasswordController;
use Dvsa\Olcs\Auth\Controller\LogoutController;
use Dvsa\Olcs\Auth\Controller\ResetPasswordController;
use Dvsa\Olcs\Auth\Controller\ValidateController;
use Dvsa\Olcs\Auth\ControllerFactory\ChangePasswordControllerFactory;
use Dvsa\Olcs\Auth\ControllerFactory\ExpiredPasswordControllerFactory;
use Dvsa\Olcs\Auth\ControllerFactory\ForgotPasswordControllerFactory;
use Dvsa\Olcs\Auth\ControllerFactory\LogoutControllerFactory;
use Dvsa\Olcs\Auth\ControllerFactory\ResetPasswordControllerFactory;
use Dvsa\Olcs\Auth\ControllerFactory\ValidateControllerFactory;
use Dvsa\Olcs\Auth\Service\Auth\ExpiredPasswordService;
use Dvsa\Olcs\Auth\Service\Auth\LoginService;

return [
    'router' => [
        'routes' => [
            'auth' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/auth[/]'
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'expired-password' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => 'expired-password[/:authId]',
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
                                'controller' => ValidateController::class,
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
    ],
    'controllers' => [
        'invokables' => [
        ],
        'aliases' => [
        ],
        'factories' => [
            ValidateController::class => ValidateControllerFactory::class,
            LogoutController::class => LogoutControllerFactory::class,
            ExpiredPasswordController::class => ExpiredPasswordControllerFactory::class,
            ForgotPasswordController::class => ForgotPasswordControllerFactory::class,
            ChangePasswordController::class => ChangePasswordControllerFactory::class,
            ResetPasswordController::class => ResetPasswordControllerFactory::class
        ]
    ],
    'service_manager' => [
        'invokables' => [
            'Auth\ResponseDecoderService' => \Dvsa\Olcs\Auth\Service\Auth\ResponseDecoderService::class,
        ],
        'factories' => [
            \Dvsa\Olcs\Auth\Service\Auth\PasswordService::class =>
                \Dvsa\Olcs\Auth\Service\Auth\PasswordServiceFactory::class,
        ]
    ],
    'view_manager' => [
        'template_map' => [
            'auth/login' => __DIR__ . '/../view/auth/login.phtml',
            'auth/layout' => __DIR__ . '/../view/auth/layout.phtml',
        ],
        'template_path_stack' => [
            __DIR__ . '/../view/'
        ],
        'strategies' => [
            'ViewJsonStrategy',
        ],
    ],
    'lmc_rbac' => [
        'guards' => [
            \LmcRbacMvc\Guard\RoutePermissionsGuard::class => [
                'auth/*' => ['*'],
            ]
        ]
    ],
    'selfserve_logout_redirect_url' => 'http://gov.uk/done/vehicle-operator-licensing',
];
