<?php

use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Transfer\Query;
use Dvsa\Olcs\Transfer\Router\CommandConfig;
use Dvsa\Olcs\Transfer\Router\QueryConfig;
use Dvsa\Olcs\Transfer\Router\RouteConfig;

return [
    'letter-section-variant' => [
        'type' => 'Segment',
        'options' => [
            'route' => 'letter-section-variant[/]',
        ],
        'may_terminate' => false,
        'child_routes' => [
            'single' => RouteConfig::getSingleConfig(
                [
                    'GET' => QueryConfig::getConfig(Query\Letter\LetterSectionVariant\Get::class),
                    'PUT' => CommandConfig::getPutConfig(Command\Letter\LetterSectionVariant\Update::class),
                    'DELETE' => CommandConfig::getDeleteConfig(Command\Letter\LetterSectionVariant\Delete::class),
                ]
            ),
            'POST' => CommandConfig::getPostConfig(Command\Letter\LetterSectionVariant\Create::class),
        ]
    ],
];
