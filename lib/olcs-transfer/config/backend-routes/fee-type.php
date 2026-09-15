<?php

use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Transfer\Query;
use Dvsa\Olcs\Transfer\Router\CommandConfig;
use Dvsa\Olcs\Transfer\Router\QueryConfig;
use Dvsa\Olcs\Transfer\Router\RouteConfig;

return [
    'fee-type' => [
        'type' => 'Segment',
        'options' => [
            'route' => 'fee-type[/]',
        ],
        'may_terminate' => false,
        'child_routes' => [
            'single' => RouteConfig::getSingleConfig(
                [
                    'GET' => QueryConfig::getConfig(Query\Fee\FeeType::class),
                    'PUT' => CommandConfig::getPutConfig(Command\FeeType\Update::class)
                ]
            ),
            'latest' => RouteConfig::getRouteConfig(
                'latest',
                [
                    'GET' => QueryConfig::getConfig(Query\Fee\GetLatestFeeType::class),
                ]
            ),
            'fee-rates' => RouteConfig::getRouteConfig(
                'fee-rates',
                [
                    'GET' => QueryConfig::getConfig(Query\FeeType\GetList::class),
                ]
            ),
            'fee-types-distinct' => RouteConfig::getRouteConfig(
                'fee-types-distinct',
                [
                    'GET' => QueryConfig::getConfig(Query\FeeType\GetDistinctList::class),
                ]
            ),
            'GET' => QueryConfig::getConfig(Query\Fee\FeeTypeList::class),
        ]
    ],
];
