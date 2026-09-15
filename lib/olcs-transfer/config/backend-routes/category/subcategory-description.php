<?php

use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Transfer\Query;
use Dvsa\Olcs\Transfer\Router\CommandConfig;
use Dvsa\Olcs\Transfer\Router\QueryConfig;
use Dvsa\Olcs\Transfer\Router\RouteConfig;

return [
    'subcategory-description' => RouteConfig::getRouteConfig(
        'subcategory-description',
        [
            'GET' => QueryConfig::getConfig(Query\SubCategoryDescription\GetList::class),
        ]
    )
];
