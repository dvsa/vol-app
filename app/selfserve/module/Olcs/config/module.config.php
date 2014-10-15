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

            'LvaApplication' => 'Olcs\Controller\Application\OverviewController',
            'LvaApplication/TypeOfLicence' => 'Olcs\Controller\Application\TypeOfLicenceController',
            'LvaApplication/BusinessType' => 'Olcs\Controller\Application\BusinessTypeController',
            'LvaApplication/BusinessDetails' => 'Olcs\Controller\Application\BusinessDetailsController',
            'LvaApplication/Addresses' => 'Olcs\Controller\Application\AddressesController',
            'LvaApplication/People' => 'Olcs\Controller\Application\PeopleController',
            'LvaApplication/OperatingCentres' => 'Olcs\Controller\Application\OperatingCentresController',
            'LvaApplication/FinancialEvidence' => 'Olcs\Controller\Application\FinancialEvidenceController',
            'LvaApplication/TransportManagers' => 'Olcs\Controller\Application\TransportManagersController',
            'LvaApplication/Vehicles' => 'Olcs\Controller\Application\VehiclesController',
            'LvaApplication/Safety' => 'Olcs\Controller\Application\SafetyController',
            'LvaApplication/FinancialHistory' => 'Olcs\Controller\Application\FinancialHistoryController',
            'LvaApplication/LicenceHistory' => 'Olcs\Controller\Application\LicenceHistoryController',
            'LvaApplication/ConvictionsPenalties' => 'Olcs\Controller\Application\ConvictionsPenaltiesController',

            'LvaLicence' => 'Olcs\Controller\Licence\OverviewController',
            'LvaLicence/TypeOfLicence' => 'Olcs\Controller\Licence\TypeOfLicenceController',
            'LvaLicence/BusinessType' => 'Olcs\Controller\Licence\BusinessTypeController',
            'LvaLicence/BusinessDetails' => 'Olcs\Controller\Licence\BusinessDetailsController',
            'LvaLicence/Addresses' => 'Olcs\Controller\Licence\AddressesController',

            'LvaVariation' => 'Olcs\Controller\Variation\OverviewController',
            'LvaVariation/TypeOfLicence' => 'Olcs\Controller\Variation\TypeOfLicenceController',
            'LvaVariation/BusinessType' => 'Olcs\Controller\Variation\BusinessTypeController',
            'LvaVariation/BusinessDetails' => 'Olcs\Controller\Variation\BusinessDetailsController',
            'LvaVariation/Addresses' => 'Olcs\Controller\Variation\AddressesController',
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
    'application_journey' => array(
        'access_keys' => array(
            'selfserve'
        ),
        'templates' => array(
            'not-found' => 'self-serve/journey/not-found',
            'navigation' => 'self-serve/journey/application/navigation',
            'main' => 'self-serve/journey/application/main',
            'layout' => 'self-serve/journey/application/layout'
        )
    ),
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
