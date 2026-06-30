<?php

use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Transfer\Query;
use Dvsa\Olcs\Transfer\Router\CommandConfig;
use Dvsa\Olcs\Transfer\Router\QueryConfig;
use Dvsa\Olcs\Transfer\Router\RouteConfig;

return [
    'financial-standing-rate' => RouteConfig::getRouteConfig(
        'financial-standing-rate',
        [
            'GET' => QueryConfig::getConfig(Query\System\FinancialStandingRateList::class),
            'POST' => CommandConfig::getPostConfig(Command\System\CreateFinancialStandingRate::class),
            'DELETE' => CommandConfig::getDeleteConfig(Command\System\DeleteFinancialStandingRateList::class),
            'single' => RouteConfig::getSingleConfig(
                [
                    'GET' => QueryConfig::getConfig(Query\System\FinancialStandingRate::class),
                    'PUT' => CommandConfig::getPutConfig(Command\System\UpdateFinancialStandingRate::class)
                ]
            ),
        ]
    )
];
