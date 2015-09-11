<?php

return [
    'router' => [
        'routes' => [
            'admin-dashboard' => [
                'type' => 'Literal',
                'options' => [
                    'route' => '/admin',
                    'defaults' => [
                        'controller' => 'Admin\IndexController',
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
                                'controller' => 'Admin\ScanningController',
                                'action' => 'index',
                            ]
                        ],
                    ],
                    'admin-printing' => [
                        'type' => 'Literal',
                        'options' => [
                            'route' => '/printing',
                            'defaults' => [
                                'controller' => 'Admin\PrintingController',
                                'action' => 'index',
                            ]
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'irfo-stock-control' => [
                                'type' => 'segment',
                                'options' => [
                                    'route' => '/irfo-stock-control[/:action][/:id]',
                                    'constraints' => [
                                        'id' => '([0-9]+,?)+',
                                        'action' => '(index|add|in-stock|issued|void|returned)'
                                    ],
                                    'defaults' => [
                                        'controller' => 'Admin\IrfoStockControlController',
                                        'action' => 'index'
                                    ]
                                ]
                            ]
                        ]
                    ],
                    'admin-publication' => [
                        'type' => 'Literal',
                        'options' => [
                            'route' => '/publication',
                            'defaults' => [
                                'controller' => 'Admin\PublicationController',
                                'action' => 'redirect',
                            ]
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'pending' => [
                                'type' => 'segment',
                                'options' => [
                                    'route' => '/pending[/:action][/:publication]',
                                    'constraints' => [
                                        'publication' => '[0-9]+',
                                        'action' => '[a-z]+'
                                    ],
                                    'defaults' => [
                                        'controller' => 'Admin\PublicationController',
                                        'action' => 'index'
                                    ]
                                ]
                            ],
                            'published' => [
                                'type' => 'literal',
                                'options' => [
                                    'route' => '/published',
                                    'defaults' => [
                                        'controller' => 'Admin\PublicationController',
                                        'action' => 'published',
                                        'index' => 'publication'
                                    ]
                                ]
                            ],
                            'recipient' => [
                                'type' => 'segment',
                                'options' => [
                                    'route' => '/recipient[/:action][/:id]',
                                    'constraints' => [
                                        'id' => '[0-9]+',
                                        'action' => '(index|add|edit|delete)'
                                    ],
                                    'defaults' => [
                                        'controller' => 'Admin\RecipientController',
                                        'action' => 'index'
                                    ]
                                ]
                            ]
                        ]
                    ],
                    'admin-my-account' => [
                        'type' => 'Literal',
                        'options' => [
                            'route' => '/my-account',
                            'defaults' => [
                                'controller' => 'Admin\MyDetailsController',
                                'action' => 'index',
                            ]
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'details' => [
                                'type' => 'literal',
                                'options' => [
                                    'route' => '/details',
                                    'defaults' => [
                                        'controller' => 'Admin\MyDetailsController',
                                        'action' => 'edit'
                                    ]
                                ]
                            ],
                            'change-password' => [
                                'type' => 'literal',
                                'options' => [
                                    'route' => '/password',
                                    'defaults' => [
                                        'controller' => 'Admin\MyDetailsController',
                                        'action' => 'password'
                                    ]
                                ]
                            ]
                        ]
                    ],
                    'admin-continuation' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/continuation[/]',
                            'defaults' => [
                                'controller' => 'Admin\ContinuationController',
                                'action' => 'index',
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'detail' => [
                                'type' => 'Segment',
                                'options' => [
                                    'route' => 'detail/:id[/:action[/:child_id]][/]',
                                    'defaults' => [
                                        'controller' => 'Admin\ContinuationController',
                                        'action' => 'detail',
                                    ],
                                ],
                            ],
                            'checklist-reminder' => [
                                'type' => 'Segment',
                                'options' => [
                                    'route' => 'checklist-reminder[/:action[/:child_id]][/]',
                                    'defaults' => [
                                        'controller' => 'Admin\ContinuationChecklistReminderController',
                                        'action' => 'index',
                                    ],
                                ],
                            ],
                        ]
                    ],
                    'admin-report' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/report',
                            'defaults' => [
                                'controller' => 'Admin\ReportController',
                                'action' => 'index',
                            ]
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'ch-alerts' => [
                                'type' => 'Segment',
                                'options' => [
                                    'route' => '/ch-alerts[/:action][/:id][/]',
                                    'constraints' => [
                                        'id' => '[0-9\,]+'
                                    ],
                                    'defaults' => [
                                        'controller' => 'Admin\CompaniesHouseAlertController',
                                        'action' => 'index',
                                    ]
                                ],
                            ],
                        ],
                    ],
                    'admin-user-management' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/user-management/users[/:action][/:user]',
                            'constraints' => [
                                'user' => '[0-9]+',
                                'action' => '(index|add|edit|delete)'
                            ],
                            'defaults' => [
                                'controller' => 'Admin\UserManagementController',
                                'action' => 'index'
                            ]
                        ]
                    ],
                    'admin-team-management' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/user-management/teams[/:action][/:team]',
                            'constraints' => [
                                'user' => '[0-9]+',
                                'action' => '(index|add|edit|delete)'
                            ],
                            'defaults' => [
                                'controller' => 'Admin\TeamsController',
                                'action' => 'index'
                            ]
                        ]
                    ],
                    'admin-printer-management' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/user-management/printers[/:action][/:printer]',
                            'constraints' => [
                                'user' => '[0-9]+',
                                'action' => '(index|add|edit|delete)'
                            ],
                            'defaults' => [
                                'controller' => 'Admin\PrintersController',
                                'action' => 'index'
                            ]
                        ]
                    ],
                    'admin-partner-management' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/partner[/:action][/:id]',
                            'constraints' => [
                                'id' => '[0-9]+',
                                'action' => '(index|add|edit|delete)'
                            ],
                            'defaults' => [
                                'controller' => 'Admin\PartnerController',
                                'action' => 'index'
                            ]
                        ]
                    ],
                    'admin-financial-standing' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/financial-standing[/:action][/:id][/]',
                            'constraints' => [
                                'id' => '[0-9\,]+'
                            ],
                            'defaults' => [
                                'controller' => 'Crud\FinancialStandingController',
                                'action' => 'index',
                            ]
                        ],
                    ],
                    'admin-public-holiday' => [
                        'type' => 'Literal',
                        'options' => [
                            'route' => '/public-holiday',
                            'defaults' => [
                                'controller' => 'Admin\PublicHolidayController',
                                'action' => 'index',
                            ]
                        ],
                    ],
                    'admin-system-message' => [
                        'type' => 'Literal',
                        'options' => [
                            'route' => '/system-message',
                            'defaults' => [
                                'controller' => 'Admin\SystemMessageController',
                                'action' => 'index',
                            ]
                        ],
                    ],
                    'admin-disc-printing' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => '/disc-printing[/success[/:success]]',
                            'defaults' => [
                                'controller' => 'Admin\DiscPrintingController',
                                'action' => 'index',
                            ],
                            'constraints' => [
                                'licence' => '[a-z]+'
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'disc_prefixes' => [
                                'type' => 'segment',
                                'options' => [
                                    'route' => '/disc-prefixes-list',
                                    'defaults' => [
                                        'controller' => 'Admin\DiscPrintingController',
                                        'action' => 'disc-prefixes-list'
                                    ]
                                ]
                            ],
                            'disc_numbering' => [
                                'type' => 'segment',
                                'options' => [
                                    'route' => '/disc-numbering',
                                    'defaults' => [
                                        'controller' => 'Admin\DiscPrintingController',
                                        'action' => 'disc-numbering'
                                    ]
                                ]
                            ],
                            'disc_printing' => [
                                'type' => 'segment',
                                'options' => [
                                    'route' => '/confirm-disc-printing',
                                    'defaults' => [
                                        'controller' => 'Admin\DiscPrintingController',
                                        'action' => 'confirm-disc-printing'
                                    ]
                                ]
                            ],
                        ]
                    ],
                    'admin-payment-processing' => [
                        'type' => 'Literal',
                        'options' => [
                            'route' => '/payment-processing',
                            'defaults' => [
                                'controller' => 'Admin\PaymentProcessingController',
                                'action' => 'redirect',
                            ]
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'cpid-class' => [
                                'type' => 'segment',
                                'options' => [
                                    'route' => '/cpid-classification[/:status]',
                                    'defaults' => [
                                        'controller' => 'Admin\PaymentProcessingController',
                                        'action' => 'cpidClassification',
                                        'status' => null
                                    ]
                                ]
                            ],
                            'cpid-exports' => [
                                'type' => 'segment',
                                'options' => [
                                    'route' => '/cpid-exports',
                                    'defaults' => [
                                        'controller' => 'Admin\PaymentProcessingController',
                                        'action' => 'cpidExports'
                                    ]
                                ]
                            ],
                            'misc-fees' => [
                                'type' => 'segment',
                                'options' => [
                                    'route' => '/misc-fees',
                                    'defaults' => [
                                        'controller' => 'Admin\PaymentProcessingController',
                                        'action' => 'index'
                                    ]
                                ],
                                'may_terminate' => true,
                                'child_routes' => [
                                    'fee_action' => [
                                        'type' => 'segment',
                                        'options' => [
                                            'route' => '/:action/[:fee]',
                                            'constraints' => [
                                                'fee' => '([0-9]+,?)+',
                                            ],
                                        ],
                                        'may_terminate' => true,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'tables' => [
        'config' => [
            __DIR__ . '/../src/Table/Tables/'
        ]
    ],
    'crud_service_manager' => [
        'invokables' => [
            'FinancialStandingCrudService' => 'Admin\Service\Crud\FinancialStandingCrudService',
        ]
    ],
    'crud-config' => [
        /**
         * Sample crud config
         * 'route/match/name' => [
         *    // Define which actions are available, and whether they require rows to be selected
         *   'add' => ['requireRows' => false],
         *   'edit' => ['requireRows' => true]
         * ]
         */
    ],
    /**
     * This config array contains the config for dynamic / generic controllers
     */
    'crud_controller_config' => [
        'Crud\FinancialStandingController' => [
            'index' => [
                'pageLayout' => 'admin-layout',
                'table' => 'admin-financial-standing',
                'route' => '',
                'scripts' => [
                    'table-actions'
                ]
            ],
            'add' => [
                'pageLayout' => 'admin-layout',
                'table' => 'admin-financial-standing',
                'route' => ''
            ],
            'edit' => [
                'pageLayout' => 'admin-layout',
                'table' => 'admin-financial-standing',
                'route' => ''
            ]
        ],
    ],
    'controllers' => [
        'factories' => [
            // Crud controllers
            'Crud\FinancialStandingController' => '\Common\Controller\Crud\GenericCrudControllerFactory',
        ],
        'invokables' => [
            'Admin\IndexController' => 'Admin\Controller\IndexController',
            'Admin\PrintingController' => 'Admin\Controller\PrintingController',
            'Admin\IrfoStockControlController' => 'Admin\Controller\IrfoStockControlController',
            'Admin\ScanningController' => 'Admin\Controller\ScanningController',
            'Admin\PublicationController' => 'Admin\Controller\PublicationController',
            'Admin\RecipientController' => 'Admin\Controller\RecipientController',
            'Admin\ContinuationController' => 'Admin\Controller\ContinuationController',
            'Admin\ReportController' => 'Admin\Controller\ReportController',
            'Admin\UserManagementController' => 'Admin\Controller\UserManagementController',
            'Admin\PublicHolidayController' => 'Admin\Controller\PublicHolidayController',
            'Admin\SystemMessageController' => 'Admin\Controller\SystemMessageController',
            'Admin\DiscPrintingController' => 'Admin\Controller\DiscPrintingController',
            'Admin\MyDetailsController' => 'Admin\Controller\MyDetailsController',
            'Admin\PaymentProcessingController' => 'Admin\Controller\PaymentProcessingController',
            'Admin\PartnerController' => 'Admin\Controller\PartnerController',
            'Admin\ContinuationChecklistReminderController' =>
                'Admin\Controller\ContinuationChecklistReminderController',
            'Admin\CompaniesHouseAlertController' => 'Admin\Controller\CompaniesHouseAlertController',
        ]
    ],
    'view_manager' => [
        'template_path_stack' => [
            'admin/view' => dirname(__DIR__) . '/view',
        ]
    ],
    'service_manager' => array(
        'aliases' => [
            'user-details' => 'UserDetailsNavigation'
        ],
        'factories' => array(
            'UserDetailsNavigation' => 'Admin\Navigation\UserDetailsNavigationFactory',
        )
    ),
    'local_forms_path' => [__DIR__ . '/../src/Form/Forms/'],
    //-------- Start navigation -----------------
    'navigation' => array(
        'default' => array(
            include __DIR__ . '/navigation.config.php'
        ),
        'user-details' => array(
            include __DIR__ . '/navigation-user-details.config.php'
        )
    ),
    //-------- End navigation -----------------
    'local_scripts_path' => [__DIR__ . '/../assets/js/inline/'],
];
