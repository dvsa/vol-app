<?php

use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Transfer\Query;
use Dvsa\Olcs\Transfer\Router\CommandConfig;
use Dvsa\Olcs\Transfer\Router\QueryConfig;
use Dvsa\Olcs\Transfer\Router\RouteConfig;

return [
    'ref-data' => RouteConfig::getRouteConfig(
        'ref-data',
        [
            'GET' => QueryConfig::getConfig(Query\RefData\RefDataList::class),
        ]
    )
];
