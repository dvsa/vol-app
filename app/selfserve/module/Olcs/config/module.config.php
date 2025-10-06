<?php

use Common\Service\Data as CommonDataService;
use Common\Util\LvaRoute;
use Laminas\Router\Http\Segment;
use Olcs\Auth\Adapter\CommandAdapter;
use Olcs\Auth\Adapter\CommandAdapterFactory;
use Olcs\Auth\Adapter\SelfserveCommandAdapter;
use Olcs\Auth\Adapter\SelfserveCommandAdapterFactory;
use Olcs\Auth\Service\AuthenticationServiceFactory;
use Olcs\Auth\Service\AuthenticationServiceInterface;
use Olcs\Controller\ConsultantRegistrationController;
use Olcs\Controller\Cookie\DetailsController as CookieDetailsController;
use Olcs\Controller\Cookie\DetailsControllerFactory;
use Olcs\Controller\Cookie\SettingsController as CookieSettingsController;
use Olcs\Controller\Cookie\SettingsControllerFactory as CookieSettingsControllerFactory;
use Olcs\Controller\Factory\BusReg\BusRegRegistrationsControllerFactory;
use Olcs\Controller\Factory\CorrespondenceControllerFactory;
use Olcs\Controller\Factory\Ebsr\UploadsControllerFactory;
use Olcs\Controller\Factory\IndexControllerFactory;
use Olcs\Controller\Factory\MyDetailsControllerFactory;
use Olcs\Controller\Factory\UserRegistrationControllerToggleAwareFactory;
use Olcs\Controller\IndexController;
use Olcs\Controller\Licence\Vehicle\ListVehicleController;
use Olcs\Controller\Lva\Adapters\ApplicationPeopleAdapter;
use Olcs\Controller\Lva\Adapters\LicencePeopleAdapter;
use Olcs\Controller\Lva\Adapters\LicenceTransportManagerAdapter;
use Olcs\Controller\Lva\Adapters\VariationPeopleAdapter;
use Olcs\Controller\Lva\Adapters\VariationTransportManagerAdapter;
use Olcs\Controller\Lva\DirectorChange as LvaDirectorChangeControllers;
use Olcs\Controller\Lva\Factory\Adapter\ApplicationPeopleAdapterFactory;
use Olcs\Controller\Lva\Factory\Adapter\LicencePeopleAdapterFactory;
use Olcs\Controller\Lva\Factory\Adapter\LicenceTransportManagerAdapterFactory;
use Olcs\Controller\Lva\Factory\Adapter\VariationPeopleAdapterFactory;
use Olcs\Controller\Lva\Factory\Adapter\VariationTransportManagerAdapterFactory;
use Olcs\Controller\Lva\Factory\Controller\DirectorChange as LvaDirectorChangeControllerFactories;
use Olcs\Controller\Lva\Factory\Controller\Licence as LvaLicenceControllerFactories;
use Olcs\Controller\Lva\Factory\Controller\TransportManager as LvaTransportManagerControllerFactories;
use Olcs\Controller\Lva\Factory\Controller\Variation as LvaVariationControllerFactories;
use Olcs\Controller\Lva\Licence as LvaLicenceControllers;
use Olcs\Controller\Lva\TransportManager as LvaTransportManagerControllers;
use Olcs\Controller\Lva\Variation as LvaVariationControllers;
use Olcs\Controller\Lva\Variation\VehiclesDeclarationsController;
use Olcs\Controller\MyDetailsController;
use Olcs\Controller\OperatorRegistrationController;
use Olcs\Controller\PromptController;
use Olcs\Controller\Search\SearchController;
use Olcs\Controller\SessionTimeoutController;
use Olcs\Controller\UserForgotUsernameController;
use Olcs\Controller\UserRegistrationController;
use Olcs\Controller\WelcomeController;
use Olcs\Form\Element\SearchDateRangeFieldset;
use Olcs\Form\Element\SearchDateRangeFieldsetFactory;
use Olcs\Form\Element\SearchFilterFieldset;
use Olcs\Form\Element\SearchFilterFieldsetFactory;
use Olcs\Form\Element\SearchOrderFieldset;
use Olcs\Form\Element\SearchOrderFieldsetFactory;
use Olcs\FormService\Form\Lva as LvaFormService;
use Olcs\Logging\Log\Processor\CorrelationId;
use Olcs\Logging\Log\Processor\CorrelationIdFactory;
use Olcs\Mvc\TermsAgreedListener;
use Olcs\Mvc\TermsAgreedListenerFactory;
use Olcs\Service\Cookie as CookieService;
use Olcs\Service\Data as DataService;
use Olcs\Service\Processing as ProcessingService;
use Olcs\Service\Qa as QaService;
use Olcs\Session\LicenceVehicleManagement;

$sectionConfig = new \Common\Service\Data\SectionConfig();
$configRoutes = $sectionConfig->getAllRoutes();

// We no longer want to generate application routes. Instead they will be defined within the application module.
unset($configRoutes['lva-application']);

$sections = $sectionConfig->getAllReferences();
$applicationDetailsPages = [];
$licenceDetailsPages = [];
$variationDetailsPages = [];

foreach ($sections as $section) {
    $applicationDetailsPages['application_' . $section] = [
        'id' => 'application_' . $section,
        'label' => 'section.name.' . $section,
        'route' => 'lva-application/' . $section,
        'params' => ['action' => 'index'],
        'use_route_match' => true
    ];

    $licenceDetailsPages['licence_' . $section] = [
        'id' => 'licence_' . $section,
        'label' => 'section.name.' . $section,
        'route' => 'lva-licence/' . $section,
        'params' => ['action' => 'index'],
        'use_route_match' => true
    ];

    $variationDetailsPages['variation_' . $section] = [
        'id' => 'variation_' . $section,
        'label' => 'section.name.' . $section,
        'route' => 'lva-variation/' . $section,
        'params' => ['action' => 'index'],
        'use_route_match' => true
    ];
}

