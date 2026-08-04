<?php

use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Transfer\Query;
use Dvsa\Olcs\Transfer\Router\CommandConfig;
use Dvsa\Olcs\Transfer\Router\QueryConfig;
use Dvsa\Olcs\Transfer\Router\RouteConfig;

return [
    'printer' => [
        'type' => 'Segment',
        'options' => [
            'route' => 'printer[/]',
        ],
        'may_terminate' => false,
        'child_routes' => [
            'single' => RouteConfig::getSingleConfig(
                [
                    'GET' => QueryConfig::getConfig(Query\Printer\Printer::class),
                    'PUT' => CommandConfig::getPutConfig(Command\Printer\UpdatePrinter::class),
                    'DELETE' => CommandConfig::getDeleteConfig(Command\Printer\DeletePrinter::class),
                ]
            ),
            'GET' => QueryConfig::getConfig(Query\Printer\PrinterList::class),
            'POST' => CommandConfig::getPostConfig(Command\Printer\CreatePrinter::class),
        ]
    ],
];
