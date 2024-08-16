<?php

return [
    'routes' => [
        'create_application' => [
            'type' => \Laminas\Router\Http\Segment::class,
            'options' => [
                'route' => '/application/create[/]',
                'defaults' => [
                    'skipPreDispatch' => true,
                    'controller' => 'Application/TypeOfLicence',
                    'action' => 'createApplication',
                ],
            ],
        ],
        'lva-application' => [
            'type' => \Laminas\Router\Http\Segment::class,
            'options' => [
                'route' => '/application/:application[/]',
                'constraints' => [
                    'application' => '[0-9]+',
                ],
                'defaults' => [
                    'controller' => 'Application',
                    'action' => 'index',
                ],
            ],
            'may_terminate' => true,
            'child_routes' => [
                'type_of_licence' => [
                    'type' => \Common\Util\LvaRoute::class,
                    'options' => [
                        'route' => 'type-of-licence[/]',
                        'defaults' => [
                            'controller' => 'Application/TypeOfLicence',
                            'action' => 'index',
                        ],
                    ],
                    'may_terminate' => true,
                    'child_routes' => [
                        'action' => [
                            'type' => \Laminas\Router\Http\Segment::class,
                            'options' => [
                                'route' => ':action[/:child_id][/]',
                            ],
                        ],
                    ],
                ],
                'business_type' => [
                    'type' => \Common\Util\LvaRoute::class,
                    'options' => [
                        'route' => 'business-type[/]',
                        'defaults' => [
                            'controller' => 'Application/BusinessType',
                            'action' => 'index',
                        ],
                    ],
                    'may_terminate' => true,
                    'child_routes' => [
                        'action' => [
                            'type' => \Laminas\Router\Http\Segment::class,
                            'options' => [
                                'route' => ':action[/:child_id][/]',
                            ],
                        ],
                    ],
                ],
                'business_details' => [
                    'type' => \Common\Util\LvaRoute::class,
                    'options' => [
                        'route' => 'business-details[/]',
                        'defaults' => [
                            'controller' => 'Application/BusinessDetails',
                            'action' => 'index',
                        ],
                    ],
                    'may_terminate' => true,
                    'child_routes' => [
                        'action' => [
                            'type' => \Laminas\Router\Http\Segment::class,
                            'options' => [
                                'route' => ':action[/:child_id][/]',
                            ],
                        ],
                    ],
                ],
                'addresses' => [
                    'type' => \Common\Util\LvaRoute::class,
                    'options' => [
                        'route' => 'addresses[/]',
                        'defaults' => [
                            'controller' => 'Application/Addresses',
                            'action' => 'index',
                        ],
                    ],
                    'may_terminate' => true,
                    'child_routes' => [
                        'action' => [
                            'type' => \Laminas\Router\Http\Segment::class,
                            'options' => [
                                'route' => ':action[/:child_id][/]',
                            ],
                        ],
                    ],
                ],
                'people' => [
                    'type' => \Common\Util\LvaRoute::class,
                    'options' => [
                        'route' => 'people[/]',
                        'defaults' => [
                            'controller' => 'Application/People',
                            'action' => 'index',
                        ],
                    ],
                    'may_terminate' => true,
                    'child_routes' => [
                        'action' => [
                            'type' => \Laminas\Router\Http\Segment::class,
                            'options' => [
                                'route' => ':action[/:child_id][/]',
                            ],
                        ],
                    ],
                ],
                'taxi_phv' => [
                    'type' => \Common\Util\LvaRoute::class,
                    'options' => [
                        'route' => 'taxi-phv[/]',
                        'defaults' => [
                            'controller' => 'Application/TaxiPhv',
                            'action' => 'index',
                        ],
                    ],
                    'may_terminate' => true,
                    'child_routes' => [
                        'action' => [
                            'type' => \Laminas\Router\Http\Segment::class,
                            'options' => [
                                'route' => ':action[/:child_id][/]',
                            ],
                        ],
                    ],
                ],
                'operating_centres' => [
                    'type' => \Common\Util\LvaRoute::class,
                    'options' => [
                        'route' => 'operating-centres[/]',
                        'defaults' => [
                            'controller' => 'Application/OperatingCentres',
                            'action' => 'index',
                        ],
                    ],
                    'may_terminate' => true,
                    'child_routes' => [
                        'action' => [
                            'type' => \Laminas\Router\Http\Segment::class,
                            'options' => [
                                'route' => ':action[/:child_id][/]',
                            ],
                        ],
                    ],
                ],
                'financial_evidence' => [
                    'type' => \Common\Util\LvaRoute::class,
                    'options' => [
                        'route' => 'financial-evidence[/]',
                        'defaults' => [
                            'controller' => 'Application/FinancialEvidence',
                            'action' => 'index',
                        ],
                    ],
                    'may_terminate' => true,
                    'child_routes' => [
                        'action' => [
                            'type' => \Laminas\Router\Http\Segment::class,
                            'options' => [
                                'route' => ':action[/:child_id][/]',
                            ],
                        ],
                    ],
                ],
                'transport_managers' => [
                    'type' => \Common\Util\LvaRoute::class,
                    'options' => [
                        'route' => 'transport-managers[/]',
                        'defaults' => [
                            'controller' => 'Application/TransportManagers',
                            'action' => 'index',
                        ],
                    ],
                    'may_terminate' => true,
                    'child_routes' => [
                        'action' => [
                            'type' => \Laminas\Router\Http\Segment::class,
                            'options' => [
                                'route' => ':action[/:child_id][/]',
                            ],
                        ],
                    ],
                ],
                'vehicles' => [
                    'type' => \Laminas\Router\Http\Segment::class,
                    'options' => [
                        'route' => 'vehicles[/]',
                        'defaults' => [
                            'controller' => 'Application/Vehicles',
                            'action' => 'index',
                        ],
                    ],
                    'may_terminate' => true,
                    'child_routes' => [
                        'action' => [
                            'type' => \Laminas\Router\Http\Segment::class,
                            'options' => [
                                'route' => ':action[/:child_id][/]',
                            ],
                        ],
                    ],
                ],
                'vehicles_psv' => [
                    'type' => \Common\Util\LvaRoute::class,
                    'options' => [
                        'route' => 'vehicles-psv[/]',
                        'defaults' => [
                            'controller' => 'Application/VehiclesPsv',
                            'action' => 'index',
                        ],
                    ],
                    'may_terminate' => true,
                    'child_routes' => [
                        'action' => [
                            'type' => \Laminas\Router\Http\Segment::class,
                            'options' => [
                                'route' => ':action[/:child_id][/]',
                            ],
                        ],
                    ],
                ],
                'vehicles_declarations' => [
                    'type' => \Common\Util\LvaRoute::class,
                    'options' => [
                        'route' => 'vehicles-declarations[/]',
                        'defaults' => [
                            'controller' => 'Application/VehiclesDeclarations',
                            'action' => 'index',
                        ],
                    ],
                    'may_terminate' => true,
                    'child_routes' => [
                        'action' => [
                            'type' => \Laminas\Router\Http\Segment::class,
                            'options' => [
                                'route' => ':action[/:child_id][/]',
                            ],
                        ],
                    ],
                ],
                'trailers' => [
                    'type' => \Common\Util\LvaRoute::class,
                    'options' => [
                        'route' => 'trailers[/]',
                        'defaults' => [
                            'controller' => 'Application/Trailers',
                            'action' => 'index',
                        ],
                    ],
                    'may_terminate' => true,
                    'child_routes' => [
                        'action' => [
                            'type' => \Laminas\Router\Http\Segment::class,
                            'options' => [
                                'route' => ':action[/:child_id][/]',
                            ],
                        ],
                    ],
                ],
                'discs' => [
                    'type' => \Common\Util\LvaRoute::class,
                    'options' => [
                        'route' => 'discs[/]',
                        'defaults' => [
                            'controller' => 'Application/Discs',
                            'action' => 'index',
                        ],
                    ],
                    'may_terminate' => true,
                    'child_routes' => [
                        'action' => [
                            'type' => \Laminas\Router\Http\Segment::class,
                            'options' => [
                                'route' => ':action[/:child_id][/]',
                            ],
                        ],
                    ],
                ],
                'community_licences' => [
                    'type' => \Common\Util\LvaRoute::class,
                    'options' => [
                        'route' => 'community-licences[/]',
                        'defaults' => [
                            'controller' => 'Application/CommunityLicences',
                            'action' => 'index',
                        ],
                    ],
                    'may_terminate' => true,
                    'child_routes' => [
                        'action' => [
                            'type' => \Laminas\Router\Http\Segment::class,
                            'options' => [
                                'route' => ':action[/:child_id][/]',
                            ],
                        ],
                    ],
                ],
                'safety' => [
                    'type' => \Common\Util\LvaRoute::class,
                    'options' => [
                        'route' => 'safety[/]',
                        'defaults' => [
                            'controller' => 'Application/Safety',
                            'action' => 'index',
                        ],
                    ],
                    'may_terminate' => true,
                    'child_routes' => [
                        'action' => [
                            'type' => \Laminas\Router\Http\Segment::class,
                            'options' => [
                                'route' => ':action[/:child_id][/]',
                            ],
                        ],
                    ],
                ],
                'conditions_undertakings' => [
                    'type' => \Common\Util\LvaRoute::class,
                    'options' => [
                        'route' => 'conditions-undertakings[/]',
                        'defaults' => [
                            'controller' => 'Application/ConditionsUndertakings',
                            'action' => 'index',
                        ],
                    ],
                    'may_terminate' => true,
                    'child_routes' => [
                        'action' => [
                            'type' => \Laminas\Router\Http\Segment::class,
                            'options' => [
                                'route' => ':action[/:child_id][/]',
                            ],
                        ],
                    ],
                ],
                'financial_history' => [
                    'type' => \Common\Util\LvaRoute::class,
                    'options' => [
                        'route' => 'financial-history[/]',
                        'defaults' => [
                            'controller' => 'Application/FinancialHistory',
                            'action' => 'index',
                        ],
                    ],
                    'may_terminate' => true,
                    'child_routes' => [
                        'action' => [
                            'type' => \Laminas\Router\Http\Segment::class,
                            'options' => [
                                'route' => ':action[/:child_id][/]',
                            ],
                        ],
                    ],
                ],
                'licence_history' => [
                    'type' => \Common\Util\LvaRoute::class,
                    'options' => [
                        'route' => 'licence-history[/]',
                        'defaults' => [
                            'controller' => 'Application/LicenceHistory',
                            'action' => 'index',
                        ],
                    ],
                    'may_terminate' => true,
                    'child_routes' => [
                        'action' => [
                            'type' => \Laminas\Router\Http\Segment::class,
                            'options' => [
                                'route' => ':action[/:child_id][/]',
                            ],
                        ],
                    ],
                ],
                'convictions_penalties' => [
                    'type' => \Common\Util\LvaRoute::class,
                    'options' => [
                        'route' => 'convictions-penalties[/]',
                        'defaults' => [
                            'controller' => 'Application/ConvictionsPenalties',
                            'action' => 'index',
                        ],
                    ],
                    'may_terminate' => true,
                    'child_routes' => [
                        'action' => [
                            'type' => \Laminas\Router\Http\Segment::class,
                            'options' => [
                                'route' => ':action[/:child_id][/]',
                            ],
                        ],
                    ],
                ],
                'undertakings' => [
                    'type' => \Common\Util\LvaRoute::class,
                    'options' => [
                        'route' => 'undertakings[/]',
                        'defaults' => [
                            'controller' => 'Application/Undertakings',
                            'action' => 'index',
                        ],
                    ],
                    'may_terminate' => true,
                    'child_routes' => [
                        'action' => [
                            'type' => \Laminas\Router\Http\Segment::class,
                            'options' => [
                                'route' => ':action[/:child_id][/]',
                            ],
                        ],
                    ],
                ],
                'declarations_internal' => [
                    'type' => \Common\Util\LvaRoute::class,
                    'options' => [
                        'route' => 'declarations-internal[/]',
                        'defaults' => [
                            'controller' => 'Application/DeclarationsInternal',
                            'action' => 'index',
                        ],
                    ],
                    'may_terminate' => true,
                    'child_routes' => [
                        'action' => [
                            'type' => \Laminas\Router\Http\Segment::class,
                            'options' => [
                                'route' => ':action[/:child_id][/]',
                            ],
                        ],
                    ],
                ],
                'review' => [
                    'type' => \Laminas\Router\Http\Segment::class,
                    'options' => [
                        'route' => 'review[/]',
                        'defaults' => [
                            'controller' => 'Application/Review',
                            'action' => 'index',
                        ],
                    ],
                ],
                'declaration' => [
                    'type' => \Laminas\Router\Http\Segment::class,
                    'options' => [
                        'route' => 'declaration[/]',
                        'defaults' => [
                            'controller' => 'DeclarationFormController',
                            'action' => 'index',
                        ],
                    ],
                ],
                'pay-and-submit' => [
                    'type' => \Laminas\Router\Http\Segment::class,
                    'options' => [
                        'route' => 'pay-and-submit[/:redirect-back][/]',
                        'defaults' => [
                            'controller' => 'Application/PaymentSubmission',
                            'action' => 'payAndSubmit',
                            'redirect-back' => 'overview',
                        ],
                        'constraints' => [
                            'redirect-back' => '[a-z\-]+',
                        ],
                    ],
                ],
                'payment' => [
                    'type' => \Laminas\Router\Http\Segment::class,
                    'options' => [
                        'route' => 'payment[/stored-card-reference/:storedCardReference][/]',
                        'defaults' => [
                            'controller' => 'Application/PaymentSubmission',
                            'action' => 'index',
                        ],
                        'constraints' => [
                            'storedCardReference' => '[0-9A-Za-z]+-[0-9A-F\-]+',
                        ],
                    ],
                ],
                'submission-summary' => [
                    'type' => \Laminas\Router\Http\Segment::class,
                    'options' => [
                        'route' => 'submission-summary[/]',
                        'defaults' => [
                            'controller' => 'Application/Summary',
                            'action' => 'postSubmitSummary',
                        ],
                    ],
                ],
                'upload-evidence' => [
                    'type' => \Laminas\Router\Http\Segment::class,
                    'options' => [
                        'route' => 'upload-evidence[/]',
                        'defaults' => [
                            'controller' => 'Application/UploadEvidence',
                            'action' => 'index',
                        ],
                    ],
                ],
                'summary' => [
                    'type' => \Laminas\Router\Http\Segment::class,
                    'options' => [
                        'route' => 'summary[/:reference][/]',
                        'constraints' => [
                            'reference' => '[0-9A-Za-z]+-[0-9A-F\-]+',
                        ],
                        'defaults' => [
                            'controller' => 'Application/Summary',
                            'action' => 'index',
                        ],
                    ],
                ],
                'result' => [
                    'type' => \Laminas\Router\Http\Segment::class,
                    'options' => [
                        'route' => 'result[/]',
                        'defaults' => [
                            'controller' => 'Application/PaymentSubmission',
                            'action' => 'payment-result',

                        ],
                    ],
                ],
                'cancel' => [
                    'type' => \Laminas\Router\Http\Segment::class,
                    'options' => [
                        'route' => 'cancel[/]',
                        'defaults' => [
                            'controller' => 'Application',
                            'action' => 'cancel',
                        ],
                    ],
                ],
                'withdraw' => [
                    'type' => \Laminas\Router\Http\Segment::class,
                    'options' => [
                        'route' => 'withdraw[/]',
                        'defaults' => [
                            'controller' => 'Application',
                            'action' => 'withdraw',
                        ],
                    ],
                ],
                'transport_manager_details' => [
                    'type' => \Laminas\Router\Http\Segment::class,
                    'options' => [
                        'route' => 'transport-managers/details/:child_id[/]',
                        'constraints' => [
                            'child_id' => '[0-9]+',
                            'grand_child_id' => '[0-9]+',
                        ],
                        'defaults' => [
                            'controller' => 'Application/TransportManagers',
                            'action' => 'details',
                        ],
                    ],
                    'may_terminate' => true,
                    'child_routes' => [
                        'action' => [
                            'type' => \Laminas\Router\Http\Segment::class,
                            'options' => [
                                'route' => ':action[/:grand_child_id][/]',
                                'constraints' => ['grand_child_id' => '[0-9\\,]+',],
                            ],
                        ],
                    ],
                ],
                'transport_manager_check_answer' => [
                    'type' => \Laminas\Router\Http\Segment::class,
                    'options' => [
                        'route' => 'transport-managers/check-answer/:child_id[/]',
                        'constraints' => [
                            'child_id' => '[0-9]+',
                            'grand_child_id' => '[0-9]+',
                        ],
                        'defaults' => [
                            'controller' => 'LvaTransportManager/CheckAnswers',
                            'action' => 'index',
                        ],
                    ],
                    'may_terminate' => true,
                    'child_routes' => [
                        'action' => [
                            'type' => \Laminas\Router\Http\Segment::class,
                            'options' => [
                                'route' => ':action[/:grand_child_id][/]',
                                'constraints' => ['grand_child_id' => '[0-9\\,]+',],
                            ],
                        ],
                    ],
                ],
                'transport_manager_tm_declaration' => [
                    'type' => \Laminas\Router\Http\Segment::class,
                    'options' => [
                        'route' => 'transport-managers/tm-declaration/:child_id[/]',
                        'constraints' => [
                            'child_id' => '[0-9]+',
                            'grand_child_id' => '[0-9]+',
                        ],
                        'defaults' => [
                            'controller' => 'LvaTransportManager/TmDeclaration',
                            'action' => 'index',
                        ],
                    ],
                    'may_terminate' => true,
                ],
                'transport_manager_operator_declaration' => [
                    'type' => \Laminas\Router\Http\Segment::class,
                    'options' => [
                        'route' => 'transport-managers/operator-declaration/:child_id[/]',
                        'constraints' => [
                            'child_id' => '[0-9]+',
                            'grand_child_id' => '[0-9]+',
                        ],
                        'defaults' => [
                            'controller' => 'LvaTransportManager/OperatorDeclaration',
                            'action' => 'index',
                        ],
                    ],
                    'may_terminate' => true,
                ],
                'transport_manager_confirmation' => [
                    'type' => \Laminas\Router\Http\Segment::class,
                    'options' => [
                        'route' => 'transport-managers/confirmation/:child_id[/]',
                        'constraints' => [
                            'child_id' => '[0-9]+',
                            'grand_child_id' => '[0-9]+',
                        ],
                        'defaults' => [
                            'controller' => 'LvaTransportManager/Confirmation',
                            'action' => 'index',
                        ],
                    ],
                    'may_terminate' => true,
                ],
            ],
        ],
    ],
];
