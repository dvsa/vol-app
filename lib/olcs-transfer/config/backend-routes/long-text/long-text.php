<?php

use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Transfer\Query;
use Dvsa\Olcs\Transfer\Router\CommandConfig;
use Dvsa\Olcs\Transfer\Router\QueryConfig;

return [
    'long-text' => [
        'type' => 'Segment',
        'options' => [
            'route' => 'long-text[/]',
        ],
        'may_terminate' => false,
        'child_routes' => [
            'list' => [
                'type' => 'Segment',
                'options' => ['route' => 'list[/]'],
                'child_routes' => [
                    'GET' => QueryConfig::getConfig(Query\LongText\GetList::class),
                ],
            ],
            'create' => [
                'type' => 'Segment',
                'options' => ['route' => 'create[/]'],
                'child_routes' => [
                    'POST' => CommandConfig::getPostConfig(Command\LongText\Create::class),
                ],
            ],
            'update' => [
                'type' => 'Segment',
                'options' => [
                    'route' => ':id[/]',
                    'constraints' => ['id' => '[0-9]+'],
                ],
                'child_routes' => [
                    'GET' => QueryConfig::getConfig(Query\LongText\ById::class),
                    'PUT' => CommandConfig::getPutConfig(Command\LongText\Update::class),
                ],
            ],
            'by-reference-key' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'key/:referenceKey[/]',
                    'constraints' => [
                        'referenceKey' => '[a-z0-9]+(-[a-z0-9]+)*',
                    ],
                ],
                'child_routes' => [
                    'GET' => QueryConfig::getConfig(Query\LongText\ByReferenceKey::class),
                ],
            ],
        ],
    ],
];
