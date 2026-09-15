<?php

use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Transfer\Query;
use Dvsa\Olcs\Transfer\Router\CommandConfig;
use Dvsa\Olcs\Transfer\Router\QueryConfig;

return [
    'irhp-permit-jurisdiction' => [
        'type' => 'Segment',
        'options' => [
            'route' => 'irhp-permit-jurisdiction[/]',
        ],
        'may_terminate' => false,
        'child_routes' => [
            'GET' => QueryConfig::getConfig(Query\IrhpPermitJurisdiction\GetList::class),
            'PUT' => CommandConfig::getPutConfig(Command\IrhpPermitJurisdiction\Update::class),
        ]
    ],
];
