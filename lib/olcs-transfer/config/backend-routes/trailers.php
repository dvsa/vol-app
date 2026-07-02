<?php

use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Transfer\Query;
use Dvsa\Olcs\Transfer\Router\CommandConfig;
use Dvsa\Olcs\Transfer\Router\QueryConfig;
use Dvsa\Olcs\Transfer\Router\RouteConfig;

return [
    'trailers' => [
        'type' => 'Segment',
        'options' => [
            'route' => 'trailers[/]',
        ],
        'may_terminate' => false,
        'child_routes' => [
            'single' => RouteConfig::getSingleConfig(
                [
                    'GET' => QueryConfig::getConfig(Query\Trailer\Trailer::class),
                    'PUT' => CommandConfig::getPutConfig(Command\Trailer\UpdateTrailer::class),
                ]
            ),
            'POST' => CommandConfig::getPostConfig(Command\Trailer\CreateTrailer::class),
            'DELETE' => CommandConfig::getDeleteConfig(Command\Trailer\DeleteTrailer::class),
        ]
    ]
];
