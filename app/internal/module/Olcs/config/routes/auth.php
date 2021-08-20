<?php

declare(strict_types=1);

return [
    'auth' => [
        'type' => 'segment',
        'options' => [
            'route' => '/auth[/]'
        ],
        'may_terminate' => false,
        'child_routes' => [
            'login' => [
                'may_terminate' => false,
                'type' => Segment::class,
                'options' => [
                    'route' => 'login[/]',
                    'defaults' => [
                        'controller' => \Olcs\Controller\Auth\LoginController::class,
                    ]
                ],
                'child_routes' => [
                    'GET' => [
                        'may_terminate' => true,
                        'type' => Method::class,
                        'options' => [
                            'verb' => 'GET',
                            'defaults' => [
                                'action' => 'index',
                            ],
                        ],
                    ],
                    'POST' => [
                        'may_terminate' => true,
                        'type' => Method::class,
                        'options' => [
                            'verb' => 'POST',
                            'defaults' => [
                                'action' => 'post',
                            ],
                        ],
                    ],
                ],
            ],
        ]
    ],
];
