<?php

use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Transfer\Router\CommandConfig;
use Dvsa\Olcs\Transfer\Router\RouteConfig;

return [
    'letter-instance-todo' => [
        'type' => 'Segment',
        'options' => [
            'route' => 'letter-instance-todo[/]',
        ],
        'may_terminate' => false,
        'child_routes' => [
            'single' => RouteConfig::getSingleConfig(
                [
                    'PUT' => CommandConfig::getPutConfig(Command\Letter\LetterInstanceTodo\UpdateContent::class),
                    'DELETE' => CommandConfig::getDeleteConfig(Command\Letter\LetterInstanceTodo\DeleteCustom::class),
                ]
            ),
            // a caseworker-written to-do on an Other issue (VOL-7735)
            'create-custom' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'create-custom',
                    'defaults' => [
                        'controller' => 'Api\Generic'
                    ]
                ],
                'child_routes' => [
                    'POST' => CommandConfig::getPostConfig(Command\Letter\LetterInstanceTodo\CreateCustom::class),
                ]
            ],
        ]
    ],
];
