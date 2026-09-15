<?php

use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Transfer\Query;
use Dvsa\Olcs\Transfer\Router\CommandConfig;
use Dvsa\Olcs\Transfer\Router\QueryConfig;
use Dvsa\Olcs\Transfer\Router\RouteConfig;

return [
    'printer-exception' => [
        'type' => 'Segment',
        'options' => [
            'route' => 'printer-exception[/]',
        ],
        'may_terminate' => false,
        'child_routes' => [
            'single' => RouteConfig::getSingleConfig(
                [
                    'GET' => QueryConfig::getConfig(Query\TeamPrinter\TeamPrinter::class),
                    'PUT' => CommandConfig::getPutConfig(Command\TeamPrinter\UpdateTeamPrinter::class),
                    'DELETE' => CommandConfig::getDeleteConfig(Command\TeamPrinter\DeleteTeamPrinter::class),
                ]
            ),
            'GET' => QueryConfig::getConfig(Query\TeamPrinter\TeamPrinterExceptionsList::class),
            'POST' => CommandConfig::getPostConfig(Command\TeamPrinter\CreateTeamPrinter::class),
        ]
    ],
];
