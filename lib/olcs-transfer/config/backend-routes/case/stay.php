<?php

use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Transfer\Query;
use Dvsa\Olcs\Transfer\Router\CommandConfig;
use Dvsa\Olcs\Transfer\Router\QueryConfig;
use Dvsa\Olcs\Transfer\Router\RouteConfig;

return [
    'stay' => RouteConfig::getRouteConfig(
        'stay',
        [
            'GET' => QueryConfig::getConfig(
                Query\Cases\Hearing\StayList::class
            ),
            'single' => RouteConfig::getSingleConfig(
                [
                    'GET'    => QueryConfig::getConfig(
                        Query\Cases\Hearing\Stay::class
                    ),
                    'PUT' => CommandConfig::getPutConfig(
                        Command\Cases\Hearing\UpdateStay::class
                    ),
                ]
            ),
            'POST' => CommandConfig::getPostConfig(
                Command\Cases\Hearing\CreateStay::class
            )
        ]
    )
];
