<?php

use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Transfer\Query;
use Dvsa\Olcs\Transfer\Router\CommandConfig;
use Dvsa\Olcs\Transfer\Router\QueryConfig;

return [
    'irhp-permit-sector' => [
        'type' => 'Segment',
        'options' => [
            'route' => 'irhp-permit-sector[/]',
        ],
        'may_terminate' => false,
        'child_routes' => [
            'GET' => QueryConfig::getConfig(Query\IrhpPermitSector\GetList::class),
            'PUT' => CommandConfig::getPutConfig(Command\IrhpPermitSector\Update::class),
        ]
    ],
];
