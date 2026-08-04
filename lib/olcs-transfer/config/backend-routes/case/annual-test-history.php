<?php

use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Transfer\Query;
use Dvsa\Olcs\Transfer\Router\CommandConfig;
use Dvsa\Olcs\Transfer\Router\QueryConfig;
use Dvsa\Olcs\Transfer\Router\RouteConfig;

return [
    'annual-test-history' => [
        'type' => 'Segment',
        'options' => [
            'route' => 'cases/:case/annual-test-history[/]',
        ],
        'may_terminate' => false,
        'child_routes' => [
            'single' => RouteConfig::getSingleConfig(
                [
                    'GET' => QueryConfig::getConfig(Query\Cases\AnnualTestHistory::class),
                    'PUT' => CommandConfig::getPutConfig(
                        Command\Cases\UpdateAnnualTestHistory::class
                    ),
                ]
            ),
        ]
    ]
];