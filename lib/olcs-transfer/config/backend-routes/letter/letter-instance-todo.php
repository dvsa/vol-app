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
                ]
            ),
        ]
    ],
];
