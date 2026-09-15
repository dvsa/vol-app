<?php

use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Transfer\Query;
use Dvsa\Olcs\Transfer\Router\CommandConfig;
use Dvsa\Olcs\Transfer\Router\QueryConfig;
use Dvsa\Olcs\Transfer\Router\RouteConfig;

return [
    'letter-choice' => [
        'type' => 'Segment',
        'options' => [
            'route' => 'letter-choice[/]',
        ],
        'may_terminate' => false,
        'child_routes' => [
            'single' => RouteConfig::getSingleConfig(
                [
                    'GET' => QueryConfig::getConfig(Query\Letter\LetterChoice\Get::class),
                    'PUT' => CommandConfig::getPutConfig(Command\Letter\LetterChoice\Update::class),
                    'DELETE' => CommandConfig::getDeleteConfig(Command\Letter\LetterChoice\Delete::class),
                ]
            ),
            'GET' => QueryConfig::getConfig(Query\Letter\LetterChoice\GetList::class),
            'POST' => CommandConfig::getPostConfig(Command\Letter\LetterChoice\Create::class),
        ]
    ],
];
