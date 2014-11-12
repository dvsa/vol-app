<?php

$sectionConfig = new \Common\Service\Data\SectionConfig();
$configRoutes = $sectionConfig->getAllRoutes();

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
            'route' => '/variation/create/:licence',
            'constraints' => array(
                'licence' => '[0-9]+',
            ),
            'defaults' => array(
                'controller' => 'LvaLicence/Overview',
                'action' => 'createVariation'
            )
        )
    )
);

$configRoutes['lva-application']['child_routes'] = array_merge(
    $configRoutes['lva-application']['child_routes'],
    array(
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
        'summary' => array(
            'type' => 'segment',
            'options' => array(
                'route' => 'summary[/]',
                'defaults' => array(
                    'controller' => 'LvaApplication/PaymentSubmission',
                    'action' => 'summary'
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
        'invokables' => array(

            'Olcs\Ebsr\Uploads' => 'Olcs\Controller\Ebsr\UploadsController',
            'Dashboard' => 'Olcs\Controller\DashboardController',

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

            'LvaLicence'                            => 'Olcs\Controller\Lva\Licence\OverviewController',
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
        )
    ),
    'local_forms_path' => __DIR__ . '/../src/Form/Forms/',
    'tables' => array(
        'config' => array(
            __DIR__ . '/../src/Table/Tables/'
        )
    ),
    'service_manager' => array(
        'factories' => array(
            'Olcs\Service\Data\EbsrPack' => 'Olcs\Service\Data\EbsrPack',
            'Olcs\InputFilter\EbsrPackInput' => 'Olcs\InputFilter\EbsrPackFactory'
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
            'layout/layout' => __DIR__ . '/../view/layout/base.phtml',
            'error/404' => __DIR__ . '/../view/error/404.phtml',
            'error/index' => __DIR__ . '/../view/error/index.phtml'
        ),
        'template_path_stack' => array(
            __DIR__ . '/../../../vendor/olcs/OlcsCommon/Common/view',
            __DIR__ . '/../view'
        )
    ),
    'navigation' => array(
        'default' => array()
    ),
    'asset_path' => '//dvsa-static.olcsdv-ap01.olcs.npm',
    'filters' => [
        'factories' => [
            'Olcs\Filter\DecompressUploadToTmp' => 'Olcs\Filter\DecompressUploadToTmpFactory',
        ],
        'aliases' => [
            'DecompressUploadToTmp' => 'Olcs\Filter\DecompressUploadToTmp'
        ]
    ],
    'service_api_mapping' => array(
        'endpoints' => array(
            'ebsr' => 'http://olcs-ebsr/'
        )
    )
);
