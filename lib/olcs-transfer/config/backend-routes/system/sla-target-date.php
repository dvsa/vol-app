<?php

use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Transfer\Query;
use Dvsa\Olcs\Transfer\Router\CommandConfig;
use Dvsa\Olcs\Transfer\Router\QueryConfig;
use Dvsa\Olcs\Transfer\Router\RouteConfig;

return [
    'sla-target-date' => [
        'type' => 'Segment',
        'options' => [
            'route' => 'sla-target-date[/]',
        ],
        'may_terminate' => false,
        'child_routes' => [
            'POST' => CommandConfig::getPostConfig(Command\System\CreateSlaTargetDate::class),
            'GET' => QueryConfig::getConfig(Query\System\SlaTargetDate::class),
            'PUT' => CommandConfig::getPutConfig(
                Command\System\UpdateSlaTargetDate::class
            )
        ]
    ]
];
