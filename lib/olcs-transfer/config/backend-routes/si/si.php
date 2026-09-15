<?php

use Dvsa\Olcs\Transfer\Query;
use Dvsa\Olcs\Transfer\Router\QueryConfig;
use Dvsa\Olcs\Transfer\Router\RouteConfig;

return [
    'si' => RouteConfig::getRouteConfig(
        'si',
        [
            'si-category-type' => RouteConfig::getRouteConfig(
                'si-category-type',
                [
                    'list-data' => RouteConfig::getRouteConfig(
                        'list-data',
                        [
                            'GET' => QueryConfig::getConfig(Query\Si\SiCategoryTypeListData::class),
                        ]
                    ),
                ]
            ),
            'si-penalty-type' => RouteConfig::getRouteConfig(
                'si-penalty-type',
                [
                    'list-data' => RouteConfig::getRouteConfig(
                        'list-data',
                        [
                            'GET' => QueryConfig::getConfig(Query\Si\SiPenaltyTypeListData::class),
                        ]
                    ),
                ]
            ),
        ]
    ),
];
