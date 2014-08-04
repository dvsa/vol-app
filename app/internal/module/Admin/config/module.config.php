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
            'IndexController' => 'Admin\Controller\IndexController',
            'PrintingController' => 'Admin\Controller\PrintingController',
            'ScanningController' => 'Admin\Controller\ScanningController',
            'PublicationController' => 'Admin\Controller\PublicationController',
            'ContinuationController' => 'Admin\Controller\ContinuationController',
            'ReportController' => 'Admin\Controller\ReportController',
            'UserManagementController' => 'Admin\Controller\UserManagementController',
            'FinancialStandingController' => 'Admin\Controller\FinancialStandingController',
            'PublicHolidayController' => 'Admin\Controller\PublicHolidayController',
            'SystemMessageController' => 'Admin\Controller\SystemMessageController',
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
