<?php
declare(strict_types = 1);

use Olcs\Controller\Licence\Surrender\DestroyController;
use Olcs\Controller\Licence\Surrender\InformationChangedController;
use Olcs\Controller\Licence\Surrender\PrintSignReturnController;
use Olcs\Controller\Licence\Surrender\ReviewContactDetailsController;
use Olcs\Controller\Licence\Surrender\StartController;
use Olcs\Controller\Licence\Vehicle\AddDuplicateVehicleController;
use Olcs\Controller\Licence\Vehicle\AddVehicleSearchController;
use Olcs\Controller\Licence\Vehicle\RemoveVehicleConfirmationController;
use Olcs\Controller\Licence\Vehicle\RemoveVehicleController;
use Olcs\Controller\Licence\Vehicle\SwitchBoardController;
use Olcs\Controller\Licence\Vehicle\TransferVehicleConfirmationController;
use Olcs\Controller\Licence\Vehicle\TransferVehicleController;
use Olcs\Controller\Licence\Vehicle\ListVehicleController;
use Olcs\Controller\Licence\Vehicle\ViewVehicleController;
use Laminas\Mvc\Router\Http\Method;
use Laminas\Mvc\Router\Http\Segment;

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
                                    'type' => Method::class,
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
                                    'type' => Method::class,
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
                            'may_terminate' => false,
                            'type' => Segment::class,
                            'options' => [
                                'route' => 'review-contact-details[/]',
                            ],
                            'child_routes' => [
                                'GET' => [
                                    'may_terminate' => true,
                                    'type' => Method::class,
                                    'options' => [
                                        'verb' => 'GET',
                                        'defaults' => [
                                            'controller' => ReviewContactDetailsController::class,
                                            'action' => 'index'
                                        ],
                                    ],
                                ],
                                'POST' => [
                                    'may_terminate' => true,
                                    'type' => Method::class,
                                    'options' => [
                                        'verb' => 'POST',
                                        'defaults' => [
                                            'controller' => ReviewContactDetailsController::class,
                                            'action' => 'post'
                                        ],
                                    ],
                                ]
                            ]
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
                        'current-discs' => [
                            'may_terminate' => false,
                            'type' => Segment::class,
                            'options' => [
                                'route' => 'current-discs[/]',
                            ],
                            'child_routes' => [
                                'GET' => [
                                    'may_terminate' => true,
                                    'type' => Method::class,
                                    'options' => [
                                        'verb' => 'GET',
                                        'defaults' => [
                                            'controller' => Olcs\Controller\Licence\Surrender\CurrentDiscsController::class,
                                            'action' => 'index'
                                        ],
                                    ],
                                ],

                                'POST' => [
                                    'may_terminate' => true,
                                    'type' => Method::class,
                                    'options' => [
                                        'verb' => 'POST',
                                        'defaults' => [
                                            'controller' => Olcs\Controller\Licence\Surrender\CurrentDiscsController::class,
                                            'action' => 'post'
                                        ],
                                    ],
                                ],
                                'review' => [
                                    'may_terminate' => false,
                                    'type' => Segment::class,
                                    'options' => [
                                        'route' => 'review',
                                    ],
                                    'child_routes' => [
                                        'GET' => [
                                            'may_terminate' => true,
                                            'type' => Method::class,
                                            'options' => [
                                                'verb' => 'GET',
                                                'defaults' => [
                                                    'controller' => Olcs\Controller\Licence\Surrender\CurrentDiscsController::class,
                                                    'action' => 'index',
                                                    'review' => true
                                                ],
                                            ],
                                        ],
                                        'POST' => [
                                            'may_terminate' => true,
                                            'type' => Method::class,
                                            'options' => [
                                                'verb' => 'POST',
                                                'defaults' => [
                                                    'controller' => Olcs\Controller\Licence\Surrender\CurrentDiscsController::class,
                                                    'action' => 'post',
                                                    'review' => true
                                                ],
                                            ],
                                        ],
                                    ]
                                ]
                            ]
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
                            'may_terminate' => false,
                            'type' => Segment::class,
                            'options' => [
                                'route' => 'declaration[/]',
                            ],
                            'child_routes' => [
                                'GET' => [
                                    'may_terminate' => true,
                                    'type' => Method::class,
                                    'options' => [
                                        'verb' => 'GET',
                                        'defaults' => [
                                            'controller' => Olcs\Controller\Licence\Surrender\DeclarationController::class,
                                            'action' => 'index'
                                        ],
                                    ],
                                ],
                                'POST' => [
                                    'may_terminate' => true,
                                    'type' => Method::class,
                                    'options' => [
                                        'verb' => 'POST',
                                        'defaults' => [
                                            'controller' => PrintSignReturnController::class,
                                            'action' => 'index',
                                        ],
                                    ],
                                ]
                            ]
                        ],
                        'operator-licence' => [
                            'may_terminate' => false,
                            'type' => Segment::class,
                            'options' => [
                                'route' => 'operator-licence[/]',
                            ],
                            'child_routes' => [
                                'GET' => [
                                    'may_terminate' => true,
                                    'type' => Method::class,
                                    'options' => [
                                        'verb' => 'GET',
                                        'defaults' => [
                                            'controller' => Olcs\Controller\Licence\Surrender\OperatorLicenceController::class,
                                            'action' => 'index'
                                        ],
                                    ],
                                ],
                                'POST' => [
                                    'may_terminate' => true,
                                    'type' => Method::class,
                                    'options' => [
                                        'verb' => 'POST',
                                        'defaults' => [
                                            'controller' => Olcs\Controller\Licence\Surrender\OperatorLicenceController::class,
                                            'action' => 'submit'
                                        ],
                                    ],
                                ],
                                'review' => [
                                    'may_terminate' => false,
                                    'type' => Segment::class,
                                    'options' => [
                                        'route' => 'review',
                                    ],
                                    'child_routes' => [
                                        'GET' => [
                                            'may_terminate' => true,
                                            'type' => Method::class,
                                            'options' => [
                                                'verb' => 'GET',
                                                'defaults' => [
                                                    'controller' => Olcs\Controller\Licence\Surrender\OperatorLicenceController::class,
                                                    'action' => 'index',
                                                    'review' => true
                                                ],
                                            ],
                                        ],
                                        'POST' => [
                                            'may_terminate' => true,
                                            'type' => Method::class,
                                            'options' => [
                                                'verb' => 'POST',
                                                'defaults' => [
                                                    'controller' => Olcs\Controller\Licence\Surrender\OperatorLicenceController::class,
                                                    'action' => 'submit',
                                                    'review' => true
                                                ],
                                            ],
                                        ],
                                    ]
                                ]
                            ]
                        ],
                        'community-licence' => [
                            'may_terminate' => false,
                            'type' => Segment::class,
                            'options' => [
                                'route' => 'community-licence[/]',
                            ],
                            'child_routes' => [
                                'GET' => [
                                    'may_terminate' => true,
                                    'type' => Method::class,
                                    'options' => [
                                        'verb' => 'GET',
                                        'defaults' => [
                                            'controller' => Olcs\Controller\Licence\Surrender\CommunityLicenceController::class,
                                            'action' => 'index'
                                        ],
                                    ],
                                ],
                                'POST' => [
                                    'may_terminate' => true,
                                    'type' => Method::class,
                                    'options' => [
                                        'verb' => 'POST',
                                        'defaults' => [
                                            'controller' => Olcs\Controller\Licence\Surrender\CommunityLicenceController::class,
                                            'action' => 'submit'
                                        ],
                                    ],
                                ],
                                'review' => [
                                    'may_terminate' => false,
                                    'type' => Segment::class,
                                    'options' => [
                                        'route' => 'review',
                                    ],
                                    'child_routes' => [
                                        'GET' => [
                                            'may_terminate' => true,
                                            'type' => Method::class,
                                            'options' => [
                                                'verb' => 'GET',
                                                'defaults' => [
                                                    'controller' => Olcs\Controller\Licence\Surrender\CommunityLicenceController::class,
                                                    'action' => 'index',
                                                    'review' => true
                                                ],
                                            ],
                                        ],
                                        'POST' => [
                                            'may_terminate' => true,
                                            'type' => Method::class,
                                            'options' => [
                                                'verb' => 'POST',
                                                'defaults' => [
                                                    'controller' => Olcs\Controller\Licence\Surrender\CommunityLicenceController::class,
                                                    'action' => 'submit',
                                                    'review' => true
                                                ],
                                            ],
                                        ],
                                    ]
                                ]
                            ],
                        ],
                        'review' => [
                            'may_terminate' => false,
                            'type' => Segment::class,
                            'options' => [
                                'route' => 'review[/]',
                            ],
                            'child_routes' => [
                                'GET' => [
                                    'may_terminate' => true,
                                    'type' => Method::class,
                                    'options' => [
                                        'verb' => 'GET',
                                        'defaults' => [
                                            'controller' => Olcs\Controller\Licence\Surrender\ReviewController::class,
                                            'action' => 'index'
                                        ],
                                    ],
                                ],
                                'POST' => [
                                    'may_terminate' => true,
                                    'type' => Method::class,
                                    'options' => [
                                        'verb' => 'POST',
                                        'defaults' => [
                                            'controller' => Olcs\Controller\Licence\Surrender\ReviewController::class,
                                            'action' => 'confirmation'
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        'destroy' => [
                            'may_terminate' => false,
                            'type' => Segment::class,
                            'options' => [
                                'route' => 'destroy[/]',
                            ],
                            'child_routes' => [
                                'GET' => [
                                    'may_terminate' => true,
                                    'type' => Method::class,
                                    'options' => [
                                        'verb' => 'GET',
                                        'defaults' => [
                                            'controller' => DestroyController::class,
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
                                            'controller' => DestroyController::class,
                                            'action' => 'continue',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        'print-sign-return-print' => [
                            'may_terminate' => false,
                            'type' => Segment::class,
                            'options' => [

                                'route' => 'print-sign-return/print[/]'

                            ],
                            'child_routes' => [
                                'GET' => [
                                    'may_terminate' => true,
                                    'type' => Method::class,
                                    'options' => [
                                        'verb' => 'GET',
                                        'defaults' => [
                                            'controller' => PrintSignReturnController::class,
                                            'action' => 'print',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        'print-sign-return' => [
                            'may_terminate' => false,
                            'type' => Segment::class,
                            'options' => [

                                'route' => 'print-sign-return[/]'

                            ],
                            'child_routes' => [
                                'GET' => [
                                    'may_terminate' => true,
                                    'type' => Method::class,
                                    'options' => [
                                        'verb' => 'GET',
                                        'defaults' => [
                                            'controller' => PrintSignReturnController::class,
                                            'action' => 'index',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        'information-changed' => [
                            'may_terminate' => false,
                            'type' => Segment::class,
                            'options' => [
                                'route' => 'information-changed[/]'
                            ],
                            'child_routes' => [
                                'GET' => [
                                    'may_terminate' => true,
                                    'type' => Method::class,
                                    'options' => [
                                        'verb' => 'GET',
                                        'defaults' => [
                                            'controller' => InformationChangedController::class,
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
                                            'controller' => InformationChangedController::class,
                                            'action' => 'submit',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ]
                ],
                'vehicle' => [
                    'may_terminate' => false,
                    'type' => Segment::class,
                    'options' => [
                        'route' => 'vehicle[/]',
                        'defaults' => [
                            'controller' => SwitchBoardController::class,
                        ],
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
                                    'action' => 'decision',
                                ],
                            ],
                        ],
                        'add' => [
                            'may_terminate' => false,
                            'type' => Segment::class,
                            'options' => [
                                'route' => 'add[/]',
                                'defaults' => [
                                    'controller' => AddVehicleSearchController::class,
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
                                'clear' =>  [
                                    'may_terminate' => true,
                                    'type' => Segment::class,
                                    'options' => [
                                        'route' => 'clear[/]',
                                        'defaults' => [
                                            'action' => 'clear',
                                        ],
                                    ],
                                ],
                                'confirmation' =>  [
                                    'may_terminate' => true,
                                    'type' => Segment::class,
                                    'options' => [
                                        'route' => 'confirmation[/]',
                                        'defaults' => [
                                            'action' => 'confirmation',
                                        ],
                                    ],
                                ],
                                'duplicate-confirmation' =>  [
                                    'may_terminate' => false,
                                    'type' => Segment::class,
                                    'options' => [
                                        'route' => 'duplicate-confirmation[/]',
                                        'defaults' => [
                                            'controller' => AddDuplicateVehicleController::class,
                                        ],
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
                            ],
                        ],
                        'remove' => [
                            'may_terminate' => false,
                            'type' => Segment::class,
                            'options' => [
                                'route' => 'remove[/]',
                                'defaults' => [
                                    'controller' => RemoveVehicleController::class,
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
                                'confirm' => [
                                    'may_terminate' => false,
                                    'type' => Segment::class,
                                    'options' => [
                                        'route' => 'confirm[/]',
                                        'defaults' => [
                                            'controller' => RemoveVehicleConfirmationController::class,
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
                            ],
                        ],
                        'transfer' => [
                            'may_terminate' => false,
                            'type' => Segment::class,
                            'options' => [
                                'route' => 'transfer[/]',
                                'defaults' => [
                                    'controller' => TransferVehicleController::class,
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
                                'confirm' => [
                                    'may_terminate' => false,
                                    'type' => Segment::class,
                                    'options' => [
                                        'route' => 'confirm[/]',
                                        'defaults' => [
                                            'controller' => TransferVehicleConfirmationController::class,
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
                            ],
                        ],
                        'list' => [
                            'may_terminate' => false,
                            'type' => Segment::class,
                            'options' => [
                                'route' => 'list[/]',
                                'defaults' => [
                                    'controller' => ListVehicleController::class,
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
                            ],
                        ],
                        'reprint' => [
                            'may_terminate' => false,
                            'type' => Segment::class,
                            'options' => [
                                'route' => 'reprint[/]',
                                'defaults' => [
                                    'controller' => \Olcs\Controller\Licence\Vehicle\Reprint\ReprintLicenceVehicleDiscController::class,
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
                                'confirm' => [
                                    'may_terminate' => false,
                                    'type' => Segment::class,
                                    'options' => [
                                        'route' => 'confirm[/]',
                                        'defaults' => [
                                            'controller' => \Olcs\Controller\Licence\Vehicle\Reprint\ReprintLicenceVehicleDiscConfirmationController::class,
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
                            ],
                        ],
                        'view' => [
                            'may_terminate' => false,
                            'type' => Segment::class,
                            'options' => [
                                'route' => 'view/:vehicle[/]',
                                'defaults' => [
                                    'controller' => ViewVehicleController::class,
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
                                ]
                            ],
                        ],
                    ]
                ],
            ],
        ],
    ],

];
