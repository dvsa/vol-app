<?php

use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Transfer\Query;
use Dvsa\Olcs\Transfer\Router\CommandConfig;
use Dvsa\Olcs\Transfer\Router\QueryConfig;
use Dvsa\Olcs\Transfer\Router\RouteConfig;

return [
    'letter-issue' => [
        'type' => 'Segment',
        'options' => [
            'route' => 'letter-issue[/]',
        ],
        'may_terminate' => false,
        'child_routes' => [
            'single' => RouteConfig::getSingleConfig(
                [
                    'GET' => QueryConfig::getConfig(Query\Letter\LetterIssue\Get::class),
                    'PUT' => CommandConfig::getPutConfig(Command\Letter\LetterIssue\Update::class),
                    'DELETE' => CommandConfig::getDeleteConfig(Command\Letter\LetterIssue\Delete::class),
                ]
            ),
            'GET' => QueryConfig::getConfig(Query\Letter\LetterIssue\GetList::class),
            'POST' => CommandConfig::getPostConfig(Command\Letter\LetterIssue\Create::class),
        ]
    ],
];
