<?php

use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Transfer\Query;
use Dvsa\Olcs\Transfer\Router\CommandConfig;
use Dvsa\Olcs\Transfer\Router\QueryConfig;
use Dvsa\Olcs\Transfer\Router\RouteConfig;

return [
    'subcategory' => RouteConfig::getRouteConfig(
        'subcategory',
        [
            'GET' => QueryConfig::getConfig(Query\SubCategory\GetList::class),
        ]
    )
];
