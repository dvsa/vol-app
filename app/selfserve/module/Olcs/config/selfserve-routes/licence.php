<?php

use Zend\Mvc\Router\Http\Literal;
use Zend\Mvc\Router\Http\Segment;

return [
    [
        'licence' => [
            'type' => Segment::class,
            'options' => [
                'route' => '/licence/:licence[/]',
                'constraints' => [
                    'licence' => '[0-9]+',
                ],
                'defaults'=>[
                    'controller'=> \Olcs\Controller\Lva\Licence\OverviewController::class,
                    'action' =>'index'
                ]
            ],
            'may_terminate' => true,
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
                                   
                                ]
                            ],

                            'may_terminate' => false,


                            'child_routes' => [
                                'GET' => [
                                    'type' => \Zend\Mvc\Router\Http\Method::class,
                                    'options' => [

                                        'verb' => 'GET',
                                        'defaults' => [
                                            'controller' => \Olcs\Controller\Licence\Surrender\StartController::class,
                                            'action' => 'index',
                                        ]
                                    ],

                                ],
                                'POST' =>
                                    [
                                        'type' => \Zend\Mvc\Router\Http\Method::class,
                                        'options' => [

                                            'verb' => 'POST',
                                            'defaults' => [
                                                'controller' => \Olcs\Controller\Licence\Surrender\StartController::class,
                                                'action' => 'start',
                                            ]
                                        ]
                                    ]
                            ],
                        ],
                    ],
                ],
            ],
        ]
    ]
];