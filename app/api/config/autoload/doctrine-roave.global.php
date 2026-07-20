<?php

declare(strict_types=1);

use Roave\PsrContainerDoctrine\ConnectionFactory;
use Roave\PsrContainerDoctrine\EntityManagerFactory;

return [
    'service_manager' => [
        'factories' => [
            'doctrine.connection.orm_default' => [
                ConnectionFactory::class,
                'orm_default',
            ],
            'doctrine.connection.export' => [
                ConnectionFactory::class,
                'export',
            ],
            'doctrine.entity_manager.orm_default' => [
                EntityManagerFactory::class,
                'orm_default',
            ],
        ],
        'aliases' => [
            'doctrine.entitymanager.orm_default'
                => 'doctrine.entity_manager.orm_default',
        ],
    ],
];