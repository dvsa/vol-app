<?php

use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Transfer\Router\CommandConfig;
use Dvsa\Olcs\Transfer\Router\RouteConfig;

return [
    'letter-instance-section' => [
        'type' => 'Segment',
        'options' => [
            'route' => 'letter-instance-section[/]',
        ],
        'may_terminate' => false,
        'child_routes' => [
            'single' => RouteConfig::getSingleConfig(
                [
                    'PUT' => CommandConfig::getPutConfig(Command\Letter\LetterInstanceSection\UpdateContent::class),
                ]
            ),
        ]
    ],
];
