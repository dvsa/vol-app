<?php

use Olcs\Auth\Adapter\CommandAdapter;
use Olcs\Auth\Adapter\CommandAdapterFactory;
use Olcs\Auth\Adapter\SelfserveCommandAdapter;
use Olcs\Auth\Adapter\SelfserveCommandAdapterFactory;
use Olcs\Auth\Service\AuthenticationServiceFactory;
use Olcs\Auth\Service\AuthenticationServiceInterface;
use Olcs\Controller\Cookie\DetailsController as CookieDetailsController;
use Olcs\Controller\Cookie\SettingsController as CookieSettingsController;
use Olcs\Controller\Cookie\SettingsControllerFactory as CookieSettingsControllerFactory;
use Olcs\Controller\IndexController;
use Olcs\Controller\Licence\Vehicle\ListVehicleController;
use Olcs\Controller\MyDetailsController;
use Olcs\Controller\PromptController;
use Olcs\Controller\Search\SearchController;
use Olcs\Controller\SessionTimeoutController;
use Olcs\Controller\UserForgotUsernameController;
use Olcs\Controller\UserRegistrationController;
use Olcs\Form\Element\SearchDateRangeFieldset;
use Olcs\Form\Element\SearchDateRangeFieldsetFactory;
use Olcs\Form\Element\SearchFilterFieldset;
use Olcs\Form\Element\SearchFilterFieldsetFactory;
use Olcs\Form\Element\SearchOrderFieldset;
use Olcs\Form\Element\SearchOrderFieldsetFactory;
use Olcs\FormService\Form\Lva as LvaFormService;
use Olcs\Service\Cookie as CookieService;
use Olcs\Service\Qa as QaService;
use Laminas\Mvc\Router\Http\Segment;
use Olcs\Session\LicenceVehicleManagement;

$sectionConfig = new \Common\Service\Data\SectionConfig();
$configRoutes = $sectionConfig->getAllRoutes();

// We no longer want to generate application routes. Instead they will be defined within the application module.
unset($configRoutes['lva-application']);

$sections = $sectionConfig->getAllReferences();
$applicationDetailsPages = array();
$licenceDetailsPages = array();
$variationDetailsPages = array();

