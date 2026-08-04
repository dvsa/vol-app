<?php

use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Transfer\Query;
use Dvsa\Olcs\Transfer\Router\CommandConfig;
use Dvsa\Olcs\Transfer\Router\QueryConfig;
use Dvsa\Olcs\Transfer\Router\RouteConfig;

return [
    'letter-todo' => [
        'type' => 'Segment',
        'options' => [
            'route' => 'letter-todo[/]',
        ],
        'may_terminate' => false,
        'child_routes' => [
            'single' => RouteConfig::getSingleConfig(
                [
                    'GET' => QueryConfig::getConfig(Query\Letter\LetterTodo\Get::class),
                    'PUT' => CommandConfig::getPutConfig(Command\Letter\LetterTodo\Update::class),
                    'DELETE' => CommandConfig::getDeleteConfig(Command\Letter\LetterTodo\Delete::class),
                ]
            ),
            'GET' => QueryConfig::getConfig(Query\Letter\LetterTodo\GetList::class),
            'POST' => CommandConfig::getPostConfig(Command\Letter\LetterTodo\Create::class),
        ]
    ],
];
