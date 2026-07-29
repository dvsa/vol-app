<?php

use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Transfer\Router\CommandConfig;

return [
    'cache-clear' => [
        'type' => 'Segment',
        'options' => [
            'route' => 'cache-clear[/]',
        ],
        'may_terminate' => false,
        'child_routes' => [
            'POST' => CommandConfig::getPostConfig(
                Command\Cache\Clear::class
            ),
        ],
    ],
];
