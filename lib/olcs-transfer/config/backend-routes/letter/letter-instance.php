<?php

use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Transfer\Query;
use Dvsa\Olcs\Transfer\Router\CommandConfig;
use Dvsa\Olcs\Transfer\Router\QueryConfig;
use Dvsa\Olcs\Transfer\Router\RouteConfig;

return [
    'letter-instance' => [
        'type' => 'Segment',
        'options' => [
            'route' => 'letter-instance[/]',
        ],
        'may_terminate' => false,
        'child_routes' => [
            'single' => RouteConfig::getSingleConfig(
                [
                    'GET' => QueryConfig::getConfig(Query\Letter\LetterInstance\Get::class),
                ]
            ),
            'GET' => QueryConfig::getConfig(Query\Letter\LetterInstance\GetList::class),
            'generate' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'generate',
                    'defaults' => [
                        'controller' => 'Api\Generic'
                    ]
                ],
                'child_routes' => [
                    'POST' => CommandConfig::getPostConfig(Command\Letter\LetterInstance\Generate::class),
                ]
            ],
            'preview' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'preview',
                    'defaults' => [
                        'controller' => 'Api\Generic'
                    ]
                ],
                'child_routes' => [
                    'GET' => QueryConfig::getConfig(Query\Letter\LetterInstance\Preview::class),
                    'POST' => CommandConfig::getPostConfig(Command\Letter\LetterInstance\Preview::class),
                ]
            ],
            'prepare-to-send' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'prepare-to-send',
                    'defaults' => [
                        'controller' => 'Api\Generic'
                    ]
                ],
                'child_routes' => [
                    'POST' => CommandConfig::getPostConfig(Command\Letter\LetterInstance\PrepareToSend::class),
                ]
            ]
        ]
    ],
];