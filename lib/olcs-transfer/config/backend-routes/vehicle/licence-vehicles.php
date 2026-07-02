<?php

use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Transfer\Query;
use Dvsa\Olcs\Transfer\Router\CommandConfig;
use Dvsa\Olcs\Transfer\Router\QueryConfig;
use Dvsa\Olcs\Transfer\Router\RouteConfig;

return [
    'licence-vehicle' => RouteConfig::getRouteConfig(
        'licence-vehicle',
        [
            'GET' => QueryConfig::getConfig(Query\LicenceVehicle\LicenceVehiclesById::class),
            'single' => RouteConfig::getSingleConfig(
                [
                    'GET' => QueryConfig::getConfig(Query\LicenceVehicle\LicenceVehicle::class),
                    'PUT' => CommandConfig::getPutConfig(Command\Vehicle\UpdateGoodsVehicle::class),
                    'psv' => RouteConfig::getRouteConfig(
                        'psv',
                        [
                            'GET' => QueryConfig::getConfig(Query\LicenceVehicle\PsvLicenceVehicle::class),
                            'PUT' => CommandConfig::getPutConfig(Command\LicenceVehicle\UpdatePsvLicenceVehicle::class),
                        ]
                    )
                ]
            ),
            'DELETE' => CommandConfig::getDeleteConfig(Command\Vehicle\DeleteLicenceVehicle::class)
        ]
    )
];
