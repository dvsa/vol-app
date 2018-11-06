<?php

use Zend\Mvc\Router\Http\Literal;
use Zend\Mvc\Router\Http\Segment;

return [
    [
        'licence' => [
            'type' => Literal::class,
            'options' => [
                'route' => '/licence',
            ],
            'may_terminate' => false,
            'child_routes' => [
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
            ],
        ],
    ]
];
