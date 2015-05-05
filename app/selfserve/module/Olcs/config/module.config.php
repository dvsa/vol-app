<?php

$sectionConfig = new \Common\Service\Data\SectionConfig();
$configRoutes = $sectionConfig->getAllRoutes();

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

$routes = array(
    'ebsr' => array(
        'type' => 'segment',
        'options' =>  array(
            'route' => '/ebsr[/:action]',
            'defaults' => array(
                'controller' => 'Olcs\Ebsr\Uploads',
                'action' => 'index'
            )
        )
    ),
    'bus-registration' => array(
        'type' => 'segment',
        'options' =>  array(
            'route' =>
                '/bus-registration/:action[/busreg/:busRegId][/sub-type/:subType][/page/:page]' .
                '[/limit/:limit][/sort/:sort][/order/:order]',
            'defaults' => array(
                'controller' => 'Olcs\Ebsr\BusRegistration',
                'action' => 'index',
                'page' => 1,
                'limit' => 25,
                'sort' => 'submittedDate',
                'order' => 'DESC'
            )
        )
    ),
    'dashboard' => array(
        'type' => 'segment',
        'options' => array(
            'route' => '/dashboard[/]',
            'defaults' => array(
                'controller' => 'Dashboard',
                'action' => 'index'
            )
        )
    ),
    'fees' => array(
        'type' => 'segment',
        'options' => array(
            'route' => '/fees[/]',
            'defaults' => array(
                'controller' => 'Fees',
                'action' => 'index'
            )
        )
    ),
    'correspondence' => array(
        'type' => 'segment',
        'options' => array(
            'route' => '/correspondence[/]',
            // @TODO, we need this route for navigation but not implemented until OLCS-5229
            'defaults' => array(
                'controller' => 'Dashboard',
                'action' => 'index'
            )
        )
    ),
    'create_application' => array(
        'type' => 'segment',
        'options' => array(
            'route' => '/application/create[/]',
            'defaults' => array(
                'skipPreDispatch' => true,
                'controller' => 'LvaApplication/TypeOfLicence',
                'action' => 'createApplication'
            )
        )
    ),
    'create_variation' => array(
        'type' => 'segment',
        'options' => array(
            'route' => '/variation/create/:licence[/]',
            'constraints' => array(
                'licence' => '[0-9]+',
            ),
            'defaults' => array(
                'controller' => 'LvaLicence',
                'action' => 'createVariation'
            )
        )
    ),
    'user' => array(
        'type' => 'segment',
        'options' => array(
            'route' => '/manage-user[/:action][/:id]',
            'defaults' => array(
                'controller' => 'User',
                'action' => 'index'
            )
        )
    )
);

$configRoutes['lva-application']['child_routes'] = array_merge(
    $configRoutes['lva-application']['child_routes'],
    array(
        'review' => array(
            'type' => 'segment',
            'options' => array(
                'route' => 'review[/]',
                'defaults' => array(
                    'controller' => 'LvaApplication/Review',
                    'action' => 'index'
                )
            )
        ),
        'payment' => array(
            'type' => 'segment',
            'options' => array(
                'route' => 'payment[/]',
                'defaults' => array(
                    'controller' => 'LvaApplication/PaymentSubmission',
                    'action' => 'index'
                )
            )
        ),
        'submission-summary' => array(
            'type' => 'segment',
            'options' => array(
                'route' => 'submission-summary[/]',
                'defaults' => array(
                    'controller' => 'LvaApplication/Summary',
                    'action' => 'postSubmitSummary'
                )
            )
        ),
        'summary' => array(
            'type' => 'segment',
            'options' => array(
                'route' => 'summary[/]',
                'defaults' => array(
                    'controller' => 'LvaApplication/Summary',
                    'action' => 'index'
                )
            )
        ),
        'result' => array(
            'type' => 'segment',
            'options' => array(
                'route' => 'result[/]',
                'defaults' => array(
                    'controller' => 'LvaApplication/PaymentSubmission',
                    'action' => 'payment-result',

                )
            )
        )
    )
);

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
        'summary' => array(
            'type' => 'segment',
            'options' => array(
                'route' => 'summary[/]',
                'defaults' => array(
                    'controller' => 'LvaVariation/Summary',
                    'action' => 'index'
                )
            )
        ),
        'payment' => array(
            'type' => 'segment',
            'options' => array(
                'route' => 'payment[/]',
                'defaults' => array(
                    'controller' => 'LvaVariation/PaymentSubmission',
                    'action' => 'index'
                )
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
    )
);

