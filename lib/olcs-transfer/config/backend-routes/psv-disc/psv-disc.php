<?php

use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Transfer\Router\CommandConfig;
use Dvsa\Olcs\Transfer\Router\QueryConfig;
use Dvsa\Olcs\Transfer\Router\RouteConfig;
use Dvsa\Olcs\Transfer\Query;

return [
    'psv-disc' => [
        'type' => 'Segment',
        'options' => [
            'route' => 'psv-disc/',
        ],
        'may_terminate' => false,
        'child_routes' => [
            'print-discs' => RouteConfig::getRouteConfig(
                'print-discs',
                [
                    'POST' =>
                        CommandConfig::getPostConfig(
                            Command\PsvDisc\PrintDiscs::class
                        )
                ]
            ),
            'confirm-printing' => RouteConfig::getRouteConfig(
                'confirm-printing',
                [
                    'POST' =>
                        CommandConfig::getPostConfig(
                            Command\PsvDisc\ConfirmPrinting::class
                        )
                ]
            ),
        ],
    ],
];
