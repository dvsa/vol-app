<?php

use Zend\Mvc\Router\Http\Segment;

return [
    'router' => [
        'routes' => [
            'admin-dashboard' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/admin[/]',
                    'defaults' => [
                        'controller' => Admin\Controller\IndexController::class,
                        'action' => 'index',
                    ]
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'task-allocation-rules' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' =>
                                'task-allocation-rules[/:action][/:id][/alpha-split/:alpha-split][/team/:team][/]',
                            'constraints' => [
                                'action' => '(add|edit|delete|AddAlphasplit|EditAlphasplit|DeleteAlphasplit)',
                                'id' => '[0-9\,]+',
                                'alpha-split' => '[0-9]+',
                                'team' => '[0-9]+',
                            ],
                            'defaults' => [
                                'controller' => 'Admin\TaskAllocationRulesController',
                                'action' => 'index',
                            ]
                        ],
                    ],
                    'admin-scanning' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => 'scanning[/]',
                            'defaults' => [
                                'controller' => 'Admin\ScanningController',
                                'action' => 'index',
                            ]
                        ],
                    ],
                    // * @todo remove after task allocation rules will be tested (OLCS-6844 & OLCS-12638)
                    'admin-create-task-temp' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => 'create-task-temp[/]',
                            'defaults' => [
                                'controller' => 'Admin\CreateTaskTempController',
                                'action' => 'index',
                            ]
                        ],
                    ],
                    'admin-printing' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => 'printing[/]',
                            'defaults' => [
                                'controller' => 'Admin\PrintingController',
                                'action' => 'jump',
                            ]
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'irfo-stock-control' => [
                                'type' => 'segment',
                                'options' => [
                                    'route' => 'irfo-stock-control[/:action][/:id][/]',
                                    'constraints' => [
                                        'id' => '([0-9]+,?)+',
                                        'action' => '(index|add|in-stock|issued|void|returned)'
                                    ],
                                    'defaults' => [
                                        'controller' => 'Admin\IrfoStockControlController',
                                        'action' => 'index'
                                    ]
                                ]
                            ],
                            'admin-printer-management' => [
                                'type' => 'Segment',
                                'options' => [
                                    'route' => 'printers[/:action][/:printer][/]',
                                    'constraints' => [
                                        'user' => '[0-9]+',
                                        'action' => '(index|add|edit|delete)'
                                    ],
                                    'defaults' => [
                                        'controller' => 'Admin\PrintingController',
                                        'action' => 'index'
                                    ]
                                ]
                            ],
                        ]
                    ],
                    'admin-publication' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => 'publication[/]',
                            'defaults' => [
                                'controller' => 'Admin\PublicationController',
                                'action' => 'jump',
                            ]
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'pending' => [
                                'type' => 'segment',
                                'options' => [
                                    'route' => 'pending[/:action][/:id][/]',
                                    'constraints' => [
                                        'publication' => '[0-9]+',
                                        'action' => '(index|generate|publish)'
                                    ],
                                    'defaults' => [
                                        'controller' => 'Admin\PublicationController',
                                        'action' => 'index'
                                    ]
                                ]
                            ],
                            'recipient' => [
                                'type' => 'segment',
                                'options' => [
                                    'route' => 'recipient[/:action][/:id][/]',
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
                        'type' => 'segment',
                        'options' => [
                            'route' => 'my-account[/]',
                            'defaults' => [
                                'controller' => 'Admin\MyDetailsController',
                                'action' => 'index',
                            ]
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'details' => [
                                'type' => 'segment',
                                'options' => [
                                    'route' => 'details[/]',
                                    'defaults' => [
                                        'controller' => 'Admin\MyDetailsController',
                                        'action' => 'edit'
                                    ]
                                ]
                            ],
                        ]
                    ],
                    'admin-continuation' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => 'continuation[/]',
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
                            'irfo-psv-auth' => [
                                'type' => 'Segment',
                                'options' => [
                                    'route' => 'irfo-psv-auth/:year/:month[/:action/:id][/]',
                                    'constraints' => [
                                        'year' => '([0-9]{4})',
                                        'month' => '([0-9]{1,2})',
                                        'action' => '(renew|print)',
                                        'id' => '([0-9]+,?)+',
                                    ],
                                    'defaults' => [
                                        'controller' => 'Admin\IrfoPsvAuthContinuationController',
                                        'action' => 'index',
                                    ],
                                ],
                            ],
                        ]
                    ],
                    'admin-report' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => 'report[/]',
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
                                    'route' => 'ch-alerts[/:action][/:id][/]',
                                    'constraints' => [
                                        'id' => '[0-9\,]+'
                                    ],
                                    'defaults' => [
                                        'controller' => 'Admin\CompaniesHouseAlertController',
                                        'action' => 'index',
                                    ]
                                ],
                            ],
                            'cpms' => [
                                'type' => 'Segment',
                                'options' => [
                                    'route' => 'cpms[/:action][/:id][/]',
                                    'constraints' => [
                                        'id' => '[0-9\,]+'
                                    ],
                                    'defaults' => [
                                        'controller' => 'Admin\CpmsReportController',
                                        'action' => 'index',
                                    ]
                                ],
                            ],
                            'cpid-class' => [
                                'type' => 'segment',
                                'options' => [
                                    'route' => 'cpid-classification[/:status][/]',
                                    'defaults' => [
                                        'controller' => 'Admin\ReportController',
                                        'action' => 'cpidClassification',
                                        'status' => null
                                    ]
                                ]
                            ],
                            'exported-reports' => [
                                'type' => 'segment',
                                'options' => [
                                    'route' => 'exported-reports[/]',
                                    'defaults' => [
                                        'controller' => 'Admin\ReportController',
                                        'action' => 'exportedReports'
                                    ]
                                ]
                            ],
                            'pi' => [
                                'type' => 'segment',
                                'options' => [
                                    'route' => 'pi[/]',
                                    'defaults' => [
                                        'controller' => 'Admin\PiReportController',
                                        'action' => 'index'
                                    ]
                                ]
                            ],
                        ],
                    ],
                    'admin-user-management' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => 'user-management/users[/:action][/:user][/]',
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
                            'route' => 'user-management/teams[/:action][/:team][/rule/:rule][/]',
                            'action' => '(index|add|edit|delete|addRule|editRule|deleteRule)',
                            'constraints' => [
                                'team' => '[0-9]+',
                                'rule' => '[0-9]+',
                            ],
                            'defaults' => [
                                'controller' => Admin\Controller\TeamController::class,
                                'action' => 'index'
                            ],
                        ]
                    ],
                    'admin-partner-management' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => 'partner[/:action][/:id][/]',
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
                            'route' => 'financial-standing[/:action][/:id][/]',
                            'constraints' => [
                                'id' => '[0-9\,]+'
                            ],
                            'defaults' => [
                                'controller' => 'Admin\FinancialStandingRateController',
                                'action' => 'index',
                            ]
                        ],
                    ],
                    'admin-public-holiday' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => 'public-holiday[/:action][/:holidayId][/]',
                            'constraints' => [
                                'action' => '(index|add|edit|delete)',
                            ],
                            'defaults' => [
                                'controller' => Admin\Controller\PublicHolidayController::class,
                                'action' => 'index',
                            ]
                        ],
                    ],
                    'admin-disc-printing' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => 'disc-printing[/success[/:success]][/:action][/]',
                            'defaults' => [
                                'controller' => 'Admin\DiscPrintingController',
                                'action' => 'index',
                            ]
                        ],
                    ],
                    'admin-system-parameters' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => 'system-parameters[/:action][/:sp][/]',
                            'constraints' => [
                                'action' => '(index|add|edit|delete)'
                            ],
                            'defaults' => [
                                'controller' => 'Admin\SystemParametersController',
                                'action' => 'index'
                            ]
                        ]
                    ],
                    'admin-system-info-message' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => 'system-info-message[/:action][/:msgId][/]',
                            'constraints' => [
                                'action' => '(index|add|edit|delete)',
                            ],
                            'defaults' => [
                                'controller' => Admin\Controller\SystemInfoMessageController::class,
                                'action' => 'index',
                            ],
                        ],
                    ],
                    'admin-payment-processing' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => 'payment-processing[/]',
                            'defaults' => [
                                'controller' => 'Admin\PaymentProcessingFeesController',
                                'action' => 'redirect',
                            ]
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'misc-fees' => [
                                'type' => 'segment',
                                'options' => [
                                    'route' => 'fees[/]',
                                    'defaults' => [
                                        'controller' => 'Admin\PaymentProcessingFeesController',
                                        'action' => 'index'
                                    ]
                                ],
                                'may_terminate' => true,
                                'child_routes' => [
                                    'fee_action' => [
                                        'type' => 'segment',
                                        'options' => [
                                            'route' => ':action[/:fee][/]',
                                            'constraints' => [
                                                'fee' => '([0-9]+,?)+',
                                            ],
                                        ],
                                        'may_terminate' => true,
                                        'child_routes' => [
                                            'transaction' => [
                                                'type' => 'segment',
                                                'options' => [
                                                    'route' => 'transaction/:transaction[/]',
                                                    'constraints' => [
                                                        'transaction' => '([0-9]+,?)+',
                                                    ],
                                                    'defaults' => [
                                                        'action' => 'transaction',
                                                    ]
                                                ],
                                                'may_terminate' => true,
                                                'child_routes' => [
                                                    'reverse' => [
                                                        'type' => 'segment',
                                                        'options' => [
                                                            'route' => 'reverse[/]',
                                                            'defaults' => [
                                                                'action' => 'reverseTransaction',
                                                            ]
                                                        ],
                                                        'may_terminate' => true,
                                                    ],
                                                    'adjust' => [
                                                        'type' => 'segment',
                                                        'options' => [
                                                            'route' => 'adjust[/]',
                                                            'defaults' => [
                                                                'action' => 'adjustTransaction',
                                                            ]
                                                        ],
                                                        'may_terminate' => true,
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                    'print-receipt' => [
                                        'type' => 'segment',
                                        'options' => [
                                            'route' => 'print-receipt/:reference[/]',
                                            'constraints' => [
                                                'reference' => 'OLCS-[0-9A-F\-]+',
                                            ],
                                            'defaults' => [
                                                'controller' => 'Admin\PaymentProcessingFeesController',
                                                'action' => 'print',
                                            ],
                                        ],
                                    ],
                                    'fee_type_ajax' => [
                                        'type' => 'segment',
                                        'options' => [
                                            'route' => 'ajax/',
                                        ],
                                        'may_terminate' => false,
                                        'child_routes' => [
                                            'single' => [
                                                'type' => 'segment',
                                                'options' => [
                                                    'route' => 'fee-type/:id',
                                                    'constraints' => [
                                                        'id' => '([0-9]+,?)+',
                                                    ],
                                                    'defaults' => [
                                                        'action' => 'feeType',
                                                    ]
                                                ],
                                                'may_terminate' => true,
                                            ],
                                            'list' => [
                                                'type' => 'segment',
                                                'options' => [
                                                    'route' => 'fee-type-list/:date',
                                                    // 'constraints' => [
                                                    //     'date' => '([0-9]{4}\-[0-9]{2}\-[0-9]{2})',
                                                    // ],
                                                    'defaults' => [
                                                        'action' => 'feeTypeList',
                                                    ]
                                                ],
                                                'may_terminate' => true,
                                            ]
                                        ],
                                    ]
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
    'controllers' => [
        'invokables' => [
            Admin\Controller\IndexController::class => Admin\Controller\IndexController::class,
            'Admin\PrintingController' => 'Admin\Controller\PrintingController',
            'Admin\IrfoStockControlController' => 'Admin\Controller\IrfoStockControlController',
            'Admin\IrfoPsvAuthContinuationController' => 'Admin\Controller\IrfoPsvAuthContinuationController',
            'Admin\ScanningController' => 'Admin\Controller\ScanningController',
            // * @todo remove after task allocation rules will be tested (OLCS-6844 & OLCS-12638)
            'Admin\CreateTaskTempController' => 'Admin\Controller\CreateTaskTempController',
            'Admin\PublicationController' => 'Admin\Controller\PublicationController',
            'Admin\RecipientController' => 'Admin\Controller\RecipientController',
            'Admin\ContinuationController' => 'Admin\Controller\ContinuationController',
            'Admin\ReportController' => 'Admin\Controller\ReportController',
            'Admin\UserManagementController' => 'Admin\Controller\UserManagementController',
            Admin\Controller\PublicHolidayController::class => Admin\Controller\PublicHolidayController::class,
            'Admin\DiscPrintingController' => 'Admin\Controller\DiscPrintingController',
            'Admin\MyDetailsController' => 'Admin\Controller\MyDetailsController',
            'Admin\PaymentProcessingFeesController' => 'Admin\Controller\PaymentProcessingFeesController',
            'Admin\PartnerController' => 'Admin\Controller\PartnerController',
            'Admin\ContinuationChecklistReminderController' =>
                'Admin\Controller\ContinuationChecklistReminderController',
            'Admin\CompaniesHouseAlertController' => 'Admin\Controller\CompaniesHouseAlertController',
            'Admin\FinancialStandingRateController' => 'Admin\Controller\FinancialStandingRateController',
            'Admin\CpmsReportController' => 'Admin\Controller\CpmsReportController',
            Admin\Controller\TeamController::class => \Admin\Controller\TeamController::class,
            'Admin\SystemParametersController' => \Admin\Controller\SystemParametersController::class,
            'Admin\TaskAllocationRulesController' => \Admin\Controller\TaskAllocationRulesController::class,
            'Admin\PiReportController' => 'Admin\Controller\PiReportController',
            Admin\Controller\SystemInfoMessageController::class => Admin\Controller\SystemInfoMessageController::class,
        ],
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
    'my_account_route' => 'admin-dashboard/admin-my-account',
];
