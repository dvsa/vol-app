<?php

use Olcs\Controller\IndexController;
use Olcs\Controller\MyDetailsController;
use Olcs\Controller\Search\SearchController;
use Olcs\Controller\UserForgotUsernameController;
use Olcs\Controller\UserRegistrationController;
use Olcs\Form\Element\SearchDateRangeFieldset;
use Olcs\Form\Element\SearchDateRangeFieldsetFactory;
use Olcs\Form\Element\SearchFilterFieldset;
use Olcs\Form\Element\SearchFilterFieldsetFactory;
use Olcs\FormService\Form\Lva as LvaFormService;

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
    'index' => array(
        'type' => 'literal',
        'options' =>  array(
            'route' => '/',
            'defaults' => array(
                'controller' => IndexController::class,
                'action' => 'index'
            )
        )
    ),
    'cookies' => array(
        'type' => 'segment',
        'options' =>  array(
            'route' => '/privacy-and-cookies[/]',
            'defaults' => array(
                'controller' => \Common\Controller\GuidesController::class,
                'action' => 'index',
                'guide' => 'privacy-and-cookies',
            )
        )
    ),
    'terms-and-conditions' => array(
        'type' => 'segment',
        'options' =>  array(
            'route' => '/terms-and-conditions[/]',
            'defaults' => array(
                'controller' => \Common\Controller\GuidesController::class,
                'action' => 'index',
                'guide' => 'terms-and-conditions',
            )
        )
    ),
    'search' => array(
        'type' => 'segment',
        'options' =>  array(
            'route' => '/search[/:index][/:action][/]',
            'defaults' => array(
                'controller' => SearchController::class,
                'action' => 'index'
            )
        )
    ),
    // Unfortunately, we need separate routes
    'search-operating-centre' => array(
        'type' => 'segment',
        'options' =>  array(
            'route' => '/search/operating-centre[/:action][/]',
            'defaults' => array(
                'controller' => SearchController::class,
                'action' => 'index',
                'index' => 'operating-centre'
            )
        )
    ),
    'search-person' => array(
        'type' => 'segment',
        'options' =>  array(
            'route' => '/search/person[/:action][/]',
            'defaults' => array(
                'controller' => SearchController::class,
                'action' => 'index',
                'index' => 'person'
            )
        )
    ),
    'search-operator' => array(
        'type' => 'segment',
        'options' =>  array(
            'route' => '/search/operator[/:action][/]',
            'defaults' => array(
                'controller' => SearchController::class,
                'action' => 'index',
                'index' => 'operator'
            )
        )
    ),
    'search-bus' => array(
        'type' => 'segment',
        'options' =>  array(
            'route' => '/search/bus[/:action][/]',
            'defaults' => array(
                'controller' => SearchController::class,
                'action' => 'index',
                'index' => 'bus'
            )
        )
    ),
    'search-publication' => array(
        'type' => 'segment',
        'options' =>  array(
            'route' => '/search/publication[/:action][/]',
            'defaults' => array(
                'controller' => SearchController::class,
                'action' => 'index',
                'index' => 'publication'
            )
        )
    ),
    'search-vehicle-external' => array(
        'type' => 'segment',
        'options' =>  array(
            'route' => '/search/vehicle-external[/:action][/]',
            'defaults' => array(
                'controller' => SearchController::class,
                'action' => 'index',
                'index' => 'vehicle-external'
            )
        )
    ),
    'busreg-registrations' => array(
        'type' => 'segment',
        'options' =>  array(
            'route' =>
                '/busreg-registrations[/]',
            'defaults' => array(
                'controller' => 'Olcs\BusReg\BusRegRegistrations',
                'action' => 'index',
            )
        )
    ),
    'bus-registration' => array(
        'type' => 'segment',
        'options' =>  array(
            'route' =>
                '/bus-registration[/]',
            'defaults' => array(
                'controller' => Olcs\Controller\Ebsr\BusRegApplicationsController::class,
                'action' => 'index',
            )
        ),
        'may_terminate' => true,
        'child_routes' => [
            'details' => array(
                'type' => 'segment',
                'options' =>  array(
                    'route' => 'details[/busreg/:busRegId][/]',
                    'defaults' => array(
                        'action' => 'details',
                    ),
                    'constraints' => [
                        'busRegId' => '[0-9]+',
                        'subType' => '[a-z_]+',
                        'status' => '[a-z_]+',
                        'page' => '[0-9]+',
                    ]
                )
            ),
            'ebsr' => array(
                'type' => 'segment',
                'options' =>  array(
                    'route' => 'ebsr[/:action][/]',
                    'defaults' => array(
                        'controller' => 'Olcs\Ebsr\Uploads',
                        'action' => 'index'
                    )
                )
            ),
        ]
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
                'action' => 'index',
            ),
        ),
        'may_terminate' => true,
        'child_routes' => array(
            'pay' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => 'pay/:fee[/]',
                    'constraints' => array(
                        'fee' => '[0-9\,]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Fees',
                        'action' => 'pay-fees',
                    ),
                ),
            ),
            'result' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => 'result[/]',
                    'defaults' => array(
                        'controller' => 'Fees',
                        'action' => 'handle-result',
                    ),
                ),
            ),
            'receipt' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => 'receipt/:reference[/:action][/]',
                    'constraints' => array(
                        'reference' => 'OLCS-[0-9A-F\-]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Fees',
                        'action' => 'receipt',
                    ),
                ),
            ),
        ),
    ),
    'correspondence' => array(
        'type' => 'segment',
        'options' => array(
            'route' => '/correspondence[/]',
            'defaults' => array(
                'controller' => 'Correspondence',
                'action' => 'index'
            )
        ),
        'may_terminate' => true,
        'child_routes' => array(
            'access' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => 'access/:correspondenceId[/]',
                    'defaults' => array(
                        'controller' => 'Correspondence',
                        'action' => 'accessCorrespondence'
                    ),
                )
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
    'user-registration' => array(
        'type' => 'segment',
        'options' => array(
            'route' => '/register[/]',
            'defaults' => array(
                'controller' => UserRegistrationController::class,
                'action' => 'add'
            )
        )
    ),
    'user-forgot-username' => array(
        'type' => 'segment',
        'options' => array(
            'route' => '/forgot-username[/]',
            'defaults' => array(
                'controller' => UserForgotUsernameController::class,
                'action' => 'index'
            )
        )
    ),
    'manage-user' => array(
        'type' => 'segment',
        'options' => array(
            'route' => '/manage-user[/:action][/:id][/]',
            'constraints' => array(
                'action' => '(index|add|edit|delete)',
                'id' => '[0-9]+',
            ),
            'defaults' => array(
                'controller' => 'User',
                'action' => 'index'
            )
        )
    ),
    'my-account' => array(
        'type' => 'segment',
        'options' => array(
            'route' => '/my-account[/]',
            'defaults' => array(
                'controller' => MyDetailsController::class,
                'action' => 'edit'
            )
        )
    ),
    'entity-view' => array(
        'type' => 'segment',
        'options' =>  array(
            'route' => '/view-details/:entity[/:entityId][/]',
            'constraints' => array(
                'entity' => '(licence)',
                'entityId' => '[0-9]+',
            ),
            'defaults' => array(
                'controller' => Olcs\Controller\Entity\ViewController::class,
                'action' => 'details'
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
        'declaration' => array(
            'type' => 'segment',
            'options' => array(
                'route' => 'declaration[/]',
                'defaults' => array(
                    'controller' => 'DeclarationFormController',
                    'action' => 'index'
                )
            )
        ),
        'pay-and-submit' => array(
            'type' => 'segment',
            'options' => array(
                'route' => 'pay-and-submit[/]',
                'defaults' => array(
                    'controller' => 'LvaApplication/PaymentSubmission',
                    'action' => 'payAndSubmit'
                ),
            )
        ),
        'payment' => array(
            'type' => 'segment',
            'options' => array(
                'route' => 'payment[/stored-card-reference/:storedCardReference][/]',
                'defaults' => array(
                    'controller' => 'LvaApplication/PaymentSubmission',
                    'action' => 'index'
                ),
                'constraints' => array(
                    'storedCardReference' => 'OLCS-[0-9A-F\-]+',
                ),
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
                'route' => 'summary[/:reference][/]',
                'constraints' => array(
                    'reference' => 'OLCS-[0-9A-F\-]+',
                ),
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
        ),
        'cancel' => array(
            'type' => 'segment',
            'options' => array(
                'route' => 'cancel[/]',
                'defaults' => array(
                    'controller' => 'LvaApplication',
                    'action' => 'cancel'
                )
            )
        ),
        'withdraw' => array(
            'type' => 'segment',
            'options' => array(
                'route' => 'withdraw[/]',
                'defaults' => array(
                    'controller' => 'LvaApplication',
                    'action' => 'withdraw'
                )
            )
        ),
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
                'route' => 'summary[/:reference][/]',
                'constraints' => array(
                    'reference' => 'OLCS-[0-9A-F\-]+',
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
                'route' => 'pay-and-submit[/]',
                'defaults' => array(
                    'controller' => 'LvaVariation/PaymentSubmission',
                    'action' => 'payAndSubmit'
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
                    'storedCardReference' => 'OLCS-[0-9A-F\-]+',
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
                    'label' => 'dashboard-nav-correspondence',
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
            'label' => 'search-list-vehicle-operators',
            'route' => 'search-operator',
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
            'id' => 'search-publication',
            'label' => 'search-list-publications',
            'route' => 'search-publication',
            'use_route_match' => true,
            'class' => 'search-navigation__item',
        ),
        array(
            'id' => 'search-vehicle-external',
            'label' => 'search-list-vehicles',
            'route' => 'search-vehicle-external',
            'use_route_match' => true,
            'class' => 'search-navigation__item',
        ),
        array(
            'id' => 'search-operating-centre',
            'label' => 'search-list-operating-centres',
            'route' => 'search-operating-centre',
            'use_route_match' => true,
            'class' => 'search-navigation__item',
        ),
        array(
            'id' => 'search-person',
            'label' => 'search-list-people',
            'route' => 'search-person',
            'use_route_match' => true,
            'class' => 'search-navigation__item',
        )
    )
);

$myAccountNav = array(
    'id' => 'my-account',
    'label' => 'My account',
    'route' => 'my-account',
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
            'LvaApplication/Review'                 => \Common\Controller\Lva\ReviewController::class,
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
            'LvaVariation/TaxiPhv'                  => 'Olcs\Controller\Lva\Variation\TaxiPhvController',
            'LvaVariation/Discs'                    => 'Olcs\Controller\Lva\Variation\DiscsController',
            'LvaVariation/Undertakings'             => 'Olcs\Controller\Lva\Variation\UndertakingsController',
            'LvaVariation/FinancialEvidence'        => 'Olcs\Controller\Lva\Variation\FinancialEvidenceController',
            'LvaVariation/VehiclesDeclarations'     => 'Olcs\Controller\Lva\Variation\VehiclesDeclarationsController',
            'LvaVariation/FinancialHistory'         => 'Olcs\Controller\Lva\Variation\FinancialHistoryController',
            'LvaVariation/ConvictionsPenalties'     => 'Olcs\Controller\Lva\Variation\ConvictionsPenaltiesController',
            'LvaVariation/Summary'                  => 'Olcs\Controller\Lva\Variation\SummaryController',
            'LvaVariation/PaymentSubmission'        => 'Olcs\Controller\Lva\Variation\PaymentSubmissionController',
            'LvaVariation/Review'                   => \Common\Controller\Lva\ReviewController::class,
        ),
        'invokables' => array(
            'DeclarationFormController' => \Olcs\Controller\Lva\DeclarationFormController::class,
            'Olcs\Ebsr\Uploads' => 'Olcs\Controller\Ebsr\UploadsController',
            Olcs\Controller\Ebsr\BusRegApplicationsController::class =>
                Olcs\Controller\Ebsr\BusRegApplicationsController::class,
            'Olcs\BusReg\BusRegRegistrations' => 'Olcs\Controller\BusReg\BusRegRegistrationsController',
            'Dashboard' => Olcs\Controller\DashboardController::class,
            'Fees' => 'Olcs\Controller\FeesController',
            'Correspondence' => 'Olcs\Controller\CorrespondenceController',
            'User' => 'Olcs\Controller\UserController',
            IndexController::class => IndexController::class,
            UserForgotUsernameController::class => UserForgotUsernameController::class,
            UserRegistrationController::class => UserRegistrationController::class,
            MyDetailsController::class => MyDetailsController::class,
            SearchController::class => SearchController::class,
            'Search\Result' => 'Olcs\Controller\Search\ResultController',
            Olcs\Controller\Entity\ViewController::class => Olcs\Controller\Entity\ViewController::class,
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
            'ApplicationPeopleAdapter'
                => 'Olcs\Controller\Lva\Adapters\ApplicationPeopleAdapter',
            'LicencePeopleAdapter'
                => 'Olcs\Controller\Lva\Adapters\LicencePeopleAdapter',
            'VariationPeopleAdapter'
                => 'Olcs\Controller\Lva\Adapters\VariationPeopleAdapter',
            'DashboardProcessingService'
                => 'Olcs\Service\Processing\DashboardProcessingService',
            'Email\TransportManagerCompleteDigitalForm'
                => 'Olcs\Service\Email\TransportManagerCompleteDigitalForm',
        ),
        'factories' => array(
            'CookieBannerListener' => \Olcs\Mvc\CookieBannerListener::class,
            'CookieBanner' => \Olcs\Mvc\CookieBanner::class,
            'Olcs\InputFilter\EbsrPackInput' => 'Olcs\InputFilter\EbsrPackFactory',
            'Olcs\Service\Ebsr' => 'Olcs\Service\Ebsr',
            'navigation' => 'Zend\Navigation\Service\DefaultNavigationFactory',
            'Olcs\Navigation\DashboardNavigation' => 'Olcs\Navigation\DashboardNavigationFactory',
            'LicenceTransportManagerAdapter' =>
                \Olcs\Controller\Lva\Factory\Adapter\LicenceTransportManagerAdapterFactory::class,
            'VariationTransportManagerAdapter' =>
                \Olcs\Controller\Lva\Factory\Adapter\VariationTransportManagerAdapterFactory::class,
        )
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
            SearchDateRangeFieldset::class => SearchDateRangeFieldsetFactory::class
        ],
        'aliases' => [
            'SearchFilterFieldset' => SearchFilterFieldset::class,
            'SearchDateRangeFieldset' => SearchDateRangeFieldset::class
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
        'invokables' => array(
            'returnToAddress' => \Olcs\View\Helper\ReturnToAddress::class,
            'generatePeopleList' => \Olcs\View\Helper\GeneratePeopleList::class
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
            __DIR__ . '/../../../vendor/olcs/OlcsCommon/Common/view',
            __DIR__ . '/../view'
        )
    ),
    'navigation' => array(
        'default' => array(
            $applicationNavigation,
            $searchNavigation,
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
                        'id' => 'selfserve-topnav-search',
                        'label' => 'search',
                        'route' => 'search',
                        'class' => 'proposition-nav__item',
                    ),
                    array(
                        'id' => 'selfserve-topnav-bus-registration',
                        'label' => 'selfserve-dashboard-topnav-bus-registrations',
                        'route' => 'bus-registration',
                        'action' => 'index',
                        'use_route_match' => true,
                        'class' => 'proposition-nav__item',
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
                        'id' => 'selfserve-topnav-my-account',
                        'label' => 'selfserve-topnav-my-account',
                        'route' => 'my-account',
                        'class' => 'proposition-nav__item',
                    )
                ),
            ),
            array(
                'id' => 'signin',
                'label' => 'back-to-sign-in',
                'route' => 'auth/login',
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
            'lva-variation-vehicles_psv' => LvaFormService\VariationPsvVehicles::class,
            'lva-licence-goods-vehicles' => LvaFormService\LicenceGoodsVehicles::class,
            'lva-variation-goods-vehicles' => LvaFormService\VariationGoodsVehicles::class,
            'lva-licence-goods-vehicles-add-vehicle' => LvaFormService\GoodsVehicles\AddVehicle::class,
            'lva-variation-goods-vehicles-add-vehicle' => LvaFormService\GoodsVehicles\AddVehicle::class,
            'lva-application-goods-vehicles-edit-vehicle' => LvaFormService\GoodsVehicles\EditVehicle::class,
            'lva-licence-goods-vehicles-edit-vehicle' => LvaFormService\GoodsVehicles\EditVehicle::class,
            'lva-variation-goods-vehicles-edit-vehicle' => LvaFormService\GoodsVehicles\EditVehicle::class,
            // External common psv vehicles vehicle form service
            'lva-psv-vehicles-vehicle' => LvaFormService\PsvVehiclesVehicle::class,
            // External common vehicles vehicle form service (Goods and PSV)
            'lva-vehicles-vehicle' => LvaFormService\VehiclesVehicle::class,

            'lva-licence-people' => LvaFormService\People\LicencePeople::class,
            'lva-variation-people' => LvaFormService\People\VariationPeople::class,
            'lva-application-people' => LvaFormService\People\ApplicationPeople::class,

            'lva-licence-sole_trader' => LvaFormService\People\SoleTrader\LicenceSoleTrader::class,
            'lva-variation-sole_trader' => LvaFormService\People\SoleTrader\VariationSoleTrader::class,
            'lva-application-sole_trader' => LvaFormService\People\SoleTrader\ApplicationSoleTrader::class,

            'lva-licence-transport_managers' => LvaFormService\TransportManager\LicenceTransportManager::class,
            'lva-variation-transport_managers' => LvaFormService\TransportManager\VariationTransportManager::class,
            'lva-application-transport_managers' => LvaFormService\TransportManager\ApplicationTransportManager::class,

            'lva-licence-discs' => LvaFormService\PsvDiscs\LicencePsvDiscs::class,
            'lva-variation-discs' => LvaFormService\PsvDiscs\VariationPsvDiscs::class,

            'lva-licence-taxi_phv' => LvaFormService\LicenceTaxiPhv::class,
            'lva-application-taxi_phv' => LvaFormService\ApplicationTaxiPhv::class,

            'lva-licence-trailers' => LvaFormService\LicenceTrailers::class,
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

                'lva-application/transport_manager_details*' => ['selfserve-tm'],
                'lva-variation/transport_manager_details*' => ['selfserve-tm'],
                'lva-*' => ['selfserve-lva'],
                'search*' => ['*'],
                'index' => ['*'],
                'user-registration' => ['*'],
                'user-forgot-username' => ['*'],
                'cookies' => ['*'],
                'terms-and-conditions' => ['*'],
                'not-found' => ['*'],
                'server-error' => ['*'],
                '*' => ['selfserve-user'],
            ]
        ]
    ],
    'business_rule_manager' => [
        'invokables' => [
            'UserMappingContactDetails' => 'Olcs\BusinessRule\Rule\UserMappingContactDetails',
        ]
    ],
    'business_service_manager' => [
        'invokables' => [
            'Lva\LicenceAddresses' => 'Olcs\BusinessService\Service\Lva\LicenceVariationAddresses',
            'Lva\VariationAddresses' => 'Olcs\BusinessService\Service\Lva\LicenceVariationAddresses',
            'Lva\AddressesChangeTask' => 'Olcs\BusinessService\Service\Lva\AddressesChangeTask',
        ]
    ],
    'date_settings' => [
        'date_format' => 'd M Y',
        'datetime_format' => 'd M Y H:i',
        'datetimesec_format' => 'd M Y H:i:s'
    ],
    'my_account_route' => 'my-account',
);
