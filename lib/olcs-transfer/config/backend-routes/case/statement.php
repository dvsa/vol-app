<?php

use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Transfer\Query;
use Dvsa\Olcs\Transfer\Router\CommandConfig;
use Dvsa\Olcs\Transfer\Router\QueryConfig;
use Dvsa\Olcs\Transfer\Router\RouteConfig;

return [
    'statement' => [
        'type' => 'Segment',
        'options' => [
            'route' => 'statement[/]',
        ],
        'may_terminate' => false,
        'child_routes' => [
            'GET' => QueryConfig::getConfig(Query\Cases\Statement\StatementList::class),
            'single' => RouteConfig::getSingleConfig(
                [
                    'GET' => QueryConfig::getConfig(
                        Query\Cases\Statement\Statement::class
                    ),
                    'PUT' => CommandConfig::getPutConfig(
                        Command\Cases\Statement\UpdateStatement::class
                    ),
                    'DELETE' => CommandConfig::getDeleteConfig(
                        Command\Cases\Statement\DeleteStatement::class
                    )
                ]
            ),
            'POST' => CommandConfig::getPostConfig(
                Command\Cases\Statement\CreateStatement::class
            )
        ]
    ]
];
