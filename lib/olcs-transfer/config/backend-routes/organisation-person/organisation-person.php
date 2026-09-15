<?php

use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Transfer\Query;
use Dvsa\Olcs\Transfer\Router\CommandConfig;
use Dvsa\Olcs\Transfer\Router\QueryConfig;
use Dvsa\Olcs\Transfer\Router\RouteConfig;

return [
    'organisation-person' => RouteConfig::getRouteConfig(
        'organisation-person',
        [
            'single' => RouteConfig::getSingleConfig(
                [
                    'GET' => QueryConfig::getConfig(Query\OrganisationPerson\GetSingle::class),
                    'PUT' => CommandConfig::getPutConfig(Command\OrganisationPerson\Update::class),
                ]
            ),
            'POST' => CommandConfig::getPostConfig(Command\OrganisationPerson\Create::class),
            'DELETE' => CommandConfig::getDeleteConfig(Command\OrganisationPerson\DeleteList::class)
        ]
    )
];