foreach ($sections as $section) {
    $applicationDetailsPages['application_' . $section] = array(
        'id' => 'application_' . $section,
        'label' => 'section.name.' . $section,
        'route' => 'lva-application/' . $section,
        'params' => ['action' => 'index'],
        'use_route_match' => true
    );

    $licenceDetailsPages['licence_' . $section] = array(
        'id' => 'licence_' . $section,
        'label' => 'section.name.' . $section,
        'route' => 'lva-licence/' . $section,
        'params' => ['action' => 'index'],
        'use_route_match' => true
    );

    $variationDetailsPages['variation_' . $section] = array(
        'id' => 'variation_' . $section,
        'label' => 'section.name.' . $section,
        'route' => 'lva-variation/' . $section,
        'params' => ['action' => 'index'],
        'use_route_match' => true
    );
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
                'action' => 'add'
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
    'verify' => [
        'type' => \Laminas\Mvc\Router\Http\Literal::class,
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
                    'route' => '/initiate-request[/application/:application]'.
                        '[/continuation-detail/:continuationDetailId][/]',
                    'defaults' => [
                        'action' => 'initiate-request',
                    ],
                ]
            ],
            'transport-manager' =>[
                'type' => Segment::class,
                'options' => [
                    'route' => '/:lva/:applicationId/transport-manager/:transportManagerApplicationId/:role[/]',
                    'defaults' => [
                        'action' => 'initiate-request',
                    ],
                    'constraints' =>[
                            'lva' => '(application|variation)',
                            'applicationId' => '[0-9]+',
                            'transportManagerApplicationId' => '[0-9]+',
                            'role' =>'(tma\\_sign\\_as\\_tm|tma\\_sign\\_as\\_op|tma\\_sign\\_as\\_top)'
                    ],
                ]
            ],
            'surrender' =>[
                'type' => Segment::class,
                'options' => [
                    'route' => '/surrender/:licenceId[/]',
                    'defaults' => [
                        'action' => 'initiate-request',
                    ],
                    'constraints' =>[
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
    array(
        'review' => array(
            'type' => 'segment',
            'options' => array(
                'route' => 'review[/]',
                'defaults' => array(
                    'controller' => 'LvaVariation/Review',
                    'action' => 'index'
                )
            )
        ),
        'submission-summary' => array(
            'type' => 'segment',
            'options' => array(
                'route' => 'submission-summary[/]',
                'defaults' => array(
                    'controller' => 'LvaVariation/Summary',
                    'action' => 'postSubmitSummary'
                )
            )
        ),
        'upload-evidence' => array(
            'type' => 'segment',
            'options' => array(
                'route' => 'upload-evidence[/]',
                'defaults' => array(
                    'controller' => 'LvaVariation/UploadEvidence',
                    'action' => 'index'
                )
            )
        ),
        'summary' => array(
            'type' => 'segment',
            'options' => array(
                'route' => 'summary[/:reference][/]',
                'constraints' => array(
                    'reference' => '[0-9A-Za-z]+-[0-9A-F\-]+',
                ),
                'defaults' => array(
                    'controller' => 'LvaVariation/Summary',
                    'action' => 'index'
                )
            )
        ),
        'pay-and-submit' => array(
            'type' => 'segment',
            'options' => array(
                'route' => 'pay-and-submit[/:redirect-back][/]',
                'defaults' => array(
                    'controller' => 'LvaVariation/PaymentSubmission',
                    'action' => 'payAndSubmit',
                    'redirect-back' => 'overview',
                ),
                'constraints' => array(
                    'redirect-back' => '[a-z\-]+',
                ),
            )
        ),
        'payment' => array(
            'type' => 'segment',
            'options' => array(
                'route' => 'payment[/stored-card-reference/:storedCardReference][/]',
                'defaults' => array(
                    'controller' => 'LvaVariation/PaymentSubmission',
                    'action' => 'index'
                ),
                'constraints' => array(
                    'storedCardReference' => '[0-9A-Za-z]+-[0-9A-F\-]+',
                ),
            )
        ),
        'result' => array(
            'type' => 'segment',
            'options' => array(
                'route' => 'result[/]',
                'defaults' => array(
                    'controller' => 'LvaVariation/PaymentSubmission',
                    'action' => 'payment-result',

                )
            )
        ),
        'cancel' => array(
            'type' => 'segment',
            'options' => array(
                'route' => 'cancel[/]',
                'defaults' => array(
                    'controller' => 'LvaVariation',
                    'action' => 'cancel'
                )
            )
        ),
        'withdraw' => array(
            'type' => 'segment',
            'options' => array(
                'route' => 'withdraw[/]',
                'defaults' => array(
                    'controller' => 'LvaVariation',
                    'action' => 'withdraw'
                )
            )
        ),
    )
);

$configRoutes['lva-licence']['child_routes'] = array_merge(
    $configRoutes['lva-licence']['child_routes'],
    array(
        'variation' => array(
            'type' => 'segment',
            'options' => array(
                'route' => 'variation[/:redirectRoute][/]',
                'defaults' => array(
                    'controller' => 'LvaLicence/Variation',
                    'action' => 'index'
                )
            )
        )
    )
);

foreach (['variation'] as $lva) {
    $configRoutes['lva-' . $lva]['child_routes'] = array_merge(
        $configRoutes['lva-' . $lva]['child_routes'],
        array(
            'transport_manager_details' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => 'transport-managers/details/:child_id[/]',
                    'constraints' => array(
                        'child_id' => '[0-9]+',
                        'grand_child_id' => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'Lva' . ucfirst($lva) . '/TransportManagers',
                        'action' => 'details'
                    )
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'action' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => ':action[/:grand_child_id][/]',
                            'constraints' => array(
                                'grand_child_id' => '[0-9\,]+'
                            ),
                        )
                    ),
                )
            ),
            'transport_manager_check_answer' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => 'transport-managers/check-answer/:child_id[/]',
                    'constraints' => array(
                        'child_id' => '[0-9]+',
                        'grand_child_id' => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'LvaTransportManager/CheckAnswers',
                        'action' => 'index'
                    )
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'action' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => ':action[/:grand_child_id][/]',
                            'constraints' => array(
                                'grand_child_id' => '[0-9\,]+'
                            ),
                        )
                    ),
                ),
            ),
            'transport_manager_tm_declaration' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => 'transport-managers/tm-declaration/:child_id[/]',
                    'constraints' => array(
                        'child_id' => '[0-9]+',
                        'grand_child_id' => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'LvaTransportManager/TmDeclaration',
                        'action' => 'index'
                    )
                ),
                'may_terminate' => true,
            ),
            'transport_manager_operator_declaration' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => 'transport-managers/operator-declaration/:child_id[/]',
                    'constraints' => array(
                        'child_id' => '[0-9]+',
                        'grand_child_id' => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'LvaTransportManager/OperatorDeclaration',
                        'action' => 'index'
                    )
                ),
                'may_terminate' => true,
            ),
            'transport_manager_confirmation' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => 'transport-managers/confirmation/:child_id[/]',
                    'constraints' => array(
                        'child_id' => '[0-9]+',
                        'grand_child_id' => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'LvaTransportManager/Confirmation',
                        'action' => 'index'
                    )
                ),
                'may_terminate' => true,
            )
        )
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

