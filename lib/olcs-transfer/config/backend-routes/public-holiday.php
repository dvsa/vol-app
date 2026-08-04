<?php

use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Transfer\Query;
use Dvsa\Olcs\Transfer\Router\CommandConfig;
use Dvsa\Olcs\Transfer\Router\QueryConfig;
use Dvsa\Olcs\Transfer\Router\RouteConfig;
use Laminas\Router\Http\Segment;

return [
    'public-holiday' => [
        'type' => Segment::class,
        'options' => [
            'route' => 'public-holiday[/]',
        ],
        'may_terminate' => false,
        'child_routes' => [
            'single' => RouteConfig::getSingleConfig(
                [
                    'GET' => QueryConfig::getConfig(Query\System\PublicHoliday\Get::class),
                    'PUT' => CommandConfig::getPutConfig(Command\System\PublicHoliday\Update::class),
                    'DELETE' => CommandConfig::getDeleteConfig(Command\System\PublicHoliday\Delete::class),
                ]
            ),
            'GET' => QueryConfig::getConfig(Query\System\PublicHoliday\GetList::class),
            'POST' => CommandConfig::getPostConfig(Command\System\PublicHoliday\Create::class),
        ],
    ],
];
