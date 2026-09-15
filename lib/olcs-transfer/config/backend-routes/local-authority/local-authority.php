<?php

use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Transfer\Query;
use Dvsa\Olcs\Transfer\Router\CommandConfig;
use Dvsa\Olcs\Transfer\Router\QueryConfig;
use Dvsa\Olcs\Transfer\Router\RouteConfig;

return [
    'local-authority' => [
        'type' => 'Segment',
        'options' => [
            'route' => 'local-authority[/]',
        ],
        'may_terminate' => false,
        'child_routes' => [
            'single' => RouteConfig::getSingleConfig(
                [
                    'GET' => QueryConfig::getConfig(Query\LocalAuthority\ById::class),
                    'PUT' => CommandConfig::getPutConfig(Command\LocalAuthority\Update::class),
                ]
            ),
            'GET' => QueryConfig::getConfig(Query\LocalAuthority\LocalAuthorityList::class),
        ]
    ]
];
