<?php

use Dvsa\Olcs\Transfer\Query;
use Dvsa\Olcs\Transfer\Router\QueryConfig;
use Dvsa\Olcs\Transfer\Router\RouteConfig;
use Dvsa\Olcs\Transfer\Router\CommandConfig;
use Dvsa\Olcs\Transfer\Command\DataRetention;

return [
    'data-retention' => RouteConfig::getRouteConfig(
        'data-retention',
        [
            'GET' => QueryConfig::getConfig(Query\DataRetention\RuleList::class),
            'rule-list' => RouteConfig::getRouteConfig(
                'rule-list',
                [
                    'single' => RouteConfig::getSingleConfig(
                        [
                            'GET' => QueryConfig::getConfig(Query\DataRetention\GetRule::class),
                        ]
                    ),
                    'GET' => QueryConfig::getConfig(Query\DataRetention\RuleList::class),
                ]
            ),
            'rule-admin' => RouteConfig::getRouteConfig(
                'rule-admin',
                [
                    'GET' => QueryConfig::getConfig(Query\DataRetention\RuleAdmin::class),
                ]
            ),
            'update-rule' => RouteConfig::getRouteConfig(
                'update-rule',
                [
                    'POST' => CommandConfig::getPostConfig(DataRetention\UpdateRule::class),
                ]
            ),
            'records' => RouteConfig::getRouteConfig(
                'records',
                [
                    'GET' => QueryConfig::getConfig(Query\DataRetention\Records::class),
                ]
            ),
            'mark-for-delete' => RouteConfig::getRouteConfig(
                'mark-for-delete',
                [
                    'POST' => CommandConfig::getPostConfig(DataRetention\MarkForDelete::class),
                ]
            ),
            'mark-for-review' => RouteConfig::getRouteConfig(
                'mark-for-review',
                [
                    'POST' => CommandConfig::getPostConfig(DataRetention\MarkForReview::class),
                ]
            ),
            'delay-items' => RouteConfig::getRouteConfig(
                'delay-items',
                [
                    'POST' => CommandConfig::getPostConfig(DataRetention\DelayItems::class),
                ]
            ),
            'assign-items' => RouteConfig::getRouteConfig(
                'assign-items',
                [
                    'POST' => CommandConfig::getPostConfig(DataRetention\AssignItems::class),
                ]
            ),
            'processed-list' => RouteConfig::getRouteConfig(
                'processed-list',
                [
                    'GET' => QueryConfig::getConfig(Query\DataRetention\GetProcessedList::class),
                ]
            ),
        ]
    ),
];
