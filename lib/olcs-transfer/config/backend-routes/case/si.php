<?php

use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Transfer\Query;
use Dvsa\Olcs\Transfer\Router\CommandConfig;
use Dvsa\Olcs\Transfer\Router\QueryConfig;
use Dvsa\Olcs\Transfer\Router\RouteConfig;

return [
    'case-si' => RouteConfig::getRouteConfig(
        'case/si',
        [
            'create-response' => RouteConfig::getRouteConfig(
                'create-response',
                [
                    'POST' => CommandConfig::getPostConfig(Command\Cases\Si\CreateResponse::class)
                ]
            ),
            'single' => RouteConfig::getSingleConfig(
                [
                    'GET' => QueryConfig::getConfig(Query\Cases\Si\Si::class),
                    'PUT' => CommandConfig::getPutConfig(Command\Cases\Si\UpdateSi::class),
                    'DELETE' => CommandConfig::getDeleteConfig(Command\Cases\Si\DeleteSi::class),
                ],
                '[0-9]+'
            ),
            'GET' => QueryConfig::getConfig(Query\Cases\Si\SiList::class),
            'POST' => CommandConfig::getPostConfig(Command\Cases\Si\CreateSi::class),
        ]
    )
];
