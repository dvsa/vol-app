<?php

use Dvsa\Olcs\Transfer\Command\Auth\ChangeExpiredPassword;
use Dvsa\Olcs\Transfer\Command\Auth\ChangePassword;
use Dvsa\Olcs\Transfer\Command\Auth\ForgotPassword;
use Dvsa\Olcs\Transfer\Command\Auth\Login;
use Dvsa\Olcs\Transfer\Command\Auth\RefreshTokens;
use Dvsa\Olcs\Transfer\Command\Auth\ResetPassword;
use Dvsa\Olcs\Transfer\Router\CommandConfig;
use Dvsa\Olcs\Transfer\Router\RouteConfig;

return [
    'auth' => [
        'type' => 'Segment',
        'options' => [
            'route' => 'auth[/]',
        ],
        'may_terminate' => false,
        'child_routes' => [
            'login' => RouteConfig::getRouteConfig(
                'login[/]',
                [
                    'POST' => CommandConfig::getPostConfig(Login::class)
                ]
            ),
            'change-password' => RouteConfig::getRouteConfig(
                'change-password',
                [
                    'POST' => CommandConfig::getPostConfig(ChangePassword::class)
                ]
            ),
            'reset-password' => RouteConfig::getRouteConfig(
                'reset-password',
                [
                    'POST' => CommandConfig::getPostConfig(ResetPassword::class)
                ]
            ),
            'forgot-password' => RouteConfig::getRouteConfig(
                'forgot-password',
                [
                    'POST' => CommandConfig::getPostConfig(ForgotPassword::class)
                ]
            ),
            'refresh-tokens' => RouteConfig::getRouteConfig(
                'refresh-tokens',
                [
                    'POST' => CommandConfig::getPostConfig(RefreshTokens::class)
                ]
            ),
            'change-expired-password' => RouteConfig::getRouteConfig(
                'change-expired-password',
                [
                    'POST' => CommandConfig::getPostConfig(ChangeExpiredPassword::class)
                ]
            ),
        ]
    ],
];
