<?php

use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Transfer\Query;
use Dvsa\Olcs\Transfer\Router\CommandConfig;
use Dvsa\Olcs\Transfer\Router\QueryConfig;
use Dvsa\Olcs\Transfer\Router\RouteConfig;

return [
    'issue-type' => [
        'type' => 'Segment',
        'options' => [
            'route' => 'issue-type[/]',
        ],
        'may_terminate' => false,
        'child_routes' => [
            'GET' => QueryConfig::getConfig(Query\Letter\LetterIssueType\GetList::class),
            'POST' => CommandConfig::getPostConfig(Command\Letter\LetterIssueType\Create::class),
            'single' => RouteConfig::getSingleConfig(
                [
                    'GET' => QueryConfig::getConfig(Query\Letter\LetterIssueType\Get::class),
                    'PUT' => CommandConfig::getPutConfig(Command\Letter\LetterIssueType\Update::class),
                    'DELETE' => CommandConfig::getDeleteConfig(Command\Letter\LetterIssueType\Delete::class),
                ],
                '\d+'
            ),
        ]
    ],
];
