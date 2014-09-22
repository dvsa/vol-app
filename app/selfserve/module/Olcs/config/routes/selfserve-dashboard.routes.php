<?php

return array(
    'application_start' => array(
        'type' => 'segment',
        'options' => array(
            'route' => '/dashboard[/:action]',
            'defaults' => array(
                'controller' => 'Olcs\Dashboard\Index',
                'action' => 'index'
            )
        ),
    ),
    'licence' => array(
        'type' => 'segment',
        'options' => array(
            'route' => '/licence/:licence',
            'constraints' => array(
                'licence' => '[0-9]+'
            )
        ),
        // @todo Decide where to send /licence/x to by default, then change this to true
        'may_terminate' => false,
        'child_routes' => array(
            'overview' => array(
                'type' => 'literal',
                'options' => array(
                    'route' => '/overview',
                    'defaults' => array(
                        'controller' => 'LicenceOverview',
                        'action' => 'index'
                    )
                )
            ),
            'licence_type' => array(
                'type' => 'literal',
                'options' => array(
                    'route' => '/licence_type',
                    'defaults' => array(
                        'controller' => 'LicenceLicenceType',
                        'action' => 'index'
                    )
                )
            ),
            'your_business' => array(
                'type' => 'literal',
                'options' => array(
                    'route' => '/your_business',
                    'defaults' => array(
                        'controller' => 'LicenceYourBusiness',
                        'action' => 'index'
                    )
                )
            ),
            'operating_centres' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/operating_centres',
                    'defaults' => array(
                        'controller' => 'LicenceOperatingCentres',
                        'action' => 'index'
                    )
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'authorisation' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/authorisation[/:action][/:id]',
                            'contraints' => array(
                                'id' => '[0-9]+'
                            ),
                            'defaults' => array(
                                'controller' => 'LicenceOperatingCentresAuthorisation',
                                'action' => 'index'
                            )
                        )
                    ),
                    'financial' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/financial',
                            'defaults' => array(
                                'controller' => 'LicenceOperatingCentresFinancial',
                                'action' => 'index'
                            )
                        )
                    )
                )
            ),
            'transport_managers' => array(
                'type' => 'literal',
                'options' => array(
                    'route' => '/transport_managers',
                    'defaults' => array(
                        'controller' => 'LicenceTransportManagers',
                        'action' => 'index'
                    )
                )
            ),
            'vehicles_safety' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/vehicles_safety',
                    'defaults' => array(
                        'controller' => 'LicenceVehicleSafety',
                        'action' => 'index'
                    )
                )
            ),
            'previous_history' => array(
                'type' => 'literal',
                'options' => array(
                    'route' => '/previous_history',
                    'defaults' => array(
                        'controller' => 'LicencePreviousHistory',
                        'action' => 'index'
                    )
                )
            ),
            'review' => array(
                'type' => 'literal',
                'options' => array(
                    'route' => '/review',
                    'defaults' => array(
                        'controller' => 'LicenceReview',
                        'action' => 'index'
                    )
                )
            ),
            'pay' => array(
                'type' => 'literal',
                'options' => array(
                    'route' => '/pay',
                    'defaults' => array(
                        'controller' => 'LicencePay',
                        'action' => 'index'
                    )
                )
            )
        )
    ),
    // @todo replace this with the real varitation route
    'application-variation' => array(
        'type' => 'segment',
        'options' => array(
            'route' => '/variation',
            'defaults' => array(
            )
        )
    )
);
