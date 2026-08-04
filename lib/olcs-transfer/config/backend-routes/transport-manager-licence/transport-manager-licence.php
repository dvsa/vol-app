<?php

use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Transfer\Query;
use Dvsa\Olcs\Transfer\Router\CommandConfig;
use Dvsa\Olcs\Transfer\Router\QueryConfig;
use Dvsa\Olcs\Transfer\Router\RouteConfig;

return [
    'transport-manager-licence' => RouteConfig::getRouteConfig(
        'transport-manager-licence',
        [
            'GET' => QueryConfig::getConfig(Query\TransportManagerLicence\GetList::class),
            'DELETE' => CommandConfig::getDeleteConfig(Command\TransportManagerLicence\Delete::class),
            'variation' => RouteConfig::getRouteConfig(
                'variation',
                ['GET' => QueryConfig::getConfig(Query\TransportManagerLicence\GetListByVariation::class)]
            ),

        ]
    )
];