$routes = [
    'index' => [
        'type' => 'literal',
        'options' =>  [
            'route' => '/',
            'defaults' => [
                'controller' => IndexController::class,
                'action' => 'index'
            ]
        ]
    ],
    'welcome' => [
        'type' => Segment::class,
        'options' =>  [
            'route' => '/welcome[/]',
            'defaults' => [
                'controller' => WelcomeController::class,
                'action' => 'generic',
            ],
        ],
    ],
    'cookies' => [
        'type' => 'segment',
        'options' =>  [
            'route' => '/cookies[/]',
            'defaults' => [
                'controller' => CookieDetailsController::class,
                'action' => 'generic',
            ]
        ],
        'may_terminate' => true,
        'child_routes' => [
            'settings' => [
                'type' => Segment::class,
                'options' =>  [
                    'route' => 'settings[/]',
                    'defaults' => [
                        'controller' => CookieSettingsController::class,
                        'action' => 'generic',
                    ],

                ],
            ],
        ],
    ],
    'privacy-notice' => [
        'type' => 'segment',
        'options' =>  [
            'route' => '/privacy-notice[/]',
            'defaults' => [
                'controller' => \Common\Controller\GuidesController::class,
                'action' => 'index',
                'guide' => 'privacy-notice',
            ]
        ]
    ],
    'terms-and-conditions' => [
        'type' => 'segment',
        'options' =>  [
            'route' => '/terms-and-conditions[/]',
            'defaults' => [
                'controller' => \Common\Controller\GuidesController::class,
                'action' => 'index',
                'guide' => 'terms-and-conditions',
            ]
        ]
    ],
    'accessibility-statement' => [
        'type' => 'segment',
        'options' =>  [
            'route' => '/accessibility-statement[/]',
            'defaults' => [
                'controller' => \Common\Controller\GuidesController::class,
                'action' => 'index',
                'guide' => 'accessibility-statement',
            ]
        ]
    ],
    'right-first-time' => [
        'type' => 'segment',
        'options' =>  [
            'route' => '/are-you-ready[/]',
            'defaults' => [
                'controller' => \Common\Controller\GuidesController::class,
                'action' => 'index',
                'guide' => \Common\Controller\GuidesController::GUIDE_RIGHT_FIRST_TIME,
            ]
        ]
    ],
    'main-occupation-criteria-guidance' => [
        'type' => 'segment',
        'options' =>  [
            'route' => '/main-occupation-criteria-guidance[/]',
            'defaults' => [
                'controller' => \Common\Controller\GuidesController::class,
                'action' => 'index',
                'guide' => \Common\Controller\GuidesController::MAIN_OCCUPATION_CRITERIA_GUIDANCE,
            ]
        ]
    ],
    //  search result page with filter and table of results
    'search' => [
        'type' => 'segment',
        'options' =>  [
            'route' => '/search[/:index][/:action][/]',
            'defaults' => [
                'controller' => SearchController::class,
                'action' => 'index',
            ],
            'constraints' => [
                'index' => '(bus|operator|operating-centre|person|publication|vehicle-external)',
                'action' => '(index|search)'
            ]
        ]
    ],
    // Unfortunately, we need separate routes
    'search-operator' => [
        'class' => 'govuk-link',
        'type' => 'segment',
        'options' =>  [
            'route' => '/search/find-lorry-bus-operators[/]',
            'defaults' => [
                'controller' => SearchController::class,
                'action' => 'index',
                'index' => 'operator'
            ]
        ]
    ],
    'search-bus' => [
        'type' => Segment::class,
        'options' =>  [
            'route' => '/search/find-registered-local-bus-services[/]',
            'defaults' => [
                'controller' => SearchController::class,
                'action' => 'index',
                'index' => 'bus',
            ],
        ],
        'may_terminate' => true,
        'child_routes' => [
            'browse' => [
                'type' => 'segment',
                'options' =>  [
                    'route' => 'browse[/]',
                    'defaults' => [
                        'controller' => Olcs\Controller\BusReg\BusRegBrowseController::class,
                        'action' => 'index',
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'results' => [
                        'type' => 'segment',
                        'options' =>  [
                            'route' => 'results[/]',
                            'defaults' => [
                                'action' => 'results',
                            ],
                        ]
                    ],
                ],
            ],
            'details' => [
                'type' => Segment::class,
                'options' =>  [
                    'route' => 'details/:busRegId[/]',
                    'defaults' => [
                        'controller' => Olcs\Controller\Ebsr\BusRegApplicationsController::class,
                        'action' => 'searchDetails',
                    ],
                    'constraints' => [
                        'busRegId' => '[0-9]+',
                    ],
                ],
            ],
        ],
    ],
    'search-publication' => [
        'type' => 'segment',
        'options' =>  [
            'route' => '/search/check-vehicle-operator-decisions-applications[/]',
            'defaults' => [
                'controller' => SearchController::class,
                'action' => 'index',
                'index' => 'publication'
            ]
        ]
    ],
    'search-vehicle-external' => [
        'type' => 'segment',
        'options' =>  [
            'route' => '/search/find-vehicles[/]',
            'defaults' => [
                'controller' => SearchController::class,
                'action' => 'index',
                'index' => 'vehicle-external'
            ]
        ]
    ],
    'busreg-registrations' => [
        'type' => 'segment',
        'options' =>  [
            'route' => '/busreg-registrations[/]',
            'defaults' => [
                'controller' => Olcs\Controller\BusReg\BusRegRegistrationsController::class,
                'action' => 'index',
            ]
        ]
    ],
    'bus-registration' => [
        'type' => 'segment',
        'options' =>  [
            'route' => '/bus-registration[/]',
            'defaults' => [
                'controller' => Olcs\Controller\Ebsr\BusRegApplicationsController::class,
                'action' => 'index',
            ]
        ],
        'may_terminate' => true,
        'child_routes' => [
            'details' => [
                'type' => 'segment',
                'options' =>  [
                    'route' => 'details[/busreg/:busRegId][/]',
                    'defaults' => [
                        'action' => 'details',
                    ],
                    'constraints' => [
                        'busRegId' => '[0-9]+',
                        'subType' => '[a-z_]+',
                        'status' => '[a-z_]+',
                        'page' => '[0-9]+',
                    ]
                ]
            ],
            'ebsr' => [
                'type' => 'segment',
                'options' =>  [
                    'route' => 'ebsr[/:action][/:id][/]',
                    'defaults' => [
                        'controller' => 'Olcs\Ebsr\Uploads',
                        'action' => 'upload'
                    ],
                    'constraints' => [
                        'action' => '(upload|detail)'
                    ]
                ]
            ],
        ]
    ],
    'dashboard' => [
        'type' => 'segment',
        'options' => [
            'route' => '/dashboard[/]',
            'defaults' => [
                'controller' => 'Dashboard',
                'action' => 'index'
            ]
        ],
        'may_terminate' => true,
        'child_routes' => [
            'topsreport' => [
                'type' => 'segment',
                'options' => [
                    'route' => 'topsreport',
                    'defaults' => [
                        'action' => 'topsreport',
                    ],
                ]
            ]
        ]
    ],
    'prompt' => [
        'type' => 'segment',
        'options' =>  [
            'route' => '/prompt[/]',
            'defaults' => [
                'controller' => PromptController::class,
                'action' => 'generic',
            ],
        ],
    ],
    'fees' => [
        'type' => 'segment',
        'options' => [
            'route' => '/fees[/]',
            'defaults' => [
                'controller' => Olcs\Controller\FeesController::class,
                'action' => 'index',
            ],
        ],
        'may_terminate' => true,
        'child_routes' => [
            'pay' => [
                'type' => 'segment',
                'options' => [
                    'route' => 'pay/:fee[/]',
                    'constraints' => [
                        'fee' => '[0-9\,]+',
                    ],
                    'defaults' => [
                        'action' => 'pay-fees',
                    ],
                ],
            ],
            'result' => [
                'type' => 'segment',
                'options' => [
                    'route' => 'result[/]',
                    'defaults' => [
                        'action' => 'handle-result',
                    ],
                ],
            ],
            'receipt' => [
                'type' => 'segment',
                'options' => [
                    'route' => 'receipt/:reference[/:action][/]',
                    'constraints' => [
                        'reference' => '[0-9A-Za-z]+-[0-9A-F\-]+',
                    ],
                    'defaults' => [
                        'action' => 'receipt',
                    ],
                ],
            ],
            'late' => [
                'type' => 'segment',
                'options' => [
                    'route' => 'late/:fee[/]',
                    'constraints' => [
                        'fee' => '[0-9\,]+',
                    ],
                    'defaults' => [
                        'action' => 'late-fee',
                    ],
                ],
            ],
        ],
    ],
    'correspondence' => [
        'type' => 'segment',
        'options' => [
            'route' => '/correspondence[/]',
            'defaults' => [
                'controller' => Olcs\Controller\CorrespondenceController::class,
                'action' => 'index'
            ]
        ],
        'may_terminate' => true,
        'child_routes' => [
            'access' => [
                'type' => 'segment',
                'options' => [
                    'route' => 'access/:correspondenceId[/]',
                    'defaults' => [
                        'action' => 'accessCorrespondence',
                    ],
                ]
            ]
        ]
    ],
    'conversations' => [
        'type' => 'segment',
        'options' => [
            'route' => '/conversations[/]',
            'defaults' => [
                'controller' => Olcs\Controller\ConversationsController::class,
                'action' => 'index'
            ]
        ],
        'may_terminate' => true,
        'child_routes' => [
            'view' => [
                'type' => 'segment',
                'options' => [
                    'route' => ':conversationId[/]',
                    'defaults' => [
                        'action' => 'view',
                    ],
                ]
            ],
            'new' => [
                'type' => 'segment',
                'options' => [
                    'route' => 'new[/]',
                    'defaults' => [
                        'action' => 'add',
                    ],
                ]
            ],
        ]
    ],
    'create_variation' => [
        'type' => 'segment',
        'options' => [
            'route' => '/variation/create/:licence[/]',
            'constraints' => [
                'licence' => '[0-9]+',
            ],
            'defaults' => [
                'controller' => 'LvaLicence',
                'action' => 'createVariation'
            ]
        ]
    ],
    'licence-print' => [
        'type' => Segment::class,
        'options' => [
            'route' => '/licence/print/:licence[/]',
            'constraints' => [
                'licence' => '[0-9]+',
            ],
            'defaults' => [
                'controller' => 'LvaLicence',
                'action' => 'print',
            ],
        ],
    ],
    'user-registration' => [
        'type' => 'segment',
        'options' => [
            'route' => '/register[/]',
            'defaults' => [
                'controller' => UserRegistrationController::class,
                'action' => 'start'
            ]
        ],
        'may_terminate' => true,
        'child_routes' => [
            'operator' => [
                'type' => 'segment',
                'options' => [
                    'route' => 'operator[/]',
                    'defaults' => [
                        'controller' => \Olcs\Controller\Factory\UserRegistrationControllerToggleAwareFactory::class,
                        'action' => 'add',
                    ]
                ]
            ],
            'operator-representation' => [
                'type' => 'segment',
                'options' => [
                    'route' => 'operator-representation[/]',
                    'defaults' => [
                        'controller' => ConsultantRegistrationController::class,
                        'action' => 'operatorRepresentation'
                    ]
                ]
            ],
            'operator-confirm' => [
                'type' => 'segment',
                'options' => [
                    'route' => 'operator-confirm[/]',
                    'defaults' => [
                        'controller' => UserRegistrationController::class,
                        'action' => 'add'
                    ]
                ]
            ],
            'register-for-operator' => [
                'type' => 'segment',
                'options' => [
                    'route' => 'register-for-operator[/]',
                    'defaults' => [
                        'controller' => ConsultantRegistrationController::class,
                        'action' => 'registerForOperator'
                    ]
                ]
            ],
            'register-consultant-account' => [
                'type' => 'segment',
                'options' => [
                    'route' => 'register-consultant-account[/]',
                    'defaults' => [
                        'controller' => ConsultantRegistrationController::class,
                        'action' => 'registerConsultantAccount'
                    ]
                ]
            ],
            'contact-your-administrator' => [
                'type' => 'segment',
                'options' => [
                    'route' => 'contact-your-administrator[/]',
                    'defaults' => [
                        'controller' => ConsultantRegistrationController::class,
                        'action' => 'contactYourAdministrator'
                    ]
                ]
            ]
        ]
    ],
    'user-forgot-username' => [
        'type' => 'segment',
        'options' => [
            'route' => '/forgot-username[/]',
            'defaults' => [
                'controller' => UserForgotUsernameController::class,
                'action' => 'index'
            ]
        ]
    ],
    'manage-user' => [
        'type' => 'segment',
        'options' => [
            'route' => '/manage-user[/:action][/:id][/]',
            'constraints' => [
                'action' => '(index|add|edit|delete)',
                'id' => '[0-9]+',
            ],
            'defaults' => [
                'controller' => Olcs\Controller\UserController::class,
                'action' => 'index'
            ]
        ]
    ],
    'your-account' => [
        'type' => 'segment',
        'options' => [
            'route' => '/your-account[/]',
            'defaults' => [
                'controller' => MyDetailsController::class,
                'action' => 'edit'
            ]
        ]
    ],
    'entity-view' => [
        'type' => 'segment',
        'options' =>  [
            'route' => '/view-details/:entity[/:entityId][/]',
            'constraints' => [
                'entity' => '(licence)',
                'entityId' => '[0-9]+',
            ],
            'defaults' => [
                'controller' => Olcs\Controller\Entity\ViewController::class,
                'action' => 'details'
            ]
        ]
    ],
    'govuk-account' => [
        'type' => \Laminas\Router\Http\Literal::class,
        'options' => [
            'route' => '/govuk-account',
            'defaults' => [
                'controller' => Olcs\Controller\SignatureVerificationController::class,
            ]
        ],
        'may_terminate' => false,
        'child_routes' => [
            'process' => [
                'type' => \Laminas\Router\Http\Literal::class,
                'options' => [
                    'route' => '/process',
                    'defaults' => [
                        'action' => 'index',
                    ],
                ],
            ],
        ],
    ],
    'verify' => [
        'type' => \Laminas\Router\Http\Literal::class,
        'options' => [
            'route' => '/verify',
            'defaults' => [
                'controller' => Olcs\Controller\GdsVerifyController::class,
                'action' => 'index',
            ]
        ],
        'may_terminate' => false,
        'child_routes' => [
            'initiate-request' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/initiate-request[/application/:application]' .
                        '[/continuation-detail/:continuationDetailId][/]',
                    'defaults' => [
                        'action' => 'initiate-request',
                    ],
                ]
            ],
            'transport-manager' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/:lva/:applicationId/transport-manager/:transportManagerApplicationId/:role[/]',
                    'defaults' => [
                        'action' => 'initiate-request',
                    ],
                    'constraints' => [
                            'lva' => '(application|variation)',
                            'applicationId' => '[0-9]+',
                            'transportManagerApplicationId' => '[0-9]+',
                            'role' => '(tma\\_sign\\_as\\_tm|tma\\_sign\\_as\\_op|tma\\_sign\\_as\\_top)'
                    ],
                ]
            ],
            'surrender' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/surrender/:licenceId[/]',
                    'defaults' => [
                        'action' => 'initiate-request',
                    ],
                    'constraints' => [
                        'licenceId' => '[0-9]+',
                    ],
                ]
            ],
            'process-response' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/process-response[/]',
                    'defaults' => [
                        'action' => 'process-response',
                    ],
                ],
            ],
            'process-signature' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/process-signature[/]',
                    'defaults' => [
                        'action' => 'process-signature',
                    ],
                ]
            ],
        ]
    ],
    'session-timeout' => [
        'type' => 'segment',
        'options' =>  [
            'route' => '/auth/timeout[/]',
            'defaults' => [
                'controller' => SessionTimeoutController::class,
                'action' => 'index',
            ]
        ]
    ],
];

