<?php

return [
    'application-name' => 'internal-admin',
    'router' => [
        'routes' => [
            'admin-dashboard' => [
                'type' => 'Literal',
                'options' => [
                    'route' => '/admin',
                    'defaults' => [
                        'controller' => 'IndexController',
                        'action' => 'index',
                    ]
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'admin-scanning' => [
                        'type' => 'Literal',
                        'options' => [
                            'route' => '/scanning',
                            'defaults' => [
                                'controller' => 'IndexController',
                                'action' => 'index',
                            ]
                        ],
                    ]
                ],
            ],
        ],
    ],
    'tables' => [
        'config' => [
            __DIR__ . '/../src/Table/Tables/'
        ]
    ],
    'controllers' => [
        'invokables' => [
            'Admin\IndexController' => 'Admin\Controller\IndexController',
            'Admin\PrintingController' => 'Admin\Controller\PrintingController',
            'Admin\ScanningController' => 'Admin\Controller\ScanningController',
            'Admin\PublicationController' => 'Admin\Controller\PublicationController',
            'Admin\ContinuationController' => 'Admin\Controller\ContinuationController',
            'Admin\ReportController' => 'Admin\Controller\ReportController',
            'Admin\UserManagementController' => 'Admin\Controller\UserManagementController',
            'Admin\FinancialStandingController' => 'Admin\Controller\FinancialStandingController',
            'Admin\PublicHolidayController' => 'Admin\Controller\PublicHolidayController',
            'Admin\SystemMessageController' => 'Admin\Controller\SystemMessageController',
        ]
    ],
    'view_manager' => [
        'template_path_stack' => [
            'admin/view' => dirname(__DIR__) . '/view',
        ]
    ],
    'local_forms_path' => [__DIR__ . '/../src/Form/Forms/'],
    //-------- Start navigation -----------------
    'navigation' => [
        'default' => [
            include __DIR__ . '/navigation.config.php'
        ]
    ],
    //-------- End navigation -----------------
    'local_scripts_path' => [__DIR__ . '/../assets/js/inline/'],
];
