<?php

use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Transfer\Query;
use Dvsa\Olcs\Transfer\Router\CommandConfig;
use Dvsa\Olcs\Transfer\Router\QueryConfig;
use Dvsa\Olcs\Transfer\Router\RouteConfig;

return [
    'fee' => [
        'type' => 'Segment',
        'options' => [
            'route' => 'fee/',
        ],
        'may_terminate' => false,
        'child_routes' => [
            'single' => RouteConfig::getSingleConfig(
                [
                    'GET' => QueryConfig::getConfig(Query\Fee\Fee::class),
                    'PUT' => CommandConfig::getPutConfig(Command\Fee\UpdateFee::class),
                    'approve-waive' => RouteConfig::getRouteConfig(
                        'approve-waive',
                        [
                            'PUT' => CommandConfig::getPutConfig(Command\Fee\ApproveWaive::class),
                        ]
                    ),
                    'recommend-waive' => RouteConfig::getRouteConfig(
                        'recommend-waive',
                        [
                            'PUT' => CommandConfig::getPutConfig(Command\Fee\RecommendWaive::class),
                        ]
                    ),
                    'reject-waive' => RouteConfig::getRouteConfig(
                        'reject-waive',
                        [
                            'PUT' => CommandConfig::getPutConfig(Command\Fee\RejectWaive::class),
                        ]
                    ),
                    'refund-fee' => RouteConfig::getRouteConfig(
                        'refund-fee',
                        [
                            'PUT' => CommandConfig::getPutConfig(Command\Fee\RefundFee::class),
                        ]
                    ),
                ]
            ),
            'GET' => QueryConfig::getConfig(Query\Fee\FeeList::class),
            'POST' => CommandConfig::getPostConfig(Command\Fee\CreateFee::class),
            'interim-refunds' => RouteConfig::getRouteConfig(
                'interim-refunds',
                [
                    'GET' => QueryConfig::getConfig(Query\Fee\InterimRefunds::class)
                ]
            ),
        ]
    ],
];
