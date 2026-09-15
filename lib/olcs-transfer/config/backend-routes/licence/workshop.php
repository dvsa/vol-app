<?php

use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Transfer\Query;
use Dvsa\Olcs\Transfer\Router\CommandConfig;
use Dvsa\Olcs\Transfer\Router\QueryConfig;
use Dvsa\Olcs\Transfer\Router\RouteConfig;

return [
    'workshop' => RouteConfig::getRouteConfig(
        'workshop',
        [
            'DELETE' => CommandConfig::getDeleteConfig(Command\Workshop\DeleteWorkshop::class),
            'POST' => CommandConfig::getPostConfig(Command\Workshop\CreateWorkshop::class),
            'single' => RouteConfig::getSingleConfig(
                [
                    'GET' => QueryConfig::getConfig(Query\Workshop\Workshop::class),
                    'PUT' => CommandConfig::getPutConfig(Command\Workshop\UpdateWorkshop::class),
                ]
            )
        ]
    )
];