$files = glob(__DIR__ . '/selfserve-routes/*.php');

foreach ($files as $config) {
    $newRoute = include $config;
    $otherSelfserveRoutes = current($newRoute);
    $routes = array_merge($routes, $otherSelfserveRoutes);
}

//$routes = array_merge($routes, $otherSelfserveRoutes);

$configRoutes['lva-variation']['child_routes'] = array_merge(
    $configRoutes['lva-variation']['child_routes'],
    [
        'vehicles_size' => [
            'type' => LvaRoute::class,
            'options' => [
                'route' => 'vehicles-size[/]',
                'defaults' => [
                    'controller' => VehiclesDeclarationsController::class,
                    'action' => 'size',
                ],
            ],
        ],
        'psv_operate_large' => [
            'type' => LvaRoute::class,
            'options' => [
                'route' => 'psv-operate-large[/]',
                'defaults' => [
                    'controller' => VehiclesDeclarationsController::class,
                    'action' => 'operateLarge',
                ],
            ],
        ],
        'psv_operate_small' => [
            'type' => LvaRoute::class,
            'options' => [
                'route' => 'psv-operate-small[/]',
                'defaults' => [
                    'controller' => VehiclesDeclarationsController::class,
                    'action' => 'operateSmall',
                ],
            ],
        ],
        'psv_small_part_written' => [
            'type' => LvaRoute::class,
            'options' => [
                'route' => 'psv-small-part-written[/]',
                'defaults' => [
                    'controller' => VehiclesDeclarationsController::class,
                    'action' => 'writtenExplanation',
                ],
            ],
        ],
        'psv_small_conditions' => [
            'type' => LvaRoute::class,
            'options' => [
                'route' => 'psv-small-conditions[/]',
                'defaults' => [
                    'controller' => VehiclesDeclarationsController::class,
                    'action' => 'smallConditions',
                ],
            ],
        ],
        'psv_operate_novelty' => [
            'type' => LvaRoute::class,
            'options' => [
                'route' => 'psv-operate-novelty[/]',
                'defaults' => [
                    'controller' => VehiclesDeclarationsController::class,
                    'action' => 'novelty',
                ],
            ],
        ],
        'psv_documentary_evidence_small' => [
            'type' => LvaRoute::class,
            'options' => [
                'route' => 'psv-documentary-evidence-small[/]',
                'defaults' => [
                    'controller' => VehiclesDeclarationsController::class,
                    'action' => 'smallEvidence',
                ],
            ],
        ],
        'psv_documentary_evidence_large' => [
            'type' => LvaRoute::class,
            'options' => [
                'route' => 'psv-documentary-evidence-large[/]',
                'defaults' => [
                    'controller' => VehiclesDeclarationsController::class,
                    'action' => 'largeEvidence',
                ],
            ],
        ],
        'psv_main_occupation_undertakings' => [
            'type' => LvaRoute::class,
            'options' => [
                'route' => 'psv-main-occupation-undertakings[/]',
                'defaults' => [
                    'controller' => VehiclesDeclarationsController::class,
                    'action' => 'mainOccupation',
                ],
            ],
        ],
        'review' => [
            'type' => 'segment',
            'options' => [
                'route' => 'review[/]',
                'defaults' => [
                    'controller' => 'LvaVariation/Review',
                    'action' => 'index'
                ]
            ]
        ],
        'submission-summary' => [
            'type' => 'segment',
            'options' => [
                'route' => 'submission-summary[/]',
                'defaults' => [
                    'controller' => 'LvaVariation/Summary',
                    'action' => 'postSubmitSummary'
                ]
            ]
        ],
        'upload-evidence' => [
            'type' => 'segment',
            'options' => [
                'route' => 'upload-evidence[/]',
                'defaults' => [
                    'controller' => 'LvaVariation/UploadEvidence',
                    'action' => 'index'
                ]
            ]
        ],
        'summary' => [
            'type' => 'segment',
            'options' => [
                'route' => 'summary[/:reference][/]',
                'constraints' => [
                    'reference' => '[0-9A-Za-z]+-[0-9A-F\-]+',
                ],
                'defaults' => [
                    'controller' => 'LvaVariation/Summary',
                    'action' => 'index'
                ]
            ]
        ],
        'pay-and-submit' => [
            'type' => 'segment',
            'options' => [
                'route' => 'pay-and-submit[/:redirect-back][/]',
                'defaults' => [
                    'controller' => 'LvaVariation/PaymentSubmission',
                    'action' => 'payAndSubmit',
                    'redirect-back' => 'overview',
                ],
                'constraints' => [
                    'redirect-back' => '[a-z\-]+',
                ],
            ]
        ],
        'payment' => [
            'type' => 'segment',
            'options' => [
                'route' => 'payment[/]',
                'defaults' => [
                    'controller' => 'LvaVariation/PaymentSubmission',
                    'action' => 'index'
                ],
            ]
        ],
        'result' => [
            'type' => 'segment',
            'options' => [
                'route' => 'result[/]',
                'defaults' => [
                    'controller' => 'LvaVariation/PaymentSubmission',
                    'action' => 'payment-result',

                ]
            ]
        ],
        'cancel' => [
            'type' => 'segment',
            'options' => [
                'route' => 'cancel[/]',
                'defaults' => [
                    'controller' => 'LvaVariation',
                    'action' => 'cancel'
                ]
            ]
        ],
        'withdraw' => [
            'type' => 'segment',
            'options' => [
                'route' => 'withdraw[/]',
                'defaults' => [
                    'controller' => 'LvaVariation',
                    'action' => 'withdraw'
                ]
            ]
        ],
    ]
);

$configRoutes['lva-licence']['child_routes'] = array_merge(
    $configRoutes['lva-licence']['child_routes'],
    [
        'variation' => [
            'type' => 'segment',
            'options' => [
                'route' => 'variation[/:redirectRoute][/]',
                'defaults' => [
                    'controller' => 'LvaLicence/Variation',
                    'action' => 'index'
                ]
            ]
        ]
    ]
);

