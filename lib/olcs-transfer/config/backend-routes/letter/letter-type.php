<?php

use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Transfer\Query;
use Dvsa\Olcs\Transfer\Router\CommandConfig;
use Dvsa\Olcs\Transfer\Router\QueryConfig;
use Dvsa\Olcs\Transfer\Router\RouteConfig;

return [
    'letter-type' => [
        'type' => 'Segment',
        'options' => [
            'route' => 'letter-type[/]',
        ],
        'may_terminate' => false,
        'child_routes' => [
            'single' => RouteConfig::getSingleConfig(
                [
                    'GET' => QueryConfig::getConfig(Query\Letter\LetterType\Get::class),
                    'PUT' => CommandConfig::getPutConfig(Command\Letter\LetterType\Update::class),
                    'DELETE' => CommandConfig::getDeleteConfig(Command\Letter\LetterType\Delete::class),
                ]
            ),
            'GET' => QueryConfig::getConfig(Query\Letter\LetterType\GetList::class),
            'POST' => CommandConfig::getPostConfig(Command\Letter\LetterType\Create::class),
            'clone' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'clone',
                    'defaults' => [
                        'controller' => 'Api\Generic'
                    ]
                ],
                'child_routes' => [
                    'POST' => CommandConfig::getPostConfig(Command\Letter\LetterType\Clone::class),
                ]
            ]
        ]
    ],
];