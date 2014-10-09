<?php

$sectionConfig = new \Common\Service\Data\SectionConfig();

return array(
    'router' => array(
        'routes' => $sectionConfig->getAllRoutes(),
    ),
    'controllers' => array(
        'invokables' => array(
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

            'LvaVariation' => 'Olcs\Controller\Variation\OverviewController',
            'LvaVariation/TypeOfLicence' => 'Olcs\Controller\Variation\TypeOfLicenceController',
        )
    ),
    'local_forms_path' => __DIR__ . '/../src/Form/Forms/',
    'tables' => array(
        'config' => array(
            __DIR__ . '/../src/Table/Tables/'
        )
    ),
    'service_manager' => array(
        'factories' => array()
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
    )
);
