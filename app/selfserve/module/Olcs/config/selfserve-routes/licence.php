<?php

use Olcs\Controller\Licence\Surrender\ReviewContactDetailsController;
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
            ],
            'may_terminate' => false,
            'child_routes' => [
                'surrender' => [
                    'type' => Segment::class,
                    'options' => [
                        'route' => 'surrender',
                    ],
                    'may_terminate' => false,
                    'child_routes' => [
                        'start' => [
                            'may_terminate' => false,
                            'type' => Segment::class,
                            'options' => [
                                'route' => '',

                            ],
                            'child_routes' => [
                                'GET' => [
                                    'type' => \Zend\Mvc\Router\Http\Method::class,
                                    'options' => [

                                        'verb'=>'GET',
                                        'defaults' => [
                                            'controller' => \Olcs\Controller\Licence\Surrender\StartController::class,
                                            'action' => 'index',
                                        ],
                                    ],
                                ],
                                'POST' => [
                                    'type' => \Zend\Mvc\Router\Http\Method::class,
                                    'options' => [

                                        'verb'=>'POST',
                                        'defaults' => [
                                            'controller' => \Olcs\Controller\Licence\Surrender\StartController::class,
                                            'action' => 'start',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'review-contact-details' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => ':surrender/review-contact-details[/:action][/]',
                            'defaults' => [
                                'controller' => ReviewContactDetailsController::class,
                                'action' => 'index',
                            ],
                        ],
                    ],
                    'address-details' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => ':surrender/address-details[/]',
                            'defaults' => [
                                'controller' => Olcs\Controller\Licence\Surrender\AddressDetailsController::class,
                                'action' => 'index',
                            ],
                        ],
                    ],
                ]
            ],
        ],
    ],
];
