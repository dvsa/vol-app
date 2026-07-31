<?php

use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Transfer\Query;
use Dvsa\Olcs\Transfer\Router\CommandConfig;
use Dvsa\Olcs\Transfer\Router\QueryConfig;
use Dvsa\Olcs\Transfer\Router\RouteConfig;

return [
    'retrieval-link' => RouteConfig::getRouteConfig(
        'retrieval-link',
        [
            'resolve' => RouteConfig::getRouteConfig(
                'resolve',
                [
                    'GET' => QueryConfig::getConfig(Query\RetrievalLink\Resolve::class),
                ]
            ),
            'download' => RouteConfig::getRouteConfig(
                'download',
                [
                    'GET' => QueryConfig::getConfig(Query\RetrievalLink\Download::class),
                ]
            ),
            'request-otp' => RouteConfig::getRouteConfig(
                'request-otp',
                [
                    'POST' => CommandConfig::getPostConfig(Command\RetrievalLink\RequestOtp::class),
                ]
            ),
            'verify-otp' => RouteConfig::getRouteConfig(
                'verify-otp',
                [
                    'POST' => CommandConfig::getPostConfig(Command\RetrievalLink\VerifyOtp::class),
                ]
            ),
        ]
    )
];
