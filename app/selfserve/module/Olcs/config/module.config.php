<?php

return array(
    'router' => array(
        'routes' => array(
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
                        'controller' => 'Application',
                        'action' => 'create'
                    )
                )
            ),
            'application' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/application/:id[/]',
                    'constraints' => array(
                        'id' => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'Application',
                        'action' => 'index'
                    )
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'type-of-licence' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => 'type-of-licence[/]',
                            'defaults' => array(
                                'controller' => 'Application/TypeOfLicence',
                                'action' => 'index'
                            )
                        )
                    )
                )
            ),
            'licence' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/licence/:id[/]',
                    'constraints' => array(
                        'id' => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'Licence',
                        'action' => 'index'
                    )
                ),
                'may_terminate' => true,
                // child_routes => array()
            )

        )
    ),
    'controllers' => array(
        'invokables' => array(
            'Dashboard' => 'Olcs\Controller\DashboardController',
            'Application' => 'Olcs\Controller\Application\ApplicationController',
            'Application/TypeOfLicence' => 'Olcs\Controller\Application\TypeOfLicenceController',
            'Licence' => 'Olcs\Controller\Licence\LicenceController',
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
