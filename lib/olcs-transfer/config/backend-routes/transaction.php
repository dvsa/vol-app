<?php

use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Transfer\Query;
use Dvsa\Olcs\Transfer\Router\CommandConfig;
use Dvsa\Olcs\Transfer\Router\QueryConfig;
use Dvsa\Olcs\Transfer\Router\RouteConfig;

return [
       'transaction' => [
        'type' => 'Segment',
        'options' => [
            'route' => 'transaction[/]',
        ],
        'may_terminate' => false,
        'child_routes' => [
            'by-reference' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'ref/:reference[/]',
                    'constraints' => [
                        'reference' => '[0-9A-Za-z]+-[0-9A-F\-]+',
                    ],
                ],
                'child_routes' => [
                    'GET' => QueryConfig::getConfig(Query\Transaction\TransactionByReference::class),
                    'POST' => CommandConfig::getPostConfig(Command\Transaction\CompleteTransaction::class),
                ],
            ],
            'single' => RouteConfig::getSingleConfig(
                [
                    'GET' => QueryConfig::getConfig(Query\Transaction\Transaction::class),
                    'reverse' => RouteConfig::getRouteConfig(
                        'reverse',
                        [
                            'PUT' => CommandConfig::getPutConfig(Command\Transaction\ReverseTransaction::class),
                        ]
                    )
                ]
            ),
            'pay-outstanding-fees' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'pay-outstanding-fees[/]',
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'POST' => CommandConfig::getPostConfig(Command\Transaction\PayOutstandingFees::class),
                ],
            ],
        ]
    ],
];
