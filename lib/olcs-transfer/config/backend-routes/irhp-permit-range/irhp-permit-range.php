<?php

use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Transfer\Query;
use Dvsa\Olcs\Transfer\Router\CommandConfig;
use Dvsa\Olcs\Transfer\Router\QueryConfig;
use Dvsa\Olcs\Transfer\Router\RouteConfig;

return [
    'irhp-permit-range' => [
        'type' => 'Segment',
        'options' => [
            'route' => 'irhp-permit-range[/]',
        ],
        'may_terminate' => false,
        'child_routes' => [
            'single' => RouteConfig::getSingleConfig(
                [
                    'GET' => QueryConfig::getConfig(Query\IrhpPermitRange\ById::class),
                    'PUT' => CommandConfig::getPutConfig(Command\IrhpPermitRange\Update::class),
                    'DELETE' => CommandConfig::getDeleteConfig(Command\IrhpPermitRange\Delete::class),
                ]
            ),
            'GET' => QueryConfig::getConfig(Query\IrhpPermitRange\GetList::class),
            'POST' => CommandConfig::getPostConfig(Command\IrhpPermitRange\Create::class),
        ]
    ],
];
