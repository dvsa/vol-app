<?php

return [
    'label' => 'Home',
    'route' => 'dashboard',
    'pages' => [
        [
            'id' => 'admin-dashboard',
            'label' => 'Admin',
            'route' => 'admin-dashboard',
            'pages' => [
                [
                    'id'    => 'admin-dashboard/admin-printing',
                    'label' => 'Printing',
                    'route' => 'admin-dashboard/admin-printing',
                    'pages' => [
                        [
                            'id' => 'admin-dashboard/admin-printing/irfo-stock-control',
                            'label' => 'IRFO stock control',
                            'route' => 'admin-dashboard/admin-printing/irfo-stock-control',
                        ]
                    ]
                ],
                [
                    'label' => 'Disc Printing',
                    'route' => 'admin-dashboard/admin-disc-printing',
                ],
                [
                    'label' => 'Scanning',
                    'route' => 'admin-dashboard/admin-scanning',
                ],
                [
                    'id'    => 'admin-dashboard/admin-publication',
                    'label' => 'Publications',
                    'route' => 'admin-dashboard/admin-publication',
                    'pages' => [
                        [
                            'id' => 'admin-dashboard/admin-publication/pending',
                            'label' => 'Pending',
                            'route' => 'admin-dashboard/admin-publication/pending'
                        ],
                        [
                            'id' => 'admin-dashboard/admin-publication/recipient',
                            'label' => 'Recipients',
                            'route' => 'admin-dashboard/admin-publication/recipient',
                        ]
                    ]
                ],
                [
                    'id'    => 'admin-dashboard/admin-my-account',
                    'label' => 'My account',
                    'route' => 'admin-dashboard/admin-my-account',
                    'pages' => [
                        [
                            'id' => 'admin-dashboard/admin-my-account/details',
                            'label' => 'Details',
                            'route' => 'admin-dashboard/admin-my-account/details'
                        ],
                        [
                            'id' => 'admin-dashboard/admin-my-account/change-password',
                            'label' => 'Change password',
                            'route' => 'admin-dashboard/admin-my-account/change-password',
                        ],
                    ]
                ],
                [
                    'id' => 'admin-dashboard/continuations',
                    'label' => 'admin-continuations-title',
                    'route' => 'admin-dashboard/admin-continuation',
                    'pages' => [
                        [
                            'label' => 'admin-generate-continuations-title',
                            'route' => 'admin-dashboard/admin-continuation',
                            'pages' => [
                                [
                                    'label' => 'admin-generate-continuation-details-title',
                                    'route' => 'admin-dashboard/admin-continuation/detail'
                                ]
                            ]
                        ],
                        [
                            'label' => 'admin-continuations-checklist-reminders-title',
                            'route' => 'admin-dashboard/admin-continuation/checklist-reminder',
                        ]
                    ]
                ],
                [
                    'label' => 'Reports',
                    'id' => 'admin-dashboard/admin-report',
                    'route' => 'admin-dashboard/admin-report',
                    'pages' => [
                        [
                            'id' => 'admin-dashboard/admin-report/ch-alerts',
                            'label' => 'Companies House alerts',
                            'route' => 'admin-dashboard/admin-report/ch-alerts'
                        ],
                        [
                            'id' => 'admin-dashboard/admin-report/cpms',
                            'label' => 'CPMS Financial report',
                            'route' => 'admin-dashboard/admin-report/cpms'
                        ],
                        [
                            'id' => 'admin-dashboard/admin-report/cpid-class',
                            'label' => 'CPID classification',
                            'route' => 'admin-dashboard/admin-report/cpid-class',
                        ],
                        [
                            'id' => 'admin-dashboard/admin-report/exported-reports',
                            'label' => 'Exported reports',
                            'route' => 'admin-dashboard/admin-report/exported-reports',
                        ],
                    ],
                ],
                [
                    'id'    => 'admin-dashboard/admin-user-management',
                    'label' => 'User management',
                    'route' => 'admin-dashboard/admin-user-management',
                    'pages' => [
                        [
                            'id' => 'admin-dashboard/admin-team-management',
                            'label' => 'Teams',
                            'route' => 'admin-dashboard/admin-team-management',
                        ],
                        [
                            'id' => 'admin-dashboard/admin-printer-management',
                            'label' => 'Printers',
                            'route' => 'admin-dashboard/admin-printer-management',
                        ],
                        [
                            'id' => 'admin-dashboard/admin-partner-management',
                            'label' => 'Partners',
                            'route' => 'admin-dashboard/admin-partner-management',
                        ]
                    ]
                ],
                [
                    'id' => 'admin-dashboard/admin-financial-standing',
                    'label' => 'Financial standing rates',
                    'route' => 'admin-dashboard/admin-financial-standing',
                ],
                [
                    'label' => 'Public holidays',
                    'route' => 'admin-dashboard/admin-public-holiday',
                ],
                [
                    'label' => 'System messages',
                    'route' => 'admin-dashboard/admin-system-message',
                ],
                [
                    'id'    => 'admin-dashboard/admin-payment-processing',
                    'label' => 'Payment processing',
                    'route' => 'admin-dashboard/admin-payment-processing',
                    'pages' => [
                        [
                            'id' => 'admin-dashboard/admin-payment-processing/misc-fees',
                            'label' => 'Miscellaneous fees',
                            'route' => 'admin-dashboard/admin-payment-processing/misc-fees',
                            'pages' => [
                                [
                                    'id' => 'admin-dashboard/admin-payment-processing/misc-fees/details',
                                    'label' => 'Fee details',
                                    'route' => 'admin-dashboard/admin-payment-processing/misc-fees/fee_action',
                                ],
                                [
                                    // note, we can't nest the transaction breadcrumb under fee details
                                    // due to conflicting 'action' params :(
                                    'id' => 'admin-dashboard/admin-payment-processing/misc-fees/transaction',
                                    'label' => 'Transaction details',
                                    'route' =>
                                        'admin-dashboard/admin-payment-processing/misc-fees/fee_action/transaction',
                                ],
                            ],
                        ],
                    ]
                ],
            ]
        ]
    ]
];