$configRoutes['lva-licence']['child_routes'] = array_merge(
    $configRoutes['lva-licence']['child_routes'],
    array(
        'variation' => array(
            'type' => 'segment',
            'options' => array(
                'route' => 'variation[/]',
                'defaults' => array(
                    'controller' => 'LvaLicence/Variation',
                    'action' => 'index'
                )
            )
        )
    )
);

foreach (['application', 'variation'] as $lva) {
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
                            'defaults' => array(
                                'controller' => 'Lva' . ucfirst($lva) . '/TransportManagers'
                            )
                        )
                    )
                )
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
}

return array(
    'router' => array(
        'routes' => array_merge($routes, $configRoutes),
    ),
    'controllers' => array(
        'lva_controllers' => array(
            'LvaApplication'                        => 'Olcs\Controller\Lva\Application\OverviewController',
            'LvaApplication/TypeOfLicence'          => 'Olcs\Controller\Lva\Application\TypeOfLicenceController',
            'LvaApplication/BusinessType'           => 'Olcs\Controller\Lva\Application\BusinessTypeController',
            'LvaApplication/BusinessDetails'        => 'Olcs\Controller\Lva\Application\BusinessDetailsController',
            'LvaApplication/Addresses'              => 'Olcs\Controller\Lva\Application\AddressesController',
            'LvaApplication/People'                 => 'Olcs\Controller\Lva\Application\PeopleController',
            'LvaApplication/OperatingCentres'       => 'Olcs\Controller\Lva\Application\OperatingCentresController',
            'LvaApplication/FinancialEvidence'      => 'Olcs\Controller\Lva\Application\FinancialEvidenceController',
            'LvaApplication/TransportManagers'      => 'Olcs\Controller\Lva\Application\TransportManagersController',
            'LvaApplication/Vehicles'               => 'Olcs\Controller\Lva\Application\VehiclesController',
            'LvaApplication/VehiclesPsv'            => 'Olcs\Controller\Lva\Application\VehiclesPsvController',
            'LvaApplication/Safety'                 => 'Olcs\Controller\Lva\Application\SafetyController',
            'LvaApplication/FinancialHistory'       => 'Olcs\Controller\Lva\Application\FinancialHistoryController',
            'LvaApplication/LicenceHistory'         => 'Olcs\Controller\Lva\Application\LicenceHistoryController',
            'LvaApplication/ConvictionsPenalties'   => 'Olcs\Controller\Lva\Application\ConvictionsPenaltiesController',
            'LvaApplication/Undertakings'           => 'Olcs\Controller\Lva\Application\UndertakingsController',
            'LvaApplication/TaxiPhv'                => 'Olcs\Controller\Lva\Application\TaxiPhvController',
            'LvaApplication/VehiclesDeclarations'   => 'Olcs\Controller\Lva\Application\VehiclesDeclarationsController',
            'LvaApplication/PaymentSubmission'      => 'Olcs\Controller\Lva\Application\PaymentSubmissionController',
            'LvaApplication/Summary'                => 'Olcs\Controller\Lva\Application\SummaryController',
            'LvaApplication/Review'                 => 'Olcs\Controller\Lva\Application\ReviewController',
            'LvaLicence'                            => 'Olcs\Controller\Lva\Licence\OverviewController',
            'LvaLicence/Variation'                  => 'Olcs\Controller\Lva\Licence\VariationController',
            'LvaLicence/TypeOfLicence'              => 'Olcs\Controller\Lva\Licence\TypeOfLicenceController',
            'LvaLicence/BusinessType'               => 'Olcs\Controller\Lva\Licence\BusinessTypeController',
            'LvaLicence/BusinessDetails'            => 'Olcs\Controller\Lva\Licence\BusinessDetailsController',
            'LvaLicence/Addresses'                  => 'Olcs\Controller\Lva\Licence\AddressesController',
            'LvaLicence/People'                     => 'Olcs\Controller\Lva\Licence\PeopleController',
            'LvaLicence/OperatingCentres'           => 'Olcs\Controller\Lva\Licence\OperatingCentresController',
            'LvaLicence/TransportManagers'          => 'Olcs\Controller\Lva\Licence\TransportManagersController',
            'LvaLicence/Vehicles'                   => 'Olcs\Controller\Lva\Licence\VehiclesController',
            'LvaLicence/VehiclesPsv'                => 'Olcs\Controller\Lva\Licence\VehiclesPsvController',
            'LvaLicence/Trailers'                   => 'Olcs\Controller\Lva\Licence\TrailersController',
            'LvaLicence/Safety'                     => 'Olcs\Controller\Lva\Licence\SafetyController',
            'LvaLicence/CommunityLicences'          => 'Olcs\Controller\Lva\Licence\CommunityLicencesController',
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
            'LvaVariation/TransportManagers'        => 'Olcs\Controller\Lva\Variation\TransportManagersController',
            'LvaVariation/Vehicles'                 => 'Olcs\Controller\Lva\Variation\VehiclesController',
            'LvaVariation/VehiclesPsv'              => 'Olcs\Controller\Lva\Variation\VehiclesPsvController',
            'LvaVariation/Safety'                   => 'Olcs\Controller\Lva\Variation\SafetyController',
            'LvaVariation/CommunityLicences'        => 'Olcs\Controller\Lva\Variation\CommunityLicencesController',
            'LvaVariation/TaxiPhv'                  => 'Olcs\Controller\Lva\Variation\TaxiPhvController',
            'LvaVariation/Discs'                    => 'Olcs\Controller\Lva\Variation\DiscsController',
            'LvaVariation/ConditionsUndertakings'   => 'Olcs\Controller\Lva\Variation\ConditionsUndertakingsController',
            'LvaVariation/Undertakings'             => 'Olcs\Controller\Lva\Variation\UndertakingsController',
            'LvaVariation/FinancialEvidence'        => 'Olcs\Controller\Lva\Variation\FinancialEvidenceController',
            'LvaVariation/VehiclesDeclarations'     => 'Olcs\Controller\Lva\Variation\VehiclesDeclarationsController',
            'LvaVariation/FinancialHistory'         => 'Olcs\Controller\Lva\Variation\FinancialHistoryController',
            'LvaVariation/ConvictionsPenalties'     => 'Olcs\Controller\Lva\Variation\ConvictionsPenaltiesController',
            'LvaVariation/Summary'                  => 'Olcs\Controller\Lva\Variation\SummaryController',
            'LvaVariation/PaymentSubmission'        => 'Olcs\Controller\Lva\Variation\PaymentSubmissionController',
            'LvaVariation/Review'                   => 'Olcs\Controller\Lva\Variation\ReviewController',
        ),
        'delegators' => array(
            'LvaApplication/BusinessType' => array(
                'delegator' => 'Olcs\Controller\Lva\Delegators\ApplicationBusinessTypeDelegator'
            ),
            'LvaLicence/BusinessType' => array(
                'delegator' => 'Olcs\Controller\Lva\Delegators\LicenceVariationBusinessTypeDelegator'
            ),
            'LvaVariation/BusinessType' => array(
                'delegator' => 'Olcs\Controller\Lva\Delegators\LicenceVariationBusinessTypeDelegator'
            ),
            'LvaApplication/TypeOfLicence' => array(
                'delegator' => 'Olcs\Controller\Lva\Delegators\ApplicationTypeOfLicenceDelegator'
            ),
        ),
        'invokables' => array(
            'Olcs\Ebsr\Uploads' => 'Olcs\Controller\Ebsr\UploadsController',
            'Olcs\Ebsr\BusRegistration' => 'Olcs\Controller\Ebsr\BusRegistrationController',
            'Dashboard' => 'Olcs\Controller\DashboardController',
            'Fees' => 'Olcs\Controller\FeesController',
            'User' => 'Olcs\Controller\UserController'
        )
    ),
    'local_forms_path' => __DIR__ . '/../src/Form/Forms/',
    'tables' => array(
        'config' => array(
            __DIR__ . '/../src/Table/Tables/'
        )
    ),
    'service_manager' => array(
        'aliases' => [
            'Zend\Authentication\AuthenticationService' => 'zfcuser_auth_service',
        ],
        'invokables' => array(
            'LicenceOperatingCentreAdapter'
                => 'Olcs\Controller\Lva\Adapters\LicenceOperatingCentreAdapter',
            'VariationOperatingCentreAdapter'
                => 'Olcs\Controller\Lva\Adapters\VariationOperatingCentreAdapter',
            'ApplicationOperatingCentreAdapter'
                => 'Olcs\Controller\Lva\Adapters\ApplicationOperatingCentreAdapter',
            'Lva\BusinessType' => 'Olcs\Service\Lva\BusinessTypeLvaService',
            'ApplicationBusinessTypeAdapter'
                => 'Olcs\Controller\Lva\Adapters\ApplicationBusinessTypeAdapter',
            'LicenceVariationBusinessTypeAdapter'
                => 'Olcs\Controller\Lva\Adapters\LicenceVariationBusinessTypeAdapter',
            'ApplicationTypeOfLicenceAdapter'
                => 'Olcs\Controller\Lva\Adapters\ApplicationTypeOfLicenceAdapter',
            'ApplicationPeopleAdapter'
                => 'Olcs\Controller\Lva\Adapters\ApplicationPeopleAdapter',
            'LicencePeopleAdapter'
                => 'Olcs\Controller\Lva\Adapters\LicencePeopleAdapter',
            'VariationPeopleAdapter'
                => 'Olcs\Controller\Lva\Adapters\VariationPeopleAdapter',
            'LicenceTransportManagerAdapter'
                => 'Olcs\Controller\Lva\Adapters\LicenceTransportManagerAdapter',
            'DashboardProcessingService'
                => 'Olcs\Service\Processing\DashboardProcessingService',
            'Email\TransportManagerCompleteDigitalForm'
                => 'Olcs\Service\Email\TransportManagerCompleteDigitalForm',
        ),
        'factories' => array(
            'Olcs\InputFilter\EbsrPackInput' => 'Olcs\InputFilter\EbsrPackFactory',
            'Olcs\Service\Ebsr' => 'Olcs\Service\Ebsr',
            'navigation' => 'Zend\Navigation\Service\DefaultNavigationFactory',
            'Olcs\Navigation\DashboardNavigation' => 'Olcs\Navigation\DashboardNavigationFactory',
        )
    ),
    'controller_plugins' => array(
        'invokables' => array()
    ),
    'simple_date_format' => array(
        'default' => 'd-m-Y'
    ),
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions' => true,
        'doctype' => 'HTML5',
        'not_found_template' => 'error/404',
        'exception_template' => 'error/index',
        'template_map' => array(
            'layout/layout' => __DIR__ . '/../view/layouts/base.phtml',
            'layout/ajax' => __DIR__ . '/../view/layouts/ajax.phtml',
            'error/404' => __DIR__ . '/../view/error/404.phtml',
            'error/index' => __DIR__ . '/../view/error/index.phtml'
        ),
        'template_path_stack' => array(
            __DIR__ . '/../../../vendor/olcs/OlcsCommon/Common/view',
            __DIR__ . '/../view'
        )
    ),
    'navigation' => array(
        'default' => array(
            array(
                'id' => 'home',
                'label' => 'Home',
                'route' => 'dashboard',
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
                        'use_route_match' => true
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
                        'use_route_match' => true
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
                )
            ),
        ),
        'dashboard' => array(
            // dashboard tabs
            array(
                'id' => 'dashboard-licences',
                'label' => 'dashboard-nav-licences',
                'route' => 'dashboard',
            ),
            array(
                'id' => 'dashboard-fees',
                'label' => 'dashboard-nav-fees',
                'route' => 'fees',
            ),
            array(
                'id' => 'dashboard-correspondence',
                'label' => 'dashboard-nav-correspondence',
                'route' => 'correspondence',
            ),
        ),
    ),
    'asset_path' => '//dvsa-static.olcsdv-ap01.olcs.npm',
    'service_api_mapping' => array(
        'endpoints' => array(
            'ebsr' => 'http://olcs-ebsr/'
        )
    ),
    'rest_services' => array(
        'delegators' => [
            'Olcs\RestService\ebsr\pack' => ['Olcs\Service\Rest\EbsrPackDelegatorFactory']
        ]
    ),
    'form_service_manager' => [
        'invokables' => [
            'lva-lock-business_details' => 'Olcs\FormService\Form\Lva\LockBusinessDetails',
            'lva-licence-business_details' => 'Olcs\FormService\Form\Lva\LicenceBusinessDetails',
            'lva-variation-business_details' => 'Olcs\FormService\Form\Lva\VariationBusinessDetails',
            'lva-application-business_details' => 'Olcs\FormService\Form\Lva\ApplicationBusinessDetails',
            // Goods vehicle filter form service
            'lva-application-goods-vehicles-filters' => 'Olcs\FormService\Form\Lva\ApplicationGoodsVehiclesFilters',
            // External common goods vehicles vehicle form service
            'lva-goods-vehicles-vehicle' => 'Olcs\FormService\Form\Lva\GoodsVehiclesVehicle',
            // External common psv vehicles vehicle form service
            'lva-psv-vehicles-vehicle' => 'Olcs\FormService\Form\Lva\PsvVehiclesVehicle',
            // External common vehicles vehicle form service (Goods and PSV)
            'lva-vehicles-vehicle' => 'Olcs\FormService\Form\Lva\VehiclesVehicle',
        ],
    ],
    'zfc_rbac' => [
        'guards' => [
            'ZfcRbac\Guard\RoutePermissionsGuard' =>[
                'lva-application/transport_manager_details*' => ['selfserve-tm'],
                'lva-variation/transport_manager_details*' => ['selfserve-tm'],
                'lva-*' => ['selfserve-lva'],
                'manage-user' => ['selfserve-manage-user'], // route -> permission
                '*user*' => ['*'],
                'zfcuser/login'    => ['*'],
                'zfcuser/logout'    => ['*'],
                'ebsr' => ['selfserve-ebsr'],
                'bus-registration' => ['selfserve-ebsr'],
                '*' => ['selfserve-user'],
            ]
        ]
    ],
    'business_rule_manager' => [
        'invokables' => [
            'ApplicationGoodsVehiclesLicenceVehicle'
                => 'Olcs\BusinessRule\Rule\ApplicationGoodsVehiclesLicenceVehicle',
            'UserMappingContactDetails'
            => 'Olcs\BusinessRule\Rule\UserMappingContactDetails',
        ]
    ],
    'business_service_manager' => [
        'invokables' => [
            'Lva\LicenceAddresses' => 'Olcs\BusinessService\Service\Lva\LicenceVariationAddresses',
            'Lva\VariationAddresses' => 'Olcs\BusinessService\Service\Lva\LicenceVariationAddresses',
            'Lva\AddressesChangeTask' => 'Olcs\BusinessService\Service\Lva\AddressesChangeTask',
        ]
    ]
);
