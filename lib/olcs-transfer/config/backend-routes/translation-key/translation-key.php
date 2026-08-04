<?php

use Dvsa\Olcs\Transfer\Query;
use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Transfer\Router\CommandConfig;
use Dvsa\Olcs\Transfer\Router\QueryConfig;
use Dvsa\Olcs\Transfer\Router\RouteConfig;

return [
    'translation-key' => [
        'type' => 'Segment',
        'options' => [
            'route' => 'translation-key[/]',
        ],
        'may_terminate' => false,
        'child_routes' => [
            'single' => RouteConfig::getSingleConfig(
                [
                    'GET' => QueryConfig::getConfig(Query\TranslationKey\ById::class),
                    'PUT' => CommandConfig::getPutConfig(Command\TranslationKey\Update::class),
                    'DELETE' => CommandConfig::getDeleteConfig(Command\TranslationKey\Delete::class),
                ]
            ),
            'GET' => QueryConfig::getConfig(Query\TranslationKey\GetList::class),
            'POST' => CommandConfig::getPostConfig(Command\TranslationKey\Create::class),
        ]
    ],
];