$applicationNavigation = array(
    'id' => 'dashboard-applications',
    'label' => 'Home',
    'route' => 'dashboard',
    'class' => 'proposition-nav__item',
    'pages' => array(
        array(
            'id' => 'application-summary',
            'label' => 'Application summary',
            'route' => 'lva-application/summary',
            'use_route_match' => true
        ),
        array(
            'id' => 'application-submission-summary',
            'label' => 'Application summary',
            'route' => 'lva-application/submission-summary',
            'use_route_match' => true,
            'pages' => array(
                array(
                    'id' => 'application-upload-evidence',
                    'label' => 'Upload evidence',
                    'route' => 'lva-application/upload-evidence',
                    'use_route_match' => true
                ),
            ),
        ),
        array(
            'id' => 'application',
            'label' => 'Application overview',
            'route' => 'lva-application',
            'use_route_match' => true,
            'pages' => $applicationDetailsPages
        ),
        array(
            'id' => 'licence',
            'label' => 'Licence overview',
            'route' => 'lva-licence',
            'use_route_match' => true,
            'pages' => $licenceDetailsPages
        ),
        array(
            'id' => 'variation-summary',
            'label' => 'Application summary',
            'route' => 'lva-variation/summary',
            'use_route_match' => true
        ),
        array(
            'id' => 'variation-submission-summary',
            'label' => 'Application summary',
            'route' => 'lva-variation/submission-summary',
            'use_route_match' => true,
            'pages' => array(
                array(
                    'id' => 'variation-upload-evidence',
                    'label' => 'Upload evidence',
                    'route' => 'lva-variation/upload-evidence',
                    'use_route_match' => true
                ),
            ),
        ),
        array(
            'id' => 'variation',
            'label' => 'Application overview',
            'route' => 'lva-variation',
            'use_route_match' => true,
            'pages' => $variationDetailsPages
        ),
        // Duplicate entry for TM page, corrects the breadcrumb when the user only has access to
        // lva-tm page
        array(
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
        ),
        array(
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
        ),
        array(
            'id' => 'dashboard-licences-applications',
            'label' => 'Licences / Applications',
            'route' => 'dashboard',
            'class' => 'proposition-nav__item',
            'pages' => array(
                // dashboard tabs
                array(
                    'id' => 'dashboard-licences',
                    'label' => 'dashboard-nav-licences',
                    'route' => 'dashboard',
                ),
                array(
                    'id' => 'dashboard-permits',
                    'label' => 'dashboard-nav-permits',
                    'route' => 'permits',
                ),
                array(
                    'id' => 'dashboard-fees',
                    'label' => 'dashboard-nav-fees',
                    'route' => 'fees',
                    'pages' => array(
                        array(
                            'id' => 'pay-fees',
                            'label' => 'Pay',
                            'route' => 'fees/pay',
                        ),
                        array(
                            'id' => 'pay-fees-receipt',
                            'label' => 'Pay',
                            'route' => 'fees/receipt',
                        ),
                    ),
                ),
                array(
                    'id' => 'dashboard-correspondence',
                    'label' => 'dashboard-nav-documents',
                    'route' => 'correspondence',
                ),
            ),
        ),
    ),
);

$busRegNav = array(
    'id' => 'bus-registration',
    'label' => 'Bus registrations',
    'route' => 'bus-registration',
    'action' => 'index',
    'pages' => array(
        array(
            'id' => 'bus-registration-details',
            'label' => 'Details',
            'route' => 'bus-registration/details',
            'action' => 'details',
            'use_route_match' => true
        ),
        array(
            'id' => 'bus-registration-ebsr',
            'label' => 'EBSR',
            'route' => 'bus-registration/ebsr',
            'use_route_match' => true
        )
    )
);

$searchNavigation = array(
    'id' => 'search',
    'label' => 'search',
    'route' => 'search',
    'class' => 'proposition-nav__item',
    'pages' => array(
        // --

        array(
            'id' => 'search-operator',
            'label' => 'search-list-operator',
            'route' => 'search-operator',
            'use_route_match' => true,
            'class' => 'search-navigation__item',
        ),
        array(
            'id' => 'search-publication',
            'label' => 'search-list-publications',
            'route' => 'search-publication',
            'use_route_match' => true,
            'class' => 'search-navigation__item',
        ),
        array(
            'id' => 'search-bus',
            'label' => 'search-list-bus-registrations',
            'route' => 'search-bus',
            'use_route_match' => true,
            'class' => 'search-navigation__item',
        ),
        array(
            'id' => 'search-vehicle-external',
            'label' => 'search-list-vehicles',
            'route' => 'search-vehicle-external',
            'use_route_match' => true,
            'class' => 'search-navigation__item',
        )
    )
);

$busRegSearchTabs = array(
    'id' => 'search-bus-tabs',
    'label' => 'search',
    'route' => 'search-bus',
    'pages' => array(
        // bus search tabs
        array(
            'id' => 'search-bus',
            'label' => 'search',
            'route' => 'search-bus',
            'use_route_match' => true,
            'class' => 'search-navigation__item',
        ),
        array(
            'id' => 'search-bus-browse',
            'label' => 'view',
            'route' => 'search-bus/browse',
        ),
    ),
);

