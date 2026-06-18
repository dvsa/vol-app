<?php

use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Transfer\Router\CommandConfig;
use Dvsa\Olcs\Transfer\Router\QueryConfig;
use Dvsa\Olcs\Transfer\Router\RouteConfig;
use Dvsa\Olcs\Transfer\Query;

return [
    'goods-disc' => [
        'type' => 'Segment',
        'options' => [
            'route' => 'goods-disc/',
        ],
        'may_terminate' => false,
        'child_routes' => [
            'print-discs' => RouteConfig::getRouteConfig(
                'print-discs',
                [
                    'POST' =>
                        CommandConfig::getPostConfig(
                            Command\GoodsDisc\PrintDiscs::class
                        )
                ]
            ),
            'confirm-printing' => RouteConfig::getRouteConfig(
                'confirm-printing',
                [
                    'POST' =>
                        CommandConfig::getPostConfig(
                            Command\GoodsDisc\ConfirmPrinting::class
                        )
                ]
            ),
        ],
    ],
];
