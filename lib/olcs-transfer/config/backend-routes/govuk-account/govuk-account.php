<?php

use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Transfer\Router\CommandConfig;
use Dvsa\Olcs\Transfer\Router\RouteConfig;

return [
    'govuk-account' => RouteConfig::getRouteConfig(
        'govuk-account',
        [
            'process-auth-response' => RouteConfig::getRouteConfig(
                'process-auth-response',
                [
                    'POST' => CommandConfig::getPostConfig(Command\GovUkAccount\ProcessAuthResponse::class),
                ]
            ),
            'get-govuk-account-redirect' => RouteConfig::getRouteConfig(
                'get-govuk-account-redirect',
                [
                    'POST' => CommandConfig::getPostConfig(Command\GovUkAccount\GetGovUkAccountRedirect::class),
                ]
            ),
        ]
    )
];