$myAccountNav = array(
    'id' => 'your-account',
    'label' => 'selfserve-dashboard-topnav-your-account',
    'route' => 'your-account',
    'action' => 'edit',
    'pages' => array(
        array(
            'id' => 'change-password',
            'label' => 'Change password',
            'route' => 'change-password',
            'action' => 'index',
            'use_route_match' => true,
        ),
    )
);
return array(
    'router' => array(
        'routes' => array_merge($routes, $configRoutes),
    ),
    'controllers' => array(
        'initializers' => array(
            Olcs\Controller\Initializer\Navigation::class
        ),
        'lva_controllers' => array(
            'LvaLicence'                            => Olcs\Controller\Lva\Licence\OverviewController::class,
            'LvaLicence/Variation'                  => 'Olcs\Controller\Lva\Licence\VariationController',
            'LvaLicence/TypeOfLicence'              => 'Olcs\Controller\Lva\Licence\TypeOfLicenceController',
            'LvaLicence/BusinessType'               => 'Olcs\Controller\Lva\Licence\BusinessTypeController',
            'LvaLicence/BusinessDetails'            => 'Olcs\Controller\Lva\Licence\BusinessDetailsController',
            'LvaLicence/Addresses'                  => 'Olcs\Controller\Lva\Licence\AddressesController',
            'LvaLicence/People'                     => \Olcs\Controller\Lva\Licence\PeopleControllerFactory::class,
            'LvaLicence/OperatingCentres'           => 'Olcs\Controller\Lva\Licence\OperatingCentresController',
            'LvaLicence/TransportManagers'          => Olcs\Controller\Lva\Licence\TransportManagersController::class,
            'LvaLicence/Vehicles'                   => \Olcs\Controller\Lva\Licence\VehicleControllerFactory::class,
            'LvaLicence/VehiclesPsv'                => 'Olcs\Controller\Lva\Licence\VehiclesPsvController',
            'LvaLicence/Trailers'                   => 'Olcs\Controller\Lva\Licence\TrailersController',
            'LvaLicence/Safety'                     => 'Olcs\Controller\Lva\Licence\SafetyController',
            'LvaLicence/TaxiPhv'                    => 'Olcs\Controller\Lva\Licence\TaxiPhvController',
            'LvaLicence/Discs'                      => 'Olcs\Controller\Lva\Licence\DiscsController',
            'LvaLicence/ConditionsUndertakings'     => 'Olcs\Controller\Lva\Licence\ConditionsUndertakingsController',
            'LvaVariation'                          => 'Olcs\Controller\Lva\Variation\OverviewController',
            'LvaVariation/TypeOfLicence'            => 'Olcs\Controller\Lva\Variation\TypeOfLicenceController',
            'LvaVariation/BusinessType'             => 'Olcs\Controller\Lva\Variation\BusinessTypeController',
            'LvaVariation/BusinessDetails'          => 'Olcs\Controller\Lva\Variation\BusinessDetailsController',
            'LvaVariation/Addresses'                => 'Olcs\Controller\Lva\Variation\AddressesController',
            'LvaVariation/People'                   => 'Olcs\Controller\Lva\Variation\PeopleController',
            'LvaVariation/OperatingCentres'         => 'Olcs\Controller\Lva\Variation\OperatingCentresController',
            'LvaVariation/TransportManagers'        => Olcs\Controller\Lva\Variation\TransportManagersController::class,
            'LvaVariation/Vehicles'                 => 'Olcs\Controller\Lva\Variation\VehiclesController',
            'LvaVariation/VehiclesPsv'              => 'Olcs\Controller\Lva\Variation\VehiclesPsvController',
            'LvaVariation/Safety'                   => 'Olcs\Controller\Lva\Variation\SafetyController',
            'LvaVariation/TaxiPhv'                  => 'Olcs\Controller\Lva\Variation\TaxiPhvController',
            'LvaVariation/Discs'                    => 'Olcs\Controller\Lva\Variation\DiscsController',
            'LvaVariation/Undertakings'             => 'Olcs\Controller\Lva\Variation\UndertakingsController',
            'LvaVariation/FinancialEvidence'        => 'Olcs\Controller\Lva\Variation\FinancialEvidenceController',
            'LvaVariation/VehiclesDeclarations'     => 'Olcs\Controller\Lva\Variation\VehiclesDeclarationsController',
            'LvaVariation/FinancialHistory'         => 'Olcs\Controller\Lva\Variation\FinancialHistoryController',
            'LvaVariation/ConvictionsPenalties'     => 'Olcs\Controller\Lva\Variation\ConvictionsPenaltiesController',
            'LvaVariation/Summary'                  => 'Olcs\Controller\Lva\Variation\SummaryController',
            'LvaVariation/UploadEvidence'           => \Olcs\Controller\Lva\Variation\UploadEvidenceController::class,
            'LvaVariation/PaymentSubmission'        => 'Olcs\Controller\Lva\Variation\PaymentSubmissionController',
            'LvaVariation/Review'                   => \Common\Controller\Lva\ReviewController::class,
            'LvaDirectorChange/People'=> \Olcs\Controller\Lva\DirectorChange\PeopleController::class,
            'LvaDirectorChange/FinancialHistory' => Olcs\Controller\Lva\DirectorChange\FinancialHistoryController::class,
            'LvaDirectorChange/ConvictionsPenalties' => \Olcs\Controller\Lva\DirectorChange\ConvictionsPenaltiesControllerFactory::class,
            'LvaTransportManager/CheckAnswers' => \OLCS\Controller\Lva\TransportManager\CheckAnswersController::class,
            'LvaTransportManager/Confirmation' => \OLCS\Controller\Lva\TransportManager\ConfirmationController::class,
            'LvaTransportManager/OperatorDeclaration' => \OLCS\Controller\Lva\TransportManager\OperatorDeclarationController::class,
            'LvaTransportManager/TmDeclaration' => \OLCS\Controller\Lva\TransportManager\TmDeclarationController::class,
        ),
        'invokables' => array(
            'DeclarationFormController' => \Olcs\Controller\Lva\DeclarationFormController::class,
            'Olcs\Ebsr\Uploads' => 'Olcs\Controller\Ebsr\UploadsController',
            Olcs\Controller\Ebsr\BusRegApplicationsController::class =>
                Olcs\Controller\Ebsr\BusRegApplicationsController::class,
            Olcs\Controller\BusReg\BusRegRegistrationsController::class =>
                Olcs\Controller\BusReg\BusRegRegistrationsController::class,
            Olcs\Controller\BusReg\BusRegBrowseController::class =>
                Olcs\Controller\BusReg\BusRegBrowseController::class,
            CookieDetailsController::class => CookieDetailsController::class,
            'Dashboard' => Olcs\Controller\DashboardController::class,
            PromptController::class => PromptController::class,
            Olcs\Controller\FeesController::class => Olcs\Controller\FeesController::class,
            Olcs\Controller\CorrespondenceController::class => Olcs\Controller\CorrespondenceController::class,
            Olcs\Controller\UserController::class => Olcs\Controller\UserController::class,
            IndexController::class => IndexController::class,
            UserForgotUsernameController::class => UserForgotUsernameController::class,
            UserRegistrationController::class => UserRegistrationController::class,
            MyDetailsController::class => MyDetailsController::class,
            SearchController::class => SearchController::class,
            'Search\Result' => 'Olcs\Controller\Search\ResultController',
            Olcs\Controller\Entity\ViewController::class => Olcs\Controller\Entity\ViewController::class,
            Olcs\Controller\GdsVerifyController::class => Olcs\Controller\GdsVerifyController::class,

            // License - Surrender
            Olcs\Controller\Licence\Surrender\ReviewContactDetailsController::class => Olcs\Controller\Licence\Surrender\ReviewContactDetailsController::class,
            Olcs\Controller\Licence\Surrender\AddressDetailsController::class =>
            Olcs\Controller\Licence\Surrender\AddressDetailsController::class,
            Olcs\Controller\Licence\Surrender\StartController::class => Olcs\Controller\Licence\Surrender\StartController::class,
            Olcs\Controller\Licence\Surrender\DeclarationController::class => Olcs\Controller\Licence\Surrender\DeclarationController::class,
            Olcs\Controller\Licence\Surrender\ConfirmationController::class => Olcs\Controller\Licence\Surrender\ConfirmationController::class,
            Olcs\Controller\Licence\Surrender\CurrentDiscsController::class => Olcs\Controller\Licence\Surrender\CurrentDiscsController::class,
            Olcs\Controller\Licence\Surrender\OperatorLicenceController::class => Olcs\Controller\Licence\Surrender\OperatorLicenceController::class,
            Olcs\Controller\Licence\Surrender\ReviewController::class => Olcs\Controller\Licence\Surrender\ReviewController::class,
            Olcs\Controller\Licence\Surrender\CommunityLicenceController::class => Olcs\Controller\Licence\Surrender\CommunityLicenceController::class,
            Olcs\Controller\Licence\Surrender\DestroyController::class => Olcs\Controller\Licence\Surrender\DestroyController::class,
            Olcs\Controller\Licence\Surrender\PrintSignReturnController::class => Olcs\Controller\Licence\Surrender\PrintSignReturnController::class,
            \Olcs\Controller\Licence\Surrender\InformationChangedController::class => \Olcs\Controller\Licence\Surrender\InformationChangedController::class,

            // Licence - Vehicles
            \Olcs\Controller\Licence\Vehicle\AddVehicleSearchController::class => \Olcs\Controller\Licence\Vehicle\AddVehicleSearchController::class,
            \Olcs\Controller\Licence\Vehicle\AddDuplicateVehicleController::class => \Olcs\Controller\Licence\Vehicle\AddDuplicateVehicleController::class,
            \Olcs\Controller\Licence\Vehicle\RemoveVehicleController::class => \Olcs\Controller\Licence\Vehicle\RemoveVehicleController::class,
            \Olcs\Controller\Licence\Vehicle\RemoveVehicleConfirmationController::class => \Olcs\Controller\Licence\Vehicle\RemoveVehicleConfirmationController::class,
            \Olcs\Controller\Licence\Vehicle\TransferVehicleController::class => \Olcs\Controller\Licence\Vehicle\TransferVehicleController::class,
            \Olcs\Controller\Licence\Vehicle\ViewVehicleController::class => \Olcs\Controller\Licence\Vehicle\ViewVehicleController::class,
            \Olcs\Controller\Licence\Vehicle\TransferVehicleConfirmationController::class => \Olcs\Controller\Licence\Vehicle\TransferVehicleConfirmationController::class,
            \Olcs\Controller\Licence\Vehicle\Reprint\ReprintLicenceVehicleDiscController::class => \Olcs\Controller\Licence\Vehicle\Reprint\ReprintLicenceVehicleDiscController::class,
            \Olcs\Controller\Licence\Vehicle\Reprint\ReprintLicenceVehicleDiscConfirmationController::class => \Olcs\Controller\Licence\Vehicle\Reprint\ReprintLicenceVehicleDiscConfirmationController::class,
        ),
        'factories' => array(
            CookieSettingsController::class => CookieSettingsControllerFactory::class,
            ListVehicleController::class => \Olcs\Controller\Licence\Vehicle\ListVehicleControllerFactory::class,
            SessionTimeoutController::class => \Olcs\Controller\SessionTimeoutControllerFactory::class,
            \Olcs\Controller\Licence\Vehicle\SwitchBoardController::class => \Olcs\Controller\Licence\Vehicle\SwitchBoardControllerFactory::class,
            \Olcs\Controller\Auth\LoginController::class => \Olcs\Controller\Auth\LoginControllerFactory::class
        ),
    ),
    'local_forms_path' => __DIR__ . '/../src/Form/Forms/',
    'tables' => array(
        'config' => array(
            __DIR__ . '/../src/Table/Tables/'
        )
    ),
    'service_manager' => array(
        'invokables' => array(
            'ApplicationPeopleAdapter'
                => 'Olcs\Controller\Lva\Adapters\ApplicationPeopleAdapter',
            'LicencePeopleAdapter'
                => 'Olcs\Controller\Lva\Adapters\LicencePeopleAdapter',
            'VariationPeopleAdapter'
                => 'Olcs\Controller\Lva\Adapters\VariationPeopleAdapter',
            'DashboardProcessingService'
                => 'Olcs\Service\Processing\DashboardProcessingService',
            'CookieCookieStateFactory' => CookieService\CookieStateFactory::class,
            'CookiePreferencesFactory' => CookieService\PreferencesFactory::class,
            'CookieSetCookieFactory' => CookieService\SetCookieFactory::class,
            'CookieCookieExpiryGenerator' => CookieService\CookieExpiryGenerator::class,
            'CookieSettingsCookieNamesProvider' => CookieService\SettingsCookieNamesProvider::class,
            'QaIrhpApplicationViewGenerator' => QaService\ViewGenerator\IrhpApplicationViewGenerator::class,
            'QaIrhpPermitApplicationViewGenerator' => QaService\ViewGenerator\IrhpPermitApplicationViewGenerator::class,
            LicenceVehicleManagement::class => LicenceVehicleManagement::class
        ),
        'abstract_factories' => [
            \Laminas\Cache\Service\StorageCacheAbstractServiceFactory::class,
        ],
        'factories' => [
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
            'Olcs\InputFilter\EbsrPackInput' => 'Olcs\InputFilter\EbsrPackFactory',
            'navigation' => Laminas\Navigation\Service\DefaultNavigationFactory::class,
            'Olcs\Navigation\DashboardNavigation' => Olcs\Navigation\DashboardNavigationFactory::class,
            Olcs\Controller\Listener\Navigation::class => Olcs\Controller\Listener\NavigationFactory::class,
            'LicenceTransportManagerAdapter' =>
                \Olcs\Controller\Lva\Factory\Adapter\LicenceTransportManagerAdapterFactory::class,
            'VariationTransportManagerAdapter' =>
                \Olcs\Controller\Lva\Factory\Adapter\VariationTransportManagerAdapterFactory::class,
            'QaFormProvider' => QaService\FormProviderFactory::class,
            'QaFormFactory' => QaService\FormFactoryFactory::class,
            'QaGuidanceTemplateVarsAdder' => QaService\GuidanceTemplateVarsAdderFactory::class,
            'QaTemplateVarsGenerator' => QaService\TemplateVarsGeneratorFactory::class,
            'QaQuestionArrayProvider' => QaService\QuestionArrayProviderFactory::class,
            'QaViewGeneratorProvider' => QaService\ViewGeneratorProviderFactory::class,
            SelfserveCommandAdapter::class => SelfserveCommandAdapterFactory::class,
        ]
    ),
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
    'controller_plugins' => array(
        'invokables' => array(),
        'factories' => [
            \Olcs\Mvc\Controller\Plugin\Placeholder::class => \Olcs\Mvc\Controller\Plugin\PlaceholderFactory::class,
        ],
        'aliases' => array(
            'placeholder' => \Olcs\Mvc\Controller\Plugin\Placeholder::class,
        )
    ),
    'simple_date_format' => array(
        'default' => 'd-m-Y'
    ),
    'view_helpers' => array(
        'factories' => [
            \Olcs\View\Helper\SessionTimeoutWarning\SessionTimeoutWarning::class => \Olcs\View\Helper\SessionTimeoutWarning\SessionTimeoutWarningFactory::class
        ],
        'aliases' => array(
            'sessionTimeoutWarning' => \Olcs\View\Helper\SessionTimeoutWarning\SessionTimeoutWarning::class,
        ),
        'invokables' => array(
            'generatePeopleList' => \Olcs\View\Helper\GeneratePeopleList::class,
            'tmCheckAnswersChangeLink' => \Olcs\View\Helper\TmCheckAnswersChangeLink::class,
            'cookieManager' => \Olcs\View\Helper\CookieManager::class
        )
    ),
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions' => true,
        'doctype' => 'HTML5',
        'not_found_template' => 'error/404',
        'exception_template' => 'error/index',
        'template_map' => array(
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
        ),
        'template_path_stack' => array(
            __DIR__ . '/../../../vendor/olcs/olcs-common/Common/view',
            __DIR__ . '/../view'
        )
    ),
    'navigation' => array(
        'default' => array(
            $applicationNavigation,
            $searchNavigation,
            $busRegSearchTabs,
            $busRegNav,
            $myAccountNav,
            array(
                'id' => 'home',
                'label' => 'Home',
                'route' => 'index',
                'pages' => array(
                    array(
                        'id' => 'selfserve-topnav-home',
                        'label' => 'selfserve-dashboard-topnav-home',
                        'route' => 'dashboard',
                        'class' => 'proposition-nav__item',
                    ),
                    array(
                        'id' => 'selfserve-topnav-bus-registration',
                        'label' => 'selfserve-dashboard-topnav-bus-registrations',
                        'route' => 'busreg-registrations',
                        'action' => 'index',
                        'use_route_match' => true,
                        'class' => 'proposition-nav__item',
                    ),
                    array(
                        'id' => 'selfserve-topnav-search',
                        'label' => 'search',
                        'route' => 'search',
                        'class' => 'proposition-nav__item',
                        'visible' => false,
                    ),
                    array(
                        'id' => 'selfserve-topnav-manage-users',
                        'label' => 'Manage users',
                        'route' => 'manage-user',
                        'action' => 'index',
                        'use_route_match' => true,
                        'class' => 'proposition-nav__item',
                    ),
                    array(
                        'id' => 'selfserve-topnav-your-account',
                        'label' => 'selfserve-dashboard-topnav-your-account',
                        'route' => 'your-account',
                        'class' => 'proposition-nav__item',
                    ),
                    array(
                        'id' => 'selfserve-topnav-sign-out',
                        'label' => 'selfserve-dashboard-topnav-sign-out',
                        'route' => 'auth/logout',
                        'class' => 'proposition-nav__item',
                    )
                ),
            ),
            array(
                'id' => 'signin',
                'route' => 'auth/login/GET', //@todo is this used?
                'pages' => array(
                    array(
                        'id' => 'forgot-password',
                        'label' => 'auth.forgot-password.label',
                        'route' => 'auth/forgot-password',
                    )
                )
            )
        )
    ),
    'asset_path' => '//dev_dvsa-static.web01.olcs.mgt.mtpdvsa',
    'form_service_manager' => [
        'invokables' => [
            // Type of Licence
            'lva-licence-type_of_licence' => LvaFormService\TypeOfLicence\LicenceTypeOfLicence::class,
            'lva-variation-type_of_licence' => LvaFormService\TypeOfLicence\VariationTypeOfLicence::class,
            'lva-application-type_of_licence' => LvaFormService\TypeOfLicence\ApplicationTypeOfLicence::class,

            // Address
            'lva-licence-addresses' => LvaFormService\Addresses\LicenceAddresses::class,
            'lva-variation-addresses' => LvaFormService\Addresses\VariationAddresses::class,
            'lva-application-addresses' => LvaFormService\Addresses\ApplicationAddresses::class,

            // Safety
            'lva-licence-safety' => LvaFormService\LicenceSafety::class,
            'lva-variation-safety' => LvaFormService\VariationSafety::class,

            // Operating Centres
            'lva-licence-operating_centres' => LvaFormService\OperatingCentres\LicenceOperatingCentres::class,
            'lva-variation-operating_centres' => LvaFormService\OperatingCentres\VariationOperatingCentres::class,
            'lva-application-operating_centres' => LvaFormService\OperatingCentres\ApplicationOperatingCentres::class,

            'lva-application-operating_centre' => LvaFormService\OperatingCentre\LvaOperatingCentre::class,
            'lva-licence-operating_centre' => LvaFormService\OperatingCentre\LvaOperatingCentre::class,
            'lva-variation-operating_centre' => LvaFormService\OperatingCentre\LvaOperatingCentre::class,

            // Business Type
            'lva-application-business_type' => LvaFormService\BusinessType\ApplicationBusinessType::class,
            'lva-licence-business_type' => LvaFormService\BusinessType\LicenceBusinessType::class,
            'lva-variation-business_type' => LvaFormService\BusinessType\VariationBusinessType::class,
            //
            'lva-lock-business_details' => LvaFormService\LockBusinessDetails::class,
            'lva-licence-business_details' => LvaFormService\LicenceBusinessDetails::class,
            'lva-variation-business_details' => LvaFormService\VariationBusinessDetails::class,
            'lva-application-business_details' => LvaFormService\ApplicationBusinessDetails::class,
            // Goods vehicle filter form service
            'lva-application-goods-vehicles-filters' => LvaFormService\ApplicationGoodsVehiclesFilters::class,
            // External common goods vehicles vehicle form service
            'lva-application-goods-vehicles-add-vehicle' => LvaFormService\GoodsVehicles\AddVehicle::class,
            'lva-licence-vehicles_psv' => LvaFormService\LicencePsvVehicles::class,
            'lva-licence-goods-vehicles' => LvaFormService\LicenceGoodsVehicles::class,
            'lva-licence-goods-vehicles-add-vehicle' => LvaFormService\GoodsVehicles\AddVehicle::class,
            'lva-variation-goods-vehicles-add-vehicle' => LvaFormService\GoodsVehicles\AddVehicle::class,
            'lva-application-goods-vehicles-edit-vehicle' => LvaFormService\GoodsVehicles\EditVehicle::class,
            'lva-licence-goods-vehicles-edit-vehicle' => LvaFormService\GoodsVehicles\EditVehicle::class,
            'lva-variation-goods-vehicles-edit-vehicle' => LvaFormService\GoodsVehicles\EditVehicle::class,
            // External common psv vehicles vehicle form service
            'lva-psv-vehicles-vehicle' => LvaFormService\PsvVehiclesVehicle::class,
            // External common vehicles vehicle form service (Goods and PSV)
            'lva-vehicles-vehicle' => LvaFormService\VehiclesVehicle::class,

            'lva-application-people' => LvaFormService\People\ApplicationPeople::class,
            'lva-application-financial_evidence' => LvaFormService\ApplicationFinancialEvidence::class,
            'lva-application-vehicles_declarations' => LvaFormService\ApplicationVehiclesDeclarations::class,
            'lva-application-safety' => LvaFormService\ApplicationSafety::class,
            'lva-application-financial_history' => LvaFormService\ApplicationFinancialHistory::class,
            'lva-application-licence_history' => LvaFormService\ApplicationLicenceHistory::class,
            'lva-application-convictions_penalties' => LvaFormService\ApplicationConvictionsPenalties::class,
            'lva-licence-convictions_penalties' => Olcs\FormService\Form\Lva\ConvictionsPenalties::class,

            'lva-application-vehicles_psv' => LvaFormService\ApplicationPsvVehicles::class,
            'lva-application-goods-vehicles' => LvaFormService\ApplicationGoodsVehicles::class,

            'lva-licence-sole_trader' => LvaFormService\People\SoleTrader\LicenceSoleTrader::class,
            'lva-variation-sole_trader' => LvaFormService\People\SoleTrader\VariationSoleTrader::class,
            'lva-application-sole_trader' => LvaFormService\People\SoleTrader\ApplicationSoleTrader::class,

            'lva-application-transport_managers' => LvaFormService\TransportManager\ApplicationTransportManager::class,

            'lva-application-taxi_phv' => LvaFormService\ApplicationTaxiPhv::class,

            'lva-licence-trailers' => LvaFormService\LicenceTrailers::class,

            'lva-application-overview-submission' => LvaFormService\ApplicationOverviewSubmission::class,
            'lva-variation-overview-submission' => LvaFormService\VariationOverviewSubmission::class,
        ],
    ],
    'zfc_rbac' => [
        'assertion_map' => [
            'selfserve-ebsr-list' => \Olcs\Assertion\Ebsr\EbsrList::class,
        ],
        'guards' => [
            'ZfcRbac\Guard\RoutePermissionsGuard' => [
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
                'user-registration' => ['*'],
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
    ]
);
