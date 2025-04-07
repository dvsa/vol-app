<?php

use Common\Util\LvaRoute;
use Dvsa\Olcs\Application\Controller\VehiclesDeclarationsController;
use Laminas\Router\Http\Segment;

return [
    'routes' => [
        'create_application' => [
            'type' => Segment::class,
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
            'type' => Segment::class,
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
                    'type' => LvaRoute::class,
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
                            'type' => Segment::class,
                            'options' => [
                                'route' => ':action[/:child_id][/]',
                            ],
                        ],
                    ],
                ],
                'business_type' => [
                    'type' => LvaRoute::class,
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
                            'type' => Segment::class,
                            'options' => [
                                'route' => ':action[/:child_id][/]',
                            ],
                        ],
                    ],
                ],
                'business_details' => [
                    'type' => LvaRoute::class,
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
                            'type' => Segment::class,
                            'options' => [
                                'route' => ':action[/:child_id][/]',
                            ],
                        ],
                    ],
                ],
                'addresses' => [
                    'type' => LvaRoute::class,
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
                            'type' => Segment::class,
                            'options' => [
                                'route' => ':action[/:child_id][/]',
                            ],
                        ],
                    ],
                ],
                'people' => [
                    'type' => LvaRoute::class,
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
                            'type' => Segment::class,
                            'options' => [
                                'route' => ':action[/:child_id][/]',
                            ],
                        ],
                    ],
                ],
                'taxi_phv' => [
                    'type' => LvaRoute::class,
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
                            'type' => Segment::class,
                            'options' => [
                                'route' => ':action[/:child_id][/]',
                            ],
                        ],
                    ],
                ],
                'operating_centres' => [
                    'type' => LvaRoute::class,
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
                            'type' => Segment::class,
                            'options' => [
                                'route' => ':action[/:child_id][/]',
                            ],
                        ],
                    ],
                ],
                'financial_evidence' => [
                    'type' => LvaRoute::class,
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
                            'type' => Segment::class,
                            'options' => [
                                'route' => ':action[/:child_id][/]',
                            ],
                        ],
                    ],
                ],
                'transport_managers' => [
                    'type' => LvaRoute::class,
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
                            'type' => Segment::class,
                            'options' => [
                                'route' => ':action[/:child_id][/]',
                            ],
                        ],
                    ],
                ],
                'vehicles' => [
                    'type' => Segment::class,
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
                            'type' => Segment::class,
                            'options' => [
                                'route' => ':action[/:child_id][/]',
                            ],
                        ],
                    ],
                ],
                'vehicles_psv' => [
                    'type' => LvaRoute::class,
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
                            'type' => Segment::class,
                            'options' => [
                                'route' => ':action[/:child_id][/]',
                            ],
                        ],
                    ],
                ],
                'vehicles_size' => [
                    'type' => LvaRoute::class,
                    'options' => [
                        'route' => 'vehicles-sizes[/]',
                        'defaults' => [
                            'controller' => VehiclesDeclarationsController::class,
                            'action' => 'size',
                        ],
                    ],
                    'may_terminate' => true,
                    'child_routes' => [
                        'action' => [
                            'type' => Segment::class,
                            'options' => [
                                'route' => ':action[/:child_id][/]',
                            ],
                        ],
                    ],
                ],
                'vehicles_size_nine' => [
                    'type' => LvaRoute::class,
                    'options' => [
                        'route' => 'vehicles-size-nine[/]',
                        'defaults' => [
                            'controller' => VehiclesDeclarationsController::class,
                            'action' => 'sizeNine',
                        ],
                    ],
                    'may_terminate' => true,
                    'child_routes' => [
                        'action' => [
                            'type' => Segment::class,
                            'options' => [
                                'route' => ':action[/:child_id][/]',
                            ],
                        ],
                    ],
                ],
                'operating_small_vehicles' => [
                    'type' => LvaRoute::class,
                    'options' => [
                        'route' => 'operating-small-vehicles[/]',
                        'defaults' => [
                            'controller' => VehiclesDeclarationsController::class,
                            'action' => 'operatingSmall',
                        ],
                    ],
                    'may_terminate' => true,
                    'child_routes' => [
                        'action' => [
                            'type' => Segment::class,
                            'options' => [
                                'route' => ':action[/:child_id][/]',
                            ],
                        ],
                    ],
                ],
                'written_explanation_small_part' => [
                    'type' => LvaRoute::class,
                    'options' => [
                        'route' => 'written-explanation-small-part[/]',
                        'defaults' => [
                            'controller' => VehiclesDeclarationsController::class,
                            'action' => 'size',
                        ],
                    ],
                    'may_terminate' => true,
                    'child_routes' => [
                        'action' => [
                            'type' => Segment::class,
                            'options' => [
                                'route' => ':action[/:child_id][/]',
                            ],
                        ],
                    ],
                ],
                'small_vehicles_condition_undertakings' => [
                    'type' => LvaRoute::class,
                    'options' => [
                        'route' => 'small-vehicles-condition-undertakings[/]',
                        'defaults' => [
                            'controller' => VehiclesDeclarationsController::class,
                            'action' => 'smallConditions',
                        ],
                    ],
                    'may_terminate' => true,
                    'child_routes' => [
                        'action' => [
                            'type' => Segment::class,
                            'options' => [
                                'route' => ':action[/:child_id][/]',
                            ],
                        ],
                    ],
                ],
                'documentary_evidence_small_vehicles' => [
                    'type' => LvaRoute::class,
                    'options' => [
                        'route' => 'documentary-evidence-small-vehicles[/]',
                        'defaults' => [
                            'controller' => VehiclesDeclarationsController::class,
                            'action' => 'size',
                        ],
                    ],
                    'may_terminate' => true,
                    'child_routes' => [
                        'action' => [
                            'type' => Segment::class,
                            'options' => [
                                'route' => ':action[/:child_id][/]',
                            ],
                        ],
                    ],
                ],
                'documentary_evidence_main_occupation' => [
                    'type' => LvaRoute::class,
                    'options' => [
                        'route' => 'documentary-evidence-main-occupation[/]',
                        'defaults' => [
                            'controller' => VehiclesDeclarationsController::class,
                            'action' => 'size',
                        ],
                    ],
                    'may_terminate' => true,
                    'child_routes' => [
                        'action' => [
                            'type' => Segment::class,
                            'options' => [
                                'route' => ':action[/:child_id][/]',
                            ],
                        ],
                    ],
                ],
                'main_occupation_undertakings' => [
                    'type' => LvaRoute::class,
                    'options' => [
                        'route' => 'main-occupation-undertakings[/]',
                        'defaults' => [
                            'controller' => VehiclesDeclarationsController::class,
                            'action' => 'size',
                        ],
                    ],
                    'may_terminate' => true,
                    'child_routes' => [
                        'action' => [
                            'type' => Segment::class,
                            'options' => [
                                'route' => ':action[/:child_id][/]',
                            ],
                        ],
                    ],
                ],
                'trailers' => [
                    'type' => LvaRoute::class,
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
                            'type' => Segment::class,
                            'options' => [
                                'route' => ':action[/:child_id][/]',
                            ],
                        ],
                    ],
                ],
                'discs' => [
                    'type' => LvaRoute::class,
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
                            'type' => Segment::class,
                            'options' => [
                                'route' => ':action[/:child_id][/]',
                            ],
                        ],
                    ],
                ],
                'community_licences' => [
                    'type' => LvaRoute::class,
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
                            'type' => Segment::class,
                            'options' => [
                                'route' => ':action[/:child_id][/]',
                            ],
                        ],
                    ],
                ],
                'safety' => [
                    'type' => LvaRoute::class,
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
                            'type' => Segment::class,
                            'options' => [
                                'route' => ':action[/:child_id][/]',
                            ],
                        ],
                    ],
                ],
                'conditions_undertakings' => [
                    'type' => LvaRoute::class,
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
                            'type' => Segment::class,
                            'options' => [
                                'route' => ':action[/:child_id][/]',
                            ],
                        ],
                    ],
                ],
                'financial_history' => [
                    'type' => LvaRoute::class,
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
                            'type' => Segment::class,
                            'options' => [
                                'route' => ':action[/:child_id][/]',
                            ],
                        ],
                    ],
                ],
                'licence_history' => [
                    'type' => LvaRoute::class,
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
                            'type' => Segment::class,
                            'options' => [
                                'route' => ':action[/:child_id][/]',
                            ],
                        ],
                    ],
                ],
                'convictions_penalties' => [
                    'type' => LvaRoute::class,
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
                            'type' => Segment::class,
                            'options' => [
                                'route' => ':action[/:child_id][/]',
                            ],
                        ],
                    ],
                ],
                'undertakings' => [
                    'type' => LvaRoute::class,
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
                            'type' => Segment::class,
                            'options' => [
                                'route' => ':action[/:child_id][/]',
                            ],
                        ],
                    ],
                ],
                'declarations_internal' => [
                    'type' => LvaRoute::class,
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
                            'type' => Segment::class,
                            'options' => [
                                'route' => ':action[/:child_id][/]',
                            ],
                        ],
                    ],
                ],
                'review' => [
                    'type' => Segment::class,
                    'options' => [
                        'route' => 'review[/]',
                        'defaults' => [
                            'controller' => 'Application/Review',
                            'action' => 'index',
                        ],
                    ],
                ],
                'declaration' => [
                    'type' => Segment::class,
                    'options' => [
                        'route' => 'declaration[/]',
                        'defaults' => [
                            'controller' => 'DeclarationFormController',
                            'action' => 'index',
                        ],
                    ],
                ],
                'pay-and-submit' => [
                    'type' => Segment::class,
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
                    'type' => Segment::class,
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
                    'type' => Segment::class,
                    'options' => [
                        'route' => 'submission-summary[/]',
                        'defaults' => [
                            'controller' => 'Application/Summary',
                            'action' => 'postSubmitSummary',
                        ],
                    ],
                ],
                'upload-evidence' => [
                    'type' => Segment::class,
                    'options' => [
                        'route' => 'upload-evidence[/]',
                        'defaults' => [
                            'controller' => 'Application/UploadEvidence',
                            'action' => 'index',
                        ],
                    ],
                ],
                'summary' => [
                    'type' => Segment::class,
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
                    'type' => Segment::class,
                    'options' => [
                        'route' => 'result[/]',
                        'defaults' => [
                            'controller' => 'Application/PaymentSubmission',
                            'action' => 'payment-result',

                        ],
                    ],
                ],
                'cancel' => [
                    'type' => Segment::class,
                    'options' => [
                        'route' => 'cancel[/]',
                        'defaults' => [
                            'controller' => 'Application',
                            'action' => 'cancel',
                        ],
                    ],
                ],
                'withdraw' => [
                    'type' => Segment::class,
                    'options' => [
                        'route' => 'withdraw[/]',
                        'defaults' => [
                            'controller' => 'Application',
                            'action' => 'withdraw',
                        ],
                    ],
                ],
                'transport_manager_details' => [
                    'type' => Segment::class,
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
                            'type' => Segment::class,
                            'options' => [
                                'route' => ':action[/:grand_child_id][/]',
                                'constraints' => ['grand_child_id' => '[0-9\\,]+',],
                            ],
                        ],
                    ],
                ],
                'transport_manager_check_answer' => [
                    'type' => Segment::class,
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
                            'type' => Segment::class,
                            'options' => [
                                'route' => ':action[/:grand_child_id][/]',
                                'constraints' => ['grand_child_id' => '[0-9\\,]+',],
                            ],
                        ],
                    ],
                ],
                'transport_manager_tm_declaration' => [
                    'type' => Segment::class,
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
                    'type' => Segment::class,
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
                    'type' => Segment::class,
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
