<?php

use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Transfer\Query;
use Dvsa\Olcs\Transfer\Router\CommandConfig;
use Dvsa\Olcs\Transfer\Router\QueryConfig;
use Dvsa\Olcs\Transfer\Router\RouteConfig;

return [
    'category' => RouteConfig::getRouteConfig(
        'category',
        [
            'GET' => QueryConfig::getConfig(Query\Category\GetList::class),
        ]
    )
];
