<?php

use Zend\Mvc\Router\Http\Literal;
use Zend\Mvc\Router\Http\Segment;

return [
    [
        'licence' => [
            'type' => Segment::class,
            'options' => [
                'route' => '/licence/:id[/]',
                'constraints' => [
                    'id' => '[0-9]+',
                ],
            ],
            'may_terminate' => false,
            'child_routes' => [
                'surrender' => [
                    'type' => Segment::class,
                    'options' => [
                        'route' => 'surrender[/]',

                    ],
                    'may_terminate' => false,
                    'child_routes' => [
                        'start' => [
                            'type' => Segment::class,
                            'options' => [
                                'route' => 'start[/]',
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
