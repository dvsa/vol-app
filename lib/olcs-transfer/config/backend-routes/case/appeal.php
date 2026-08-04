<?php

use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Transfer\Query;
use Dvsa\Olcs\Transfer\Router\CommandConfig;
use Dvsa\Olcs\Transfer\Router\QueryConfig;
use Dvsa\Olcs\Transfer\Router\RouteConfig;

return [
    'appeal' => RouteConfig::getRouteConfig(
        'appeal',
        [
            'case' => RouteConfig::getRouteConfig(
                'case',
                [
                    'named-single' => RouteConfig::getNamedSingleConfig(
                        'case',
                        [
                            'GET'    => QueryConfig::getConfig(Query\Cases\Hearing\AppealByCase::class),
                        ]
                    )
                ]
            ),
            'single' => RouteConfig::getSingleConfig(
                [
                    'GET'    => QueryConfig::getConfig(
                        Query\Cases\Hearing\Appeal::class
                    ),
                    'PUT' => CommandConfig::getPutConfig(
                        Command\Cases\Hearing\UpdateAppeal::class
                    ),
                ]
            ),
            'POST' => CommandConfig::getPostConfig(
                Command\Cases\Hearing\CreateAppeal::class
            )
        ]
    )
];