foreach (['variation'] as $lva) {
    $configRoutes['lva-' . $lva]['child_routes'] = array_merge(
        $configRoutes['lva-' . $lva]['child_routes'],
        [
            'transport_manager_details' => [
                'type' => 'segment',
                'options' => [
                    'route' => 'transport-managers/details/:child_id[/]',
                    'constraints' => [
                        'child_id' => '[0-9]+',
                        'grand_child_id' => '[0-9]+'
                    ],
                    'defaults' => [
                        'controller' => 'Lva' . ucfirst($lva) . '/TransportManagers',
                        'action' => 'details'
                    ]
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'action' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => ':action[/:grand_child_id][/]',
                            'constraints' => [
                                'grand_child_id' => '[0-9\,]+'
                            ],
                        ]
                    ],
                ]
            ],
            'transport_manager_check_answer' => [
                'type' => 'segment',
                'options' => [
                    'route' => 'transport-managers/check-answer/:child_id[/]',
                    'constraints' => [
                        'child_id' => '[0-9]+',
                        'grand_child_id' => '[0-9]+'
                    ],
                    'defaults' => [
                        'controller' => 'LvaTransportManager/CheckAnswers',
                        'action' => 'index'
                    ]
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'action' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => ':action[/:grand_child_id][/]',
                            'constraints' => [
                                'grand_child_id' => '[0-9\,]+'
                            ],
                        ]
                    ],
                ],
            ],
            'transport_manager_tm_declaration' => [
                'type' => 'segment',
                'options' => [
                    'route' => 'transport-managers/tm-declaration/:child_id[/]',
                    'constraints' => [
                        'child_id' => '[0-9]+',
                        'grand_child_id' => '[0-9]+'
                    ],
                    'defaults' => [
                        'controller' => 'LvaTransportManager/TmDeclaration',
                        'action' => 'index'
                    ]
                ],
                'may_terminate' => true,
            ],
            'transport_manager_operator_declaration' => [
                'type' => 'segment',
                'options' => [
                    'route' => 'transport-managers/operator-declaration/:child_id[/]',
                    'constraints' => [
                        'child_id' => '[0-9]+',
                        'grand_child_id' => '[0-9]+'
                    ],
                    'defaults' => [
                        'controller' => 'LvaTransportManager/OperatorDeclaration',
                        'action' => 'index'
                    ]
                ],
                'may_terminate' => true,
            ],
            'transport_manager_confirmation' => [
                'type' => 'segment',
                'options' => [
                    'route' => 'transport-managers/confirmation/:child_id[/]',
                    'constraints' => [
                        'child_id' => '[0-9]+',
                        'grand_child_id' => '[0-9]+'
                    ],
                    'defaults' => [
                        'controller' => 'LvaTransportManager/Confirmation',
                        'action' => 'index'
                    ]
                ],
                'may_terminate' => true,
            ]
        ]
    );

    ${$lva . 'DetailsPages'}[$lva . '_transport_managers']['pages'] = [
        [
            'id' => $lva . '_transport_managers_details',
            'label' => 'section.name.transport_managers.details',
            'route' => 'lva-' . $lva . '/transport_manager_details',
            'pages' => [
                [
                    'id' => $lva . '_transport_managers_details_action',
                    'label' => 'section.name.transport_managers.details.action',
                    'route' => 'lva-' . $lva . '/transport_manager_details/action',
                    'use_route_match' => true
                ]
            ],
            'use_route_match' => true
        ]
    ];

    ${$lva . 'DetailsPages'}[$lva . '_pay-and-submit'] = [
        'id' => $lva . '_pay-and-submit',
        'route' => 'lva-' . $lva . '/pay-and-submit',
        'use_route_match' => true,
    ];
}

//  split route to main and CRUD action routes
foreach (['licence', 'application', 'variation'] as $lva) {
    //  add to  navigation config
    $tmpRoutes = [
        'business_details',
        'people',
        'operating_centres',
        'vehicles',
        'vehicles_psv',
        'trailers',
        'safety',
        'licence_history',
        'convictions_penalties',
        'transport_managers',
    ];

    foreach ($tmpRoutes as $route) {
        ${$lva . 'DetailsPages'}[$lva . '_' . $route]['pages'][] = [
            'id' => $lva . '_' . $route . '_action',
            'route' => 'lva-' . $lva . '/' . $route . '/action',
            'use_route_match' => true,
        ];
    }
}

$applicationNavigation = [
    'id' => 'dashboard-applications',
    'label' => 'Home',
    'route' => 'dashboard',
    'class' => 'proposition-nav__item',
    'pages' => [
        [
            'id' => 'application-summary',
            'label' => 'Application summary',
            'route' => 'lva-application/summary',
            'use_route_match' => true
        ],
        [
            'id' => 'application-submission-summary',
            'label' => 'Application summary',
            'route' => 'lva-application/submission-summary',
            'use_route_match' => true,
            'pages' => [
                [
                    'id' => 'application-upload-evidence',
                    'label' => 'Upload evidence',
                    'route' => 'lva-application/upload-evidence',
                    'use_route_match' => true
                ],
            ],
        ],
        [
            'id' => 'application',
            'label' => 'Application overview',
            'route' => 'lva-application',
            'use_route_match' => true,
            'pages' => $applicationDetailsPages
        ],
        [
            'id' => 'licence',
            'label' => 'Licence overview',
            'route' => 'lva-licence',
            'use_route_match' => true,
            'pages' => $licenceDetailsPages
        ],
        [
            'id' => 'variation-summary',
            'label' => 'Application summary',
            'route' => 'lva-variation/summary',
            'use_route_match' => true
        ],
        [
            'id' => 'variation-submission-summary',
            'label' => 'Application summary',
            'route' => 'lva-variation/submission-summary',
            'use_route_match' => true,
            'pages' => [
                [
                    'id' => 'variation-upload-evidence',
                    'label' => 'Upload evidence',
                    'route' => 'lva-variation/upload-evidence',
                    'use_route_match' => true
                ],
            ],
        ],
        [
            'id' => 'variation',
            'label' => 'Application overview',
            'route' => 'lva-variation',
            'use_route_match' => true,
            'pages' => $variationDetailsPages
        ],
        // Duplicate entry for TM page, corrects the breadcrumb when the user only has access to
        // lva-tm page
        [
            'id' => 'application_transport_managers_details',
            'label' => 'section.name.transport_managers.details',
            'route' => 'lva-application/transport_manager_details',
            'pages' => [
                [
                    'id' => 'application_transport_managers_details_action',
                    'label' => 'section.name.transport_managers.details.action',
                    'route' => 'lva-application/transport_manager_details/action',
                    'use_route_match' => true
                ]
            ],
            'use_route_match' => true
        ],
        [
            'id' => 'variation_transport_managers_details',
            'label' => 'section.name.transport_managers.details',
            'route' => 'lva-variation/transport_manager_details',
            'pages' => [
                [
                    'id' => 'variation_transport_managers_details_action',
                    'label' => 'section.name.transport_managers.details.action',
                    'route' => 'lva-variation/transport_manager_details/action',
                    'use_route_match' => true
                ]
            ],
            'use_route_match' => true
        ],
        [
            'id' => 'dashboard-licences-applications',
            'label' => 'Licences / Applications',
            'route' => 'dashboard',
            'class' => 'proposition-nav__item',
            'pages' => [
                // dashboard tabs
                [
                    'id' => 'dashboard-licences',
                    'label' => 'dashboard-nav-licences',
                    'route' => 'dashboard',
                ],
                [
                    'id' => 'dashboard-permits',
                    'label' => 'dashboard-nav-permits',
                    'route' => 'permits',
                ],
                [
                    'id' => 'dashboard-fees',
                    'label' => 'dashboard-nav-fees',
                    'route' => 'fees',
                    'pages' => [
                        [
                            'id' => 'pay-fees',
                            'label' => 'Pay',
                            'route' => 'fees/pay',
                        ],
                        [
                            'id' => 'pay-fees-receipt',
                            'label' => 'Pay',
                            'route' => 'fees/receipt',
                        ],
                    ],
                ],
                [
                    'id' => 'dashboard-correspondence',
                    'label' => 'dashboard-nav-documents',
                    'route' => 'correspondence',
                ],
                [
                    'id' => 'dashboard-messaging',
                    'label' => 'dashboard-nav-messaging',
                    'route' => 'conversations',
                    'pages' => [
                        [
                            'id' => 'messaging-create-conversation',
                            'label' => 'New Conversation',
                            'route' => 'conversations/new',
                        ],
                    ],
                ],
            ],
        ],
    ],
];

$busRegNav = [
    'id' => 'bus-registration',
    'label' => 'Bus registrations',
    'route' => 'bus-registration',
    'action' => 'index',
    'pages' => [
        [
            'id' => 'bus-registration-details',
            'label' => 'Details',
            'route' => 'bus-registration/details',
            'action' => 'details',
            'use_route_match' => true
        ],
        [
            'id' => 'bus-registration-ebsr',
            'label' => 'EBSR',
            'route' => 'bus-registration/ebsr',
            'use_route_match' => true
        ]
    ]
];

$searchNavigation = [
    'id' => 'search',
    'label' => 'search',
    'route' => 'search',
    'class' => 'proposition-nav__item',
    'pages' => [
        // --

        [
            'id' => 'search-operator',
            'label' => 'search-list-operator',
            'route' => 'search-operator',
            'use_route_match' => true,
            'class' => 'govuk-link',
        ],
        [
            'id' => 'search-publication',
            'label' => 'search-list-publications',
            'route' => 'search-publication',
            'use_route_match' => true,
            'class' => 'govuk-link',
        ],
        [
            'id' => 'search-bus',
            'label' => 'search-list-bus-registrations',
            'route' => 'search-bus',
            'use_route_match' => true,
            'class' => 'govuk-link',
        ],
        [
            'id' => 'search-vehicle-external',
            'label' => 'search-list-vehicles',
            'route' => 'search-vehicle-external',
            'use_route_match' => true,
            'class' => 'govuk-link',
        ]
    ]
];

$busRegSearchTabs = [
    'id' => 'search-bus-tabs',
    'label' => 'search',
    'route' => 'search-bus',
    'pages' => [
        // bus search tabs
        [
            'id' => 'search-bus',
            'label' => 'search',
            'route' => 'search-bus',
            'use_route_match' => true,
            'class' => 'search-navigation__item',
        ],
        [
            'id' => 'search-bus-browse',
            'label' => 'view',
            'route' => 'search-bus/browse',
        ],
    ],
];

$myAccountNav = [
    'id' => 'your-account',
    'label' => 'selfserve-dashboard-topnav-your-account',
    'route' => 'your-account',
    'action' => 'edit',
    'pages' => [
        [
            'id' => 'change-password',
            'label' => 'Change password',
            'route' => 'change-password',
            'action' => 'index',
            'use_route_match' => true,
        ],
    ]
];

// Add static assets redirect route for html hardcoded snapshot asset paths
$routes['static-assets'] = [
    'type' => 'segment',
    'options' => [
        'route' => '/static/public[/:path]',
        'constraints' => [
            'path' => '.*',
        ],
        'defaults' => [
            'controller' => \Olcs\Controller\StaticAssetsController::class,
            'action' => 'redirect',
        ],
    ],
];

