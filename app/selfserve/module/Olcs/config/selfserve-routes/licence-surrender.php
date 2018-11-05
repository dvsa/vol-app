<?php

use Zend\Mvc\Router\Http\Segment;

return [
    [
        'licence-surrender' => [
            'type' => Segment::class,
            'options' => [
                'route' => '/licence/:licence/surrender',
                'constraints' => [
                    'licence' => '[0-9]+',
                ],
            ],
            'may_terminate' => false,
            'child_routes' => [
                'start' => [
                    'type' => Segment::class,
                    'options' => [
                        'route' => '/start',
                        'defaults' => [
                            'controller' => 'SurrenderStart',
                            'action' => 'index',
                        ],
                    ],
                ],
            ]
        ],
    ]
];
