<?php

use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Transfer\Router\CommandConfig;
use Dvsa\Olcs\Transfer\Router\RouteConfig;

return [
    'auth' => RouteConfig::getRouteConfig(
        'auth',
        [
            'login' => RouteConfig::getRouteConfig(
                'login',
                [
                    'POST' => CommandConfig::getPostConfig(Command\Auth\Login::class),
                ]
            ),
            'refresh-token' => RouteConfig::getRouteConfig(
                'refresh-token',
                [
                    'POST' => CommandConfig::getPostConfig(Command\Auth\RefreshTokens::class)
                ]
            ),
            'change-expired-password' => RouteConfig::getRouteConfig(
                'change-expired-password',
                [
                    'POST' => CommandConfig::getPostConfig(Command\Auth\ChangeExpiredPassword::class),
                ]
            )
        ]
    )
];
