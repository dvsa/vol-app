<?php

use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Transfer\Router\CommandConfig;
use Dvsa\Olcs\Transfer\Router\RouteConfig;

return [
    'translation-key-text' => [
        'type' => 'Segment',
        'options' => [
            'route' => 'translation-key-text[/]',
        ],
        'may_terminate' => false,
        'child_routes' => [
            'single' => RouteConfig::getSingleConfig(
                [
                    'DELETE' => CommandConfig::getDeleteConfig(Command\TranslationKeyText\Delete::class),
                ]
            ),
        ]
    ],
];