$routes['styles-assets'] = [
    'type' => 'segment',
    'options' => [
        'route' => '/styles/:path',
        'constraints' => [
            'path' => '.+',
        ],
        'defaults' => [
            'controller' => \Olcs\Controller\StaticAssetsController::class,
            'action' => 'redirect',
            'prefix' => 'styles',
        ],
    ],
];

return [
    'router' => [
        'routes' => array_merge($routes, $configRoutes),
    ],
    'controllers' => [
        'initializers' => [
            Olcs\Controller\Initializer\Navigation::class
        ],
        'lva_controllers' => [
            'LvaLicence'                            => Olcs\Controller\Lva\Licence\OverviewController::class,
            'LvaLicence/Variation'                  => \Olcs\Controller\Lva\Licence\VariationController::class,
            'LvaLicence/TypeOfLicence'              => \Olcs\Controller\Lva\Licence\TypeOfLicenceController::class,
            'LvaLicence/BusinessType'               => \Olcs\Controller\Lva\Licence\BusinessTypeController::class,
            'LvaLicence/BusinessDetails'            => \Olcs\Controller\Lva\Licence\BusinessDetailsController::class,
            'LvaLicence/Addresses'                  => \Olcs\Controller\Lva\Licence\AddressesController::class,
            'LvaLicence/People'                     => \Olcs\Controller\Lva\Licence\PeopleController::class,
            'LvaLicence/OperatingCentres'           => \Olcs\Controller\Lva\Licence\OperatingCentresController::class,
            'LvaLicence/TransportManagers'          => Olcs\Controller\Lva\Licence\TransportManagersController::class,
            'LvaLicence/Vehicles'                   => \Olcs\Controller\Lva\Licence\VehiclesController::class,
            'LvaLicence/VehiclesPsv'                => \Olcs\Controller\Lva\Licence\VehiclesPsvController::class,
            'LvaLicence/Trailers'                   => \Olcs\Controller\Lva\Licence\TrailersController::class,
            'LvaLicence/Safety'                     => \Olcs\Controller\Lva\Licence\SafetyController::class,
            'LvaLicence/TaxiPhv'                    => \Olcs\Controller\Lva\Licence\TaxiPhvController::class,
            'LvaLicence/Discs'                      => \Olcs\Controller\Lva\Licence\DiscsController::class,
            'LvaLicence/ConditionsUndertakings'     => \Olcs\Controller\Lva\Licence\ConditionsUndertakingsController::class,
            'LvaVariation'                          => \Olcs\Controller\Lva\Variation\OverviewController::class,
            'LvaVariation/TypeOfLicence'            => \Olcs\Controller\Lva\Variation\TypeOfLicenceController::class,
            'LvaVariation/BusinessType'             => \Olcs\Controller\Lva\Variation\BusinessTypeController::class,
            'LvaVariation/BusinessDetails'          => \Olcs\Controller\Lva\Variation\BusinessDetailsController::class,
            'LvaVariation/Addresses'                => \Olcs\Controller\Lva\Variation\AddressesController::class,
            'LvaVariation/People'                   => \Olcs\Controller\Lva\Variation\PeopleController::class,
            'LvaVariation/OperatingCentres'         => \Olcs\Controller\Lva\Variation\OperatingCentresController::class,
            'LvaVariation/TransportManagers'        => Olcs\Controller\Lva\Variation\TransportManagersController::class,
            'LvaVariation/Vehicles'                 => \Olcs\Controller\Lva\Variation\VehiclesController::class,
            'LvaVariation/VehiclesPsv'              => \Olcs\Controller\Lva\Variation\VehiclesPsvController::class,
            'LvaVariation/Safety'                   => \Olcs\Controller\Lva\Variation\SafetyController::class,
            'LvaVariation/TaxiPhv'                  => \Olcs\Controller\Lva\Variation\TaxiPhvController::class,
            'LvaVariation/Discs'                    => \Olcs\Controller\Lva\Variation\DiscsController::class,
            'LvaVariation/Undertakings'             => \Olcs\Controller\Lva\Variation\UndertakingsController::class,
            'LvaVariation/FinancialEvidence'        => \Olcs\Controller\Lva\Variation\FinancialEvidenceController::class,
            'LvaVariation/VehiclesDeclarations'     => \Olcs\Controller\Lva\Variation\VehiclesDeclarationsController::class,
            'LvaVariation/FinancialHistory'         => \Olcs\Controller\Lva\Variation\FinancialHistoryController::class,
            'LvaVariation/LicenceHistory'           => \Olcs\Controller\Lva\Variation\LicenceHistoryController::class,
            'LvaVariation/ConvictionsPenalties'     => \Olcs\Controller\Lva\Variation\ConvictionsPenaltiesController::class,
            'LvaVariation/Summary'                  => \Olcs\Controller\Lva\Variation\SummaryController::class,
            'LvaVariation/UploadEvidence'           => \Olcs\Controller\Lva\Variation\UploadEvidenceController::class,
            'LvaVariation/PaymentSubmission'        => \Olcs\Controller\Lva\Variation\PaymentSubmissionController::class,
            'LvaVariation/Review'                   => \Common\Controller\Lva\ReviewController::class,
            'LvaDirectorChange/People' => \Olcs\Controller\Lva\DirectorChange\PeopleController::class,
            'LvaDirectorChange/FinancialHistory' => Olcs\Controller\Lva\DirectorChange\FinancialHistoryController::class,
            'LvaDirectorChange/LicenceHistory' => \Olcs\Controller\Lva\DirectorChange\LicenceHistoryController::class,
            'LvaDirectorChange/ConvictionsPenalties' => \Olcs\Controller\Lva\DirectorChange\ConvictionsPenaltiesController::class,
            'LvaTransportManager/CheckAnswers' => \Olcs\Controller\Lva\TransportManager\CheckAnswersController::class,
            'LvaTransportManager/Confirmation' => \Olcs\Controller\Lva\TransportManager\ConfirmationController::class,
            'LvaTransportManager/OperatorDeclaration' => \Olcs\Controller\Lva\TransportManager\OperatorDeclarationController::class,
            'LvaTransportManager/TmDeclaration' => \Olcs\Controller\Lva\TransportManager\TmDeclarationController::class,
        ],
        'aliases' => [
            'LvaLicence'                            => LvaLicenceControllers\OverviewController::class,
            'LvaLicence/Variation'                  => LvaLicenceControllers\VariationController::class,
            'LvaLicence/TypeOfLicence'              => LvaLicenceControllers\TypeOfLicenceController::class,
            'LvaLicence/BusinessType'               => LvaLicenceControllers\BusinessTypeController::class,
            'LvaLicence/BusinessDetails'            => LvaLicenceControllers\BusinessDetailsController::class,
            'LvaLicence/Addresses'                  => LvaLicenceControllers\AddressesController::class,
            'LvaLicence/People'                     => LvaLicenceControllers\PeopleController::class,
            'LvaLicence/OperatingCentres'           => LvaLicenceControllers\OperatingCentresController::class,
            'LvaLicence/TransportManagers'          => LvaLicenceControllers\TransportManagersController::class,
            'LvaLicence/Vehicles'                   => LvaLicenceControllers\VehiclesController::class,
            'LvaLicence/VehiclesPsv'                => LvaLicenceControllers\VehiclesPsvController::class,
            'LvaLicence/Trailers'                   => LvaLicenceControllers\TrailersController::class,
            'LvaLicence/Safety'                     => LvaLicenceControllers\SafetyController::class,
            'LvaLicence/TaxiPhv'                    => LvaLicenceControllers\TaxiPhvController::class,
            'LvaLicence/Discs'                      => LvaLicenceControllers\DiscsController::class,
            'LvaLicence/ConditionsUndertakings'     => LvaLicenceControllers\ConditionsUndertakingsController::class,
            'LvaVariation'                          => LvaVariationControllers\OverviewController::class,
            'LvaVariation/TypeOfLicence'            => LvaVariationControllers\TypeOfLicenceController::class,
            'LvaVariation/BusinessType'             => LvaVariationControllers\BusinessTypeController::class,
            'LvaVariation/BusinessDetails'          => LvaVariationControllers\BusinessDetailsController::class,
            'LvaVariation/Addresses'                => LvaVariationControllers\AddressesController::class,
            'LvaVariation/People'                   => LvaVariationControllers\PeopleController::class,
            'LvaVariation/OperatingCentres'         => LvaVariationControllers\OperatingCentresController::class,
            'LvaVariation/TransportManagers'        => LvaVariationControllers\TransportManagersController::class,
            'LvaVariation/Vehicles'                 => LvaVariationControllers\VehiclesController::class,
            'LvaVariation/VehiclesPsv'              => LvaVariationControllers\VehiclesPsvController::class,
            'LvaVariation/Safety'                   => LvaVariationControllers\SafetyController::class,
            'LvaVariation/TaxiPhv'                  => LvaVariationControllers\TaxiPhvController::class,
            'LvaVariation/Discs'                    => LvaVariationControllers\DiscsController::class,
            'LvaVariation/Undertakings'             => LvaVariationControllers\UndertakingsController::class,
            'LvaVariation/FinancialEvidence'        => LvaVariationControllers\FinancialEvidenceController::class,
            'LvaVariation/FinancialHistory'         => LvaVariationControllers\FinancialHistoryController::class,
            'LvaVariation/LicenceHistory'           => LvaVariationControllers\LicenceHistoryController::class,
            'LvaVariation/ConvictionsPenalties'     => LvaVariationControllers\ConvictionsPenaltiesController::class,
            'LvaVariation/Summary'                  => LvaVariationControllers\SummaryController::class,
            'LvaVariation/UploadEvidence'           => LvaVariationControllers\UploadEvidenceController::class,
            'LvaVariation/PaymentSubmission'        => LvaVariationControllers\PaymentSubmissionController::class,
            'LvaVariation/Review'                   => \Common\Controller\Lva\ReviewController::class,
            'LvaDirectorChange/People'              => LvaDirectorChangeControllers\PeopleController::class,
            'LvaDirectorChange/FinancialHistory'    => LvaDirectorChangeControllers\FinancialHistoryController::class,
            'LvaDirectorChange/LicenceHistory'      => LvaDirectorChangeControllers\LicenceHistoryController::class,
            'LvaDirectorChange/ConvictionsPenalties' => LvaDirectorChangeControllers\ConvictionsPenaltiesController::class,
            'LvaTransportManager/CheckAnswers'      => LvaTransportManagerControllers\CheckAnswersController::class,
            'LvaTransportManager/Confirmation'      => LvaTransportManagerControllers\ConfirmationController::class,
            'LvaTransportManager/OperatorDeclaration' => LvaTransportManagerControllers\OperatorDeclarationController::class,
            'LvaTransportManager/TmDeclaration'     => LvaTransportManagerControllers\TmDeclarationController::class,
            'Dashboard' => Olcs\Controller\DashboardController::class,
            'Olcs\Ebsr\Uploads' => \Olcs\Controller\Ebsr\UploadsController::class,
        ],
        'invokables' => [
            'DeclarationFormController' => \Olcs\Controller\Lva\DeclarationFormController::class,
            'Dashboard' => Olcs\Controller\DashboardController::class,
            'Search\Result' => 'Olcs\Controller\Search\ResultController',
        ],
        'factories' => [
            \Olcs\Controller\StaticAssetsController::class => \Olcs\Controller\Factory\StaticAssetsControllerFactory::class,
            IndexController::class => IndexControllerFactory::class,
            CookieSettingsController::class => CookieSettingsControllerFactory::class,
            ListVehicleController::class => \Olcs\Controller\Licence\Vehicle\ListVehicleControllerFactory::class,
            SessionTimeoutController::class => \Olcs\Controller\SessionTimeoutControllerFactory::class,
            \Olcs\Controller\Licence\Vehicle\SwitchBoardController::class => \Olcs\Controller\Licence\Vehicle\SwitchBoardControllerFactory::class,
            \Olcs\Controller\Auth\LoginController::class => \Olcs\Controller\Auth\LoginControllerFactory::class,
            CookieDetailsController::class => DetailsControllerFactory::class,

            Olcs\Controller\BusReg\BusRegRegistrationsController::class => BusRegRegistrationsControllerFactory::class,
            Olcs\Controller\Ebsr\BusRegApplicationsController::class => \Olcs\Controller\Factory\Ebsr\BusRegApplicationsControllerFactory::class,
            Olcs\Controller\BusReg\BusRegBrowseController::class => \Olcs\Controller\Factory\BusReg\BusRegBrowseControllerFactory::class,
            Olcs\Controller\CorrespondenceController::class => CorrespondenceControllerFactory::class,
            \Olcs\Controller\DashboardController::class => \Olcs\Controller\Factory\DashboardControllerFactory::class,
            Olcs\Controller\FeesController::class => \Olcs\Controller\Factory\FeesControllerFactory::class,
            MyDetailsController::class => MyDetailsControllerFactory::class,
            SearchController::class => \Olcs\Controller\Factory\Search\SearchControllerFactory::class,
            \Olcs\Controller\Ebsr\UploadsController::class => UploadsControllerFactory::class,
            Olcs\Controller\UserController::class => \Olcs\Controller\Factory\UserControllerFactory::class,
            UserForgotUsernameController::class => \Olcs\Controller\Factory\UserForgotUsernameControllerFactory::class,
            UserRegistrationController::class => \Olcs\Controller\Factory\UserRegistrationControllerFactory::class,
            ConsultantRegistrationController::class => \Olcs\Controller\Factory\ConsultantRegistrationControllerFactory::class,
            OperatorRegistrationController::class => \Olcs\Controller\Factory\OperatorRegistrationControllerFactory::class,
            \Olcs\Controller\Factory\UserRegistrationControllerToggleAwareFactory::class => \Olcs\Controller\Factory\UserRegistrationControllerToggleAwareFactory::class,

            Olcs\Controller\Entity\ViewController::class => \Olcs\Controller\Factory\Entity\ViewControllerFactory::class,

            Olcs\Controller\GdsVerifyController::class => \Olcs\Controller\Factory\GdsVerifyControllerFactory::class,

            // License - Surrender
            Olcs\Controller\Licence\Surrender\ReviewContactDetailsController::class => Olcs\Controller\Licence\Surrender\ReviewContactDetailsControllerFactory::class,
            Olcs\Controller\Licence\Surrender\AddressDetailsController::class =>
                Olcs\Controller\Licence\Surrender\AddressDetailsControllerFactory::class,
            Olcs\Controller\Licence\Surrender\StartController::class => Olcs\Controller\Licence\Surrender\StartControllerFactory::class,
            Olcs\Controller\Licence\Surrender\DeclarationController::class => Olcs\Controller\Licence\Surrender\DeclarationControllerFactory::class,
            Olcs\Controller\Licence\Surrender\ConfirmationController::class => Olcs\Controller\Licence\Surrender\ConfirmationControllerFactory::class,
            Olcs\Controller\Licence\Surrender\CurrentDiscsController::class => Olcs\Controller\Licence\Surrender\CurrentDiscsControllerFactory::class,
            Olcs\Controller\Licence\Surrender\OperatorLicenceController::class => Olcs\Controller\Licence\Surrender\OperatorLicenceControllerFactory::class,
            Olcs\Controller\Licence\Surrender\ReviewController::class => Olcs\Controller\Licence\Surrender\ReviewControllerFactory::class,
            Olcs\Controller\Licence\Surrender\CommunityLicenceController::class => Olcs\Controller\Licence\Surrender\CommunityLicenceControllerFactory::class,
            Olcs\Controller\Licence\Surrender\DestroyController::class => Olcs\Controller\Licence\Surrender\DestroyControllerFactory::class,
            Olcs\Controller\Licence\Surrender\PrintSignReturnController::class => Olcs\Controller\Licence\Surrender\PrintSignReturnControllerFactory::class,
            \Olcs\Controller\Licence\Surrender\InformationChangedController::class => \Olcs\Controller\Licence\Surrender\InformationChangedControllerFactory::class,
            // Licence - Vehicles
            \Olcs\Controller\Licence\Vehicle\AddVehicleSearchController::class                              => \Olcs\Controller\Licence\Vehicle\AddVehicleSearchControllerFactory::class,
            \Olcs\Controller\Licence\Vehicle\AddDuplicateVehicleController::class                           => \Olcs\Controller\Licence\Vehicle\AddDuplicateVehicleControllerFactory::class,
            \Olcs\Controller\Licence\Vehicle\RemoveVehicleController::class                                 => \Olcs\Controller\Licence\Vehicle\RemoveVehicleControllerFactory::class,
            \Olcs\Controller\Licence\Vehicle\RemoveVehicleConfirmationController::class                     => \Olcs\Controller\Licence\Vehicle\RemoveVehicleConfirmationControllerFactory::class,
            \Olcs\Controller\Licence\Vehicle\TransferVehicleController::class                               => \Olcs\Controller\Licence\Vehicle\TransferVehicleControllerFactory::class,
            \Olcs\Controller\Licence\Vehicle\ViewVehicleController::class                                   => \Olcs\Controller\Licence\Vehicle\ViewVehicleControllerFactory::class,
            \Olcs\Controller\Licence\Vehicle\TransferVehicleConfirmationController::class                   => \Olcs\Controller\Licence\Vehicle\TransferVehicleConfirmationControllerFactory::class,
            \Olcs\Controller\Licence\Vehicle\Reprint\ReprintLicenceVehicleDiscController::class             => \Olcs\Controller\Licence\Vehicle\Reprint\ReprintLicenceVehicleDiscControllerFactory::class,
            \Olcs\Controller\Licence\Vehicle\Reprint\ReprintLicenceVehicleDiscConfirmationController::class => \Olcs\Controller\Licence\Vehicle\Reprint\ReprintLicenceVehicleDiscConfirmationControllerFactory::class,
            Olcs\Controller\ConversationsController::class                                                  => Olcs\Controller\Factory\ConversationsControllerFactory::class,
            PromptController::class                                                                         => \Olcs\Controller\PromptControllerFactory::class,
            WelcomeController::class                                                                         => \Olcs\Controller\WelcomeControllerFactory::class,
            // Process Signature from GOV.UK Account
            \Olcs\Controller\SignatureVerificationController::class                                         => \Olcs\Controller\SignatureVerificationControllerFactory::class,
            // LVA Controller Factories
            LvaLicenceControllers\AddressesController::class                                                => LvaLicenceControllerFactories\AddressesControllerFactory::class,
            LvaLicenceControllers\BusinessDetailsController::class                                          => LvaLicenceControllerFactories\BusinessDetailsControllerFactory::class,
            LvaLicenceControllers\BusinessTypeController::class                                             => LvaLicenceControllerFactories\BusinessTypeControllerFactory::class,
            LvaLicenceControllers\ConditionsUndertakingsController::class                                   => LvaLicenceControllerFactories\ConditionsUndertakingsControllerFactory::class,
            LvaLicenceControllers\DiscsController::class                                                    => LvaLicenceControllerFactories\DiscsControllerFactory::class,
            LvaLicenceControllers\OperatingCentresController::class                                         => LvaLicenceControllerFactories\OperatingCentresControllerFactory::class,
            LvaLicenceControllers\OverviewController::class => LvaLicenceControllerFactories\OverviewControllerFactory::class,
            LvaLicenceControllers\PeopleController::class => LvaLicenceControllerFactories\PeopleControllerFactory::class,
            LvaLicenceControllers\SafetyController::class => LvaLicenceControllerFactories\SafetyControllerFactory::class,
            LvaLicenceControllers\TaxiPhvController::class => LvaLicenceControllerFactories\TaxiPhvControllerFactory::class,
            LvaLicenceControllers\TrailersController::class => LvaLicenceControllerFactories\TrailersControllerFactory::class,
            LvaLicenceControllers\TransportManagersController::class => LvaLicenceControllerFactories\TransportManagersControllerFactory::class,
            LvaLicenceControllers\TypeOfLicenceController::class => LvaLicenceControllerFactories\TypeOfLicenceControllerFactory::class,
            LvaLicenceControllers\VariationController::class => LvaLicenceControllerFactories\VariationControllerFactory::class,
            LvaLicenceControllers\VehiclesController::class => LvaLicenceControllerFactories\VehiclesControllerFactory::class,
            LvaLicenceControllers\VehiclesPsvController::class => LvaLicenceControllerFactories\VehiclesPsvControllerFactory::class,

            LvaVariationControllers\AddressesController::class => LvaVariationControllerFactories\AddressesControllerFactory::class,
            LvaVariationControllers\BusinessDetailsController::class => LvaVariationControllerFactories\BusinessDetailsControllerFactory::class,
            LvaVariationControllers\BusinessTypeController::class => LvaVariationControllerFactories\BusinessTypeControllerFactory::class,
            LvaVariationControllers\ConvictionsPenaltiesController::class => LvaVariationControllerFactories\ConvictionsPenaltiesControllerFactory::class,
            LvaVariationControllers\DiscsController::class => LvaVariationControllerFactories\DiscsControllerFactory::class,
            LvaVariationControllers\FinancialEvidenceController::class => LvaVariationControllerFactories\FinancialEvidenceControllerFactory::class,
            LvaVariationControllers\FinancialHistoryController::class => LvaVariationControllerFactories\FinancialHistoryControllerFactory::class,
            LvaVariationControllers\LicenceHistoryController::class => LvaVariationControllerFactories\LicenceHistoryControllerFactory::class,
            LvaVariationControllers\OperatingCentresController::class => LvaVariationControllerFactories\OperatingCentresControllerFactory::class,
            LvaVariationControllers\OverviewController::class => LvaVariationControllerFactories\OverviewControllerFactory::class,
            LvaVariationControllers\PaymentSubmissionController::class => LvaVariationControllerFactories\PaymentSubmissionControllerFactory::class,
            LvaVariationControllers\PeopleController::class => LvaVariationControllerFactories\PeopleControllerFactory::class,
            LvaVariationControllers\SafetyController::class => LvaVariationControllerFactories\SafetyControllerFactory::class,
            LvaVariationControllers\TaxiPhvController::class => LvaVariationControllerFactories\TaxiPhvControllerFactory::class,
            LvaVariationControllers\TransportManagersController::class => LvaVariationControllerFactories\TransportManagersControllerFactory::class,
            LvaVariationControllers\TypeOfLicenceController::class => LvaVariationControllerFactories\TypeOfLicenceControllerFactory::class,
            LvaVariationControllers\UndertakingsController::class => LvaVariationControllerFactories\UndertakingsControllerFactory::class,
            LvaVariationControllers\UploadEvidenceController::class => LvaVariationControllerFactories\UploadEvidenceControllerFactory::class,
            LvaVariationControllers\VehiclesController::class => LvaVariationControllerFactories\VehiclesControllerFactory::class,
            LvaVariationControllers\VehiclesDeclarationsController::class => LvaVariationControllerFactories\VehiclesDeclarationsControllerFactory::class,
            LvaVariationControllers\VehiclesPsvController::class => LvaVariationControllerFactories\VehiclesPsvControllerFactory::class,
            LvaVariationControllers\SummaryController::class => LvaVariationControllerFactories\SummaryControllerFactory::class,
            LvaTransportManagerControllers\CheckAnswersController::class => LvaTransportManagerControllerFactories\CheckAnswersControllerFactory::class,
            LvaTransportManagerControllers\ConfirmationController::class => LvaTransportManagerControllerFactories\ConfirmationControllerFactory::class,
            LvaTransportManagerControllers\OperatorDeclarationController::class => LvaTransportManagerControllerFactories\OperatorDeclarationControllerFactory::class,
            LvaTransportManagerControllers\TmDeclarationController::class => LvaTransportManagerControllerFactories\TmDeclarationControllerFactory::class,

            LvaDirectorChangeControllers\ConvictionsPenaltiesController::class => LvaDirectorChangeControllerFactories\ConvictionsPenaltiesControllerFactory::class,
            LvaDirectorChangeControllers\FinancialHistoryController::class => LvaDirectorChangeControllerFactories\FinancialHistoryControllerFactory::class,
            LvaDirectorChangeControllers\LicenceHistoryController::class => LvaDirectorChangeControllerFactories\LicenceHistoryControllerFactory::class,
            LvaDirectorChangeControllers\PeopleController::class => LvaDirectorChangeControllerFactories\PeopleControllerFactory::class,
        ],
    ],
    'local_forms_path' => __DIR__ . '/../src/Form/Forms/',
    'tables' => [
        'config' => [
            __DIR__ . '/../src/Table/Tables/'
        ]
    ],
    'service_manager' => [
        'aliases' => [
            'LicencePeopleAdapter' => LicencePeopleAdapter::class,
            'VariationTransportManagerAdapter' => VariationTransportManagerAdapter::class,
            'LicenceTransportManagerAdapter' => LicenceTransportManagerAdapter::class,
            'VariationPeopleAdapter' => VariationPeopleAdapter::class,
            'ApplicationPeopleAdapter' => ApplicationPeopleAdapter::class,
            'DashboardProcessingService' => ProcessingService\DashboardProcessingService::class,
            'Processing\CreateVariation' => ProcessingService\CreateVariationProcessingService::class,
        ],
        'invokables' => [
            'CookieCookieStateFactory' => CookieService\CookieStateFactory::class,
            'CookiePreferencesFactory' => CookieService\PreferencesFactory::class,
            'CookieSetCookieFactory' => CookieService\SetCookieFactory::class,
            'CookieCookieExpiryGenerator' => CookieService\CookieExpiryGenerator::class,
            'CookieSettingsCookieNamesProvider' => CookieService\SettingsCookieNamesProvider::class,
            'QaIrhpApplicationViewGenerator' => QaService\ViewGenerator\IrhpApplicationViewGenerator::class,
            'QaIrhpPermitApplicationViewGenerator' => QaService\ViewGenerator\IrhpPermitApplicationViewGenerator::class,
            LicenceVehicleManagement::class => LicenceVehicleManagement::class,
            \Olcs\Session\ConsultantRegistration::class => \Olcs\Session\ConsultantRegistration::class,
            \Olcs\Controller\Mapper\CreateAccountMapper::class => \Olcs\Controller\Mapper\CreateAccountMapper::class,

        ],
        'abstract_factories' => [
            \Laminas\Cache\Service\StorageCacheAbstractServiceFactory::class,
        ],
        'factories' => [
            TermsAgreedListener::class => TermsAgreedListenerFactory::class,
            'CookieListener' => \Olcs\Mvc\CookieListenerFactory::class,
            'CookieBannerListener' => \Olcs\Mvc\CookieBannerListenerFactory::class,
            'CookieAcceptAllSetCookieGenerator' => CookieService\AcceptAllSetCookieGeneratorFactory::class,
            'CookieBannerVisibilityProvider' => CookieService\BannerVisibilityProviderFactory::class,
            'CookieCookieReader' => CookieService\CookieReaderFactory::class,
            'CookieCurrentPreferencesProvider' => CookieService\CurrentPreferencesProviderFactory::class,
            'CookiePreferencesSetCookieGenerator' => CookieService\PreferencesSetCookieGeneratorFactory::class,
            'CookieDeleteSetCookieGenerator' => CookieService\DeleteSetCookieGeneratorFactory::class,
            'CookieSetCookieArrayGenerator' => CookieService\SetCookieArrayGeneratorFactory::class,
            'CookieAnalyticsCookieNamesProvider' => CookieService\AnalyticsCookieNamesProviderFactory::class,
            'CookieDeleteCookieNamesProvider' => CookieService\DeleteCookieNamesProviderFactory::class,
            ProcessingService\DashboardProcessingService::class => ProcessingService\DashboardProcessingServiceFactory::class,
            'Olcs\InputFilter\EbsrPackInput' => \Olcs\InputFilter\EbsrPackFactory::class,
            'Olcs\Navigation\DashboardNavigation' => Olcs\Navigation\DashboardNavigationFactory::class,
            Olcs\Controller\Listener\Navigation::class => Olcs\Controller\Listener\NavigationFactory::class,
            'QaFormProvider' => QaService\FormProviderFactory::class,
            'QaFormFactory' => QaService\FormFactoryFactory::class,
            'QaGuidanceTemplateVarsAdder' => QaService\GuidanceTemplateVarsAdderFactory::class,
            'QaTemplateVarsGenerator' => QaService\TemplateVarsGeneratorFactory::class,
            'QaQuestionArrayProvider' => QaService\QuestionArrayProviderFactory::class,
            'QaViewGeneratorProvider' => QaService\ViewGeneratorProviderFactory::class,
            SelfserveCommandAdapter::class => SelfserveCommandAdapterFactory::class,
            ProcessingService\CreateVariationProcessingService::class => ProcessingService\CreateVariationProcessingServiceFactory::class,
            Olcs\View\Model\User::class => Olcs\View\Model\UserFactory::class,
            //Adapters
            ApplicationPeopleAdapter::class => ApplicationPeopleAdapterFactory::class,
            LicencePeopleAdapter::class => LicencePeopleAdapterFactory::class,
            LicenceTransportManagerAdapter::class => LicenceTransportManagerAdapterFactory::class,
            VariationTransportManagerAdapter::class => VariationTransportManagerAdapterFactory::class,
            VariationPeopleAdapter::class => VariationPeopleAdapterFactory::class,
            \Olcs\Logging\Log\Processor\CorrelationId::class => \Olcs\Logging\Log\Processor\CorrelationIdFactory::class,
        ],
    ],
    'log_processors' => [
        'factories' => [
            CorrelationId::class => CorrelationIdFactory::class,
        ],
    ],
    'search' => [
        'invokables' => [
            'operator'          => Common\Data\Object\Search\LicenceSelfserve::class, // Selfserve licence search
            'vehicle'           => Common\Data\Object\Search\Vehicle::class,
            'vehicle-external'  => Common\Data\Object\Search\VehicleSelfserve::class,
            'bus'               => Common\Data\Object\Search\BusRegSelfserve::class,
            'person'            => Common\Data\Object\Search\PeopleSelfserve::class,
            'operating-centre'  => Common\Data\Object\Search\OperatingCentreSelfserve::class,
            'publication'       => Common\Data\Object\Search\PublicationSelfserve::class,
        ]
    ],
    'form_elements' => [
        'factories' => [
            SearchFilterFieldset::class => SearchFilterFieldsetFactory::class,
            SearchDateRangeFieldset::class => SearchDateRangeFieldsetFactory::class,
            SearchOrderFieldset::class => SearchOrderFieldsetFactory::class
        ],
        'aliases' => [
            'SearchFilterFieldset' => SearchFilterFieldset::class,
            'SearchDateRangeFieldset' => SearchDateRangeFieldset::class,
            'SearchOrderFieldset' => SearchOrderFieldset::class
        ]
    ],
    'controller_plugins' => [
        'invokables' => [],
        'factories' => [
            \Olcs\Mvc\Controller\Plugin\Placeholder::class => \Olcs\Mvc\Controller\Plugin\PlaceholderFactory::class,
        ],
        'aliases' => [
            'placeholder' => \Olcs\Mvc\Controller\Plugin\Placeholder::class,
        ]
    ],
    'simple_date_format' => [
        'default' => 'd-m-Y'
    ],
    'data_services' => [
        'factories' => [
            DataService\MessagingAppOrLicNo::class => CommonDataService\AbstractListDataServiceFactory::class,
        ],
    ],
    'view_helpers' => [
        'factories' => [
            \Olcs\View\Helper\SessionTimeoutWarning\SessionTimeoutWarning::class => \Olcs\View\Helper\SessionTimeoutWarning\SessionTimeoutWarningFactory::class,
            'cookieManager' => \Olcs\View\Helper\CookieManagerFactory::class,
        ],
        'aliases' => [
            'sessionTimeoutWarning' => \Olcs\View\Helper\SessionTimeoutWarning\SessionTimeoutWarning::class,
        ],
        'invokables' => [
            'generatePeopleList' => \Olcs\View\Helper\GeneratePeopleList::class,
        ]
    ],
    'view_manager' => [
        'display_not_found_reason' => true,
        'display_exceptions' => true,
        'doctype' => 'HTML5',
        'not_found_template' => 'error/404',
        'exception_template' => 'error/index',
        'template_map' => [
            'layout/layout' => __DIR__ . '/../view/layouts/base.phtml',
            'auth/layout' => __DIR__ . '/../view/layouts/base.phtml',
            'auth/login' => __DIR__ . '/../view/pages/auth/login.phtml',
            'auth/change-password' => __DIR__ . '/../view/pages/auth/change-password.phtml',
            'auth/expired-password' => __DIR__ . '/../view/pages/auth/expired-password.phtml',
            'auth/forgot-password' => __DIR__ . '/../view/pages/auth/forgot-password.phtml',
            'auth/confirm-forgot-password' => __DIR__ . '/../view/pages/auth/confirm-forgot-password.phtml',
            'auth/reset-password' => __DIR__ . '/../view/pages/auth/reset-password.phtml',
            'layout/ajax' => __DIR__ . '/../view/layouts/ajax.phtml',
            'error/404' => __DIR__ . '/../view/error/404.phtml',
            'error/403' => __DIR__ . '/../view/error/404.phtml',
            'error/index' => __DIR__ . '/../view/error/index.phtml'
        ],
        'template_path_stack' => [
            __DIR__ . '/../../../vendor/olcs/olcs-common/Common/view',
            __DIR__ . '/../view'
        ]
    ],
    'navigation' => [
        'default' => [
            $applicationNavigation,
            $searchNavigation,
            $busRegSearchTabs,
            $busRegNav,
            $myAccountNav,
            [
                'id' => 'home',
                'label' => 'Home',
                'route' => 'index',
                'pages' => [
                    [
                        'id' => 'selfserve-topnav-home',
                        'label' => 'selfserve-dashboard-topnav-home',
                        'route' => 'dashboard',
                        'class' => 'proposition-nav__item',
                    ],
                    [
                        'id' => 'selfserve-topnav-bus-registration',
                        'label' => 'selfserve-dashboard-topnav-bus-registrations',
                        'route' => 'busreg-registrations',
                        'action' => 'index',
                        'use_route_match' => true,
                        'class' => 'proposition-nav__item',
                    ],
                    [
                        'id' => 'selfserve-topnav-search',
                        'label' => 'search',
                        'route' => 'search',
                        'class' => 'proposition-nav__item',
                        'visible' => false,
                    ],
                    [
                        'id' => 'selfserve-topnav-manage-users',
                        'label' => 'Manage users',
                        'route' => 'manage-user',
                        'action' => 'index',
                        'use_route_match' => true,
                        'class' => 'proposition-nav__item',
                    ],
                    [
                        'id' => 'selfserve-topnav-your-account',
                        'label' => 'selfserve-dashboard-topnav-your-account',
                        'route' => 'your-account',
                        'class' => 'proposition-nav__item',
                    ],
                    [
                        'id' => 'selfserve-topnav-sign-out',
                        'label' => 'selfserve-dashboard-topnav-sign-out',
                        'route' => 'auth/logout',
                        'class' => 'proposition-nav__item',
                    ]
                ],
            ],
            [
                'id' => 'signin',
                'route' => 'auth/login/GET',
                'pages' => [
                    [
                        'id' => 'forgot-password',
                        'label' => 'auth.forgot-password.label',
                        'route' => 'auth/forgot-password',
                    ]
                ]
            ]
        ]
    ],
    'form_service_manager' => [
        'abstract_factories' => [
            LvaFormService\AbstractLvaFormServiceFactory::class,
        ],
        'aliases' => LvaFormService\AbstractLvaFormServiceFactory::FORM_SERVICE_CLASS_ALIASES,
        'factories' => [
            'lva-application-overview-submission' => LvaFormService\ApplicationOverviewSubmissionFactory::class,
            'lva-variation-overview-submission' => LvaFormService\VariationOverviewSubmissionFactory::class,
        ]
    ],
    'lmc_rbac' => [
        'assertion_map' => [
            'selfserve-ebsr-list' => \Olcs\Assertion\Ebsr\EbsrList::class,
        ],
        'guards' => [
            \LmcRbacMvc\Guard\RoutePermissionsGuard::class => [
                // Dashboard Page
                'dashboard' => ['selfserve-nav-dashboard'],

                // Manage Users Page
                'manage-user' => ['can-manage-user-selfserve'],

                // Bus reg stuff and who can access
                // upload page accessible by operators only
                'bus-registration/ebsr' => ['selfserve-ebsr-upload'],

                // bus reg list accessible by operators and LAs
                'bus-registration' => ['selfserve-ebsr-list'],
                'busreg-registrations' => ['selfserve-ebsr-list'],

                // details page accessible by everyone inc anon. users
                'bus-registration/details' => ['*'],

                'entity-view' => [
                    '*'
                ],

                // Selfserve search
                'search-vehicle-external' => ['selfserve-search-vehicle-external'],
                'lva-variation/transport_manager*' => ['selfserve-tm'],
                'lva-*' => ['selfserve-lva'],
                'verify/process-response' => ['*'],
                'search*' => ['*'],
                'index' => ['*'],
                'static-assets' => ['*'],
                'styles-assets' => ['*'],
                'user-registration*' => ['*'],
                'user-forgot-username' => ['*'],
                'cookies*' => ['*'],
                'privacy-notice' => ['*'],
                'terms-and-conditions' => ['*'],
                'accessibility-statement' => ['*'],
                'right-first-time' => ['*'],
                'not-found' => ['*'],
                'server-error' => ['*'],
                'session-timeout' => ['*'],
                '*' => ['selfserve-user'],
            ]
        ],
        'redirect_strategy' => [
            'redirect_when_connected'        => false,
            'redirect_to_route_disconnected' => 'auth/login/GET',
            'append_previous_uri'            => true,
            'previous_uri_query_key'         => 'goto'
        ],
    ],
    'date_settings' => [
        'date_format' => 'd M Y',
        'datetime_format' => 'd M Y H:i',
        'datetimesec_format' => 'd M Y H:i:s'
    ],
    'my_account_route' => 'your-account',
    'local_scripts_path' => [__DIR__ . '/../assets/js/inline/'],
    'qa' => [
        'submit_options' => [
            'options_default' => \Permits\Form\Model\Fieldset\Submit::class,
            'options_default_plus_cancel' => \Permits\Form\Model\Fieldset\SubmitOrCancelApplication::class,
            'options_bilateral' => \Permits\Form\Model\Fieldset\SubmitOnly::class,
        ]
    ],
    'validators' => [
        'factories' => [
            \Olcs\Form\Validator\UniqueConsultantDetails::class => \Olcs\Form\Validator\Factory\UniqueConsultantDetailsFactory::class,
        ],
    ],
];
