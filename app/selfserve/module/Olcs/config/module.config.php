<?php

list($allRoutes, $controllers, $journeys) = include(
    __DIR__ . '/../../../vendor/olcs/OlcsCommon/Common/config/journeys.config.php'
);

$routes = [];

$routeArray = array_map(
    function ($file) {
        return include $file;
    },
    glob(__DIR__ . '/routes/*.routes.php')
);

foreach ($routeArray as $rs) {
    $routes += $rs;
}

$routes = array_merge($allRoutes, $routes);

return array(
    'router' => array(
        'routes' => $routes
    ),
    'controllers' => array(
        'invokables' => array(
            'Olcs\Ebsr\Uploads' => 'Olcs\Controller\Ebsr\UploadsController',
            'Olcs\Dashboard\Index' => 'Olcs\Controller\Dashboard\IndexController',
            'LicenceOverview' => 'Olcs\Controller\Licence\Details\OverviewController',
            'LicenceLicenceType' => 'Olcs\Controller\Licence\Details\LicenceType\LicenceTypeController',
            'LicenceYourBusiness' => 'Olcs\Controller\Licence\Details\YourBusiness\YourBusinessController',
            'LicenceOperatingCentres' => 'Olcs\Controller\Licence\Details\OperatingCentres\OperatingCentresController',
            'LicenceOperatingCentresAuthorisation'
                => 'Olcs\Controller\Licence\Details\OperatingCentres\AuthorisationController',
            'LicenceOperatingCentresFinancial'
                => 'Olcs\Controller\Licence\Details\OperatingCentres\FinancialController',
            'LicenceTransportManagers'
                => 'Olcs\Controller\Licence\Details\TransportManagers\TransportManagersController',
            'LicenceVehiclesSafety'
                => 'Olcs\Controller\Licence\Details\VehiclesSafety\VehiclesSafetyController',
            'LicenceVehiclesSafetyDiscsPsv'
                => 'Olcs\Controller\Licence\Details\VehiclesSafety\DiscsPsvController',
            'LicencePreviousHistory'
                => 'Olcs\Controller\Licence\Details\PreviousHistory\PreviousHistoryController',
            'LicenceReview'
                => 'Olcs\Controller\Licence\Details\Review\ReviewController',
            'LicencePay'
                => 'Olcs\Controller\Licence\Details\Pay\PayController',
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
            'Olcs\Service\Data\EbsrPack' => 'Olcs\Service\Data\EbsrPack'
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
            'layout/layout'           => __DIR__ . '/../view/self-serve/layout/base.phtml',
            'error/404'               => __DIR__ . '/../view/self-serve/error/404.phtml',
            'error/index'             => __DIR__ . '/../view/self-serve/error/index.phtml'
        ),
        'template_path_stack' => array(
            __DIR__ . '/../../../vendor/olcs/OlcsCommon/Common/view',
            __DIR__ . '/../view',
            __DIR__ . '/../view/self-serve'
        )
    ),
    'navigation' => array(
        'default' => array(
            include __DIR__ . '/navigation.config.php'
        )
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
            'Olcs\Filter\Decompress' => 'Olcs\Filter\DecompressFactory',
        ],
        'aliases' => [
            'DecompressToTmp' => 'Olcs\Filter\DecompressFactory'
        ]
    ],
    'service_api_mapping' => array(
        'endpoints' => array(
            'ebsr' => 'http://olcs-ebsr/'
        )
    )
);
