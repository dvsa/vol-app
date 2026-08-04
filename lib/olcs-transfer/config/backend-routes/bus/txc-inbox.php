<?php

use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Transfer\Query;
use Dvsa\Olcs\Transfer\Router\CommandConfig;
use Dvsa\Olcs\Transfer\Router\QueryConfig;
use Dvsa\Olcs\Transfer\Router\RouteConfig;

return [
    'txc-inbox' => [
        'type' => 'Segment',
        'options' => [
            'route' => 'txc-inbox[/]',
        ],
        'may_terminate' => false,
        'child_routes' => [
            'GET' => QueryConfig::getConfig(Query\Bus\Ebsr\TxcInboxList::class),
            'PUT' => CommandConfig::getPutConfig(Command\Bus\Ebsr\UpdateTxcInbox::class),
        ]
    ]
];
