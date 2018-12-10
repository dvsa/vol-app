<?php

use Olcs\Controller\Licence\Surrender\ReviewContactDetailsController;
use Olcs\Controller\Licence\Surrender\StartController;
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
                        'route' => 'surrender[/]',
                    ],
                    'may_terminate' => false,
                    'child_routes' => [
                        'start' => [
                            'may_terminate' => false,
                            'type' => Segment::class,
                            'options' => [
                                'route' => 'start[/]',
                            ],
                            'child_routes' => [
                                'GET' => [
                                    'may_terminate' => true,
                                    'type' => \Zend\Mvc\Router\Http\Method::class,
                                    'options' => [
                                        'verb' => 'GET',
                                        'defaults' => [
                                            'controller' => StartController::class,
                                            'action' => 'index',
                                        ],
                                    ],
                                ],
                                'POST' => [
                                    'may_terminate' => true,
                                    'type' => \Zend\Mvc\Router\Http\Method::class,
                                    'options' => [
                                        'verb' => 'POST',
                                        'defaults' => [
                                            'controller' => StartController::class,
                                            'action' => 'start',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        'review-contact-details' => [
                            'may_terminate' => true,
                            'type' => Segment::class,
                            'options' => [
                                'route' => 'review-contact-details[/:action][/]',
                                'defaults' => [
                                    'controller' => ReviewContactDetailsController::class,
                                    'action' => 'index',
                                ],
                                'constraints' => [
                                    'action' => '[a-z]+'
                                ],
                            ],
                        ],
                        'address-details' => [
                            'may_terminate' => true,
                            'type' => Segment::class,
                            'options' => [
                                'route' => 'address-details[/]',
                                'defaults' => [
                                    'controller' => Olcs\Controller\Licence\Surrender\AddressDetailsController::class,
                                    'action' => 'index',
                                ],
                            ],
                        ],
                        'confirmation' => [
                            'type' => Segment::class,
                            'may_terminate' => true,
                            'options' => [
                                'route' => 'confirmation[/]',
                                'defaults' => [
                                    'controller' => \Olcs\Controller\Licence\Surrender\ConfirmationController::class,
                                    'action' => 'index',
                                ],
                            ],
                        ],
                        'declaration' => [
                            'may_terminate' => true,
                            'type' => Segment::class,
                            'options' => [
                                'route' => 'declaration[/]',
                                'defaults' => [
                                    'controller' => Olcs\Controller\Licence\Surrender\DeclarationController::class,
                                    'action' => 'index'
                                ],
                            ]
                        ],
                        'operator-licence' => [
                            'may_terminate' => true,
                            'type' => Segment::class,
                            'options' => [
                                'route' => 'operator-licence[/]',
                                'defaults' => [
                                    'controller' => Olcs\Controller\Licence\Surrender\OperatorLicenceController::class,
                                    'action' => 'index'
                                ],
                            ]
                        ],
                    ]
                ],
            ],
        ],
    ],

];
