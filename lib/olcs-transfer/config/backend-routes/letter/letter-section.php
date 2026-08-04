<?php

use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Transfer\Query;
use Dvsa\Olcs\Transfer\Router\CommandConfig;
use Dvsa\Olcs\Transfer\Router\QueryConfig;
use Dvsa\Olcs\Transfer\Router\RouteConfig;

return [
    'letter-section' => [
        'type' => 'Segment',
        'options' => [
            'route' => 'letter-section[/]',
        ],
        'may_terminate' => false,
        'child_routes' => [
            'single' => RouteConfig::getSingleConfig(
                [
                    'GET' => QueryConfig::getConfig(Query\Letter\LetterSection\Get::class),
                    'PUT' => CommandConfig::getPutConfig(Command\Letter\LetterSection\Update::class),
                    'DELETE' => CommandConfig::getDeleteConfig(Command\Letter\LetterSection\Delete::class),
                ]
            ),
            'GET' => QueryConfig::getConfig(Query\Letter\LetterSection\GetList::class),
            'POST' => CommandConfig::getPostConfig(Command\Letter\LetterSection\Create::class),
        ]
    ],
];