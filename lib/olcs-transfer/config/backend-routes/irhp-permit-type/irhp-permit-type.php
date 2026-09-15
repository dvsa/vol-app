<?php

use Dvsa\Olcs\Transfer\Query;
use Dvsa\Olcs\Transfer\Router\QueryConfig;
use Dvsa\Olcs\Transfer\Router\RouteConfig;

return [
    'irhp-permit-type' => [
        'type' => 'Segment',
        'options' => [
            'route' => 'irhp-permit-type[/]',
        ],
        'may_terminate' => false,
        'child_routes' => [
            'single' => RouteConfig::getSingleConfig(
                [
                    'GET' => QueryConfig::getConfig(Query\IrhpPermitType\ById::class),
                ]
            ),
            'GET' => QueryConfig::getConfig(Query\IrhpPermitType\GetList::class),
        ]
    ],
];
