<?php

$sectionConfig = new \Common\Service\Data\SectionConfig();
$configRoutes = $sectionConfig->getAllRoutes();

$sections = $sectionConfig->getAllReferences();
$applicationDetailsPages = array();
$licenceDetailsPages = array();
$variationDetailsPages = array();

foreach ($sections as $section) {
    $applicationDetailsPages[] = array(
        'id' => 'application_' . $section,
        'label' => 'section.name.' . $section,
        'route' => 'lva-application/' . $section,
        'use_route_match' => true
    );

    $licenceDetailsPages[] = array(
        'id' => 'licence_' . $section,
        'label' => 'section.name.' . $section,
        'route' => 'lva-licence/' . $section,
        'use_route_match' => true
    );

    $variationDetailsPages[] = array(
        'id' => 'variation_' . $section,
        'label' => 'section.name.' . $section,
        'route' => 'lva-variation/' . $section,
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
                'route' => 'result/:fee',
                'constraints' => [
                    'fee' => '[0-9]+',
                ],
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
                'route' => 'result/:fee',
                'constraints' => [
                    'fee' => '[0-9]+',
                ],
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
            'LvaApplication/BusinessDetails' => array(
                'delegator' => 'Olcs\Controller\Lva\Delegators\ApplicationBusinessDetailsDelegator'
            ),
            'LvaLicence/BusinessDetails' => array(
                'delegator' => 'Olcs\Controller\Lva\Delegators\LicenceVariationBusinessDetailsDelegator'
            ),
            'LvaVariation/BusinessDetails' => array(
                'delegator' => 'Olcs\Controller\Lva\Delegators\LicenceVariationBusinessDetailsDelegator'
            ),
            'LvaApplication/TypeOfLicence' => array(
                'delegator' => 'Olcs\Controller\Lva\Delegators\ApplicationTypeOfLicenceDelegator'
            ),
        ),
        'invokables' => array(
            'Olcs\Ebsr\Uploads' => 'Olcs\Controller\Ebsr\UploadsController',
            'Dashboard' => 'Olcs\Controller\DashboardController',
        )
    ),
    'local_forms_path' => __DIR__ . '/../src/Form/Forms/',
    'tables' => array(
        'config' => array(
            __DIR__ . '/../src/Table/Tables/'
        )
    ),
    'service_manager' => array(
        'invokables' => array(
            'LicenceOperatingCentreAdapter'
                => 'Olcs\Controller\Lva\Adapters\LicenceOperatingCentreAdapter',
            'VariationOperatingCentreAdapter'
                => 'Olcs\Controller\Lva\Adapters\VariationOperatingCentreAdapter',
            'ApplicationOperatingCentreAdapter'
                => 'Olcs\Controller\Lva\Adapters\ApplicationOperatingCentreAdapter',
            'VehicleFormAdapter' => 'Common\Service\VehicleFormAdapter\VehicleFormAdapterService',
            'Lva\BusinessType' => 'Olcs\Service\Lva\BusinessTypeLvaService',
            'Lva\BusinessDetails' => 'Olcs\Service\Lva\BusinessDetailsLvaService',
            'ApplicationVehiclesGoodsAdapter'
                => 'Olcs\Controller\Lva\Adapters\ApplicationVehiclesGoodsAdapter',
            'VehicleFormAdapter' => 'Common\Service\VehicleFormAdapter\VehicleFormAdapterService',
            'ApplicationBusinessTypeAdapter'
                => 'Olcs\Controller\Lva\Adapters\ApplicationBusinessTypeAdapter',
            'LicenceVariationBusinessTypeAdapter'
                => 'Olcs\Controller\Lva\Adapters\LicenceVariationBusinessTypeAdapter',
            'LicenceVariationBusinessDetailsAdapter'
                => 'Olcs\Controller\Lva\Adapters\LicenceVariationBusinessDetailsAdapter',
            'ApplicationBusinessDetailsAdapter'
                => 'Olcs\Controller\Lva\Adapters\ApplicationBusinessDetailsAdapter',
            'ApplicationTypeOfLicenceAdapter'
                => 'Olcs\Controller\Lva\Adapters\ApplicationTypeOfLicenceAdapter',
        ),
        'factories' => array(
            'Olcs\InputFilter\EbsrPackInput' => 'Olcs\InputFilter\EbsrPackFactory',
            'Olcs\Service\Ebsr' => 'Olcs\Service\Ebsr',
            'navigation' => 'Zend\Navigation\Service\DefaultNavigationFactory',
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
                    )
                )
            ),
        )
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
    )
);
