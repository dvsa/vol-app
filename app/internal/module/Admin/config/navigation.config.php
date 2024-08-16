<?php

return [
    'label' => 'Home',
    'route' => 'dashboard',
    'pages' => [
        [
            'id' => 'admin-dashboard',
            'class' => 'govuk-link--no-visited-state',
            'label' => 'Admin',
            'route' => 'admin-dashboard',
            'pages' => [
                [
                    'label' => 'Scanning',
                    'class' => 'govuk-link--no-visited-state',
                    'route' => 'admin-dashboard/admin-scanning',
                ],
                [
                    'id'    => 'admin-dashboard/admin-user-management',
                    'class' => 'govuk-link--no-visited-state',
                    'label' => 'User management',
                    'route' => 'admin-dashboard/admin-team-management',
                    'pages' => [
                        [
                            'id' => 'admin-dashboard/admin-team-management',
                            'class' => 'govuk-link--no-visited-state',
                            'label' => 'Teams',
                            'route' => 'admin-dashboard/admin-team-management',
                        ],
                        [
                            'id' => 'admin-dashboard/admin-partner-management',
                            'class' => 'govuk-link--no-visited-state',
                            'label' => 'Partner organisations',
                            'route' => 'admin-dashboard/admin-partner-management',
                        ]
                    ]
                ],
                [
                    'id'    => 'admin-dashboard/admin-printing',
                    'class' => 'govuk-link--no-visited-state',
                    'label' => 'Printing',
                    'route' => 'admin-dashboard/admin-printing',
                    'pages' => [
                        [
                            'id' => 'admin-dashboard/admin-printing/admin-printer-management',
                            'class' => 'govuk-link--no-visited-state',
                            'label' => 'Printers',
                            'route' => 'admin-dashboard/admin-printing/admin-printer-management',
                        ],
                        [
                            'label' => 'Disc Printing',
                            'class' => 'govuk-link--no-visited-state',
                            'route' => 'admin-dashboard/admin-disc-printing',
                        ],
                        [
                            'id' => 'admin-dashboard/admin-printing/irfo-stock-control',
                            'class' => 'govuk-link--no-visited-state',
                            'label' => 'IRFO stock control',
                            'route' => 'admin-dashboard/admin-printing/irfo-stock-control',
                        ],
                        [
                            'id' => 'admin-dashboard/admin-printing/irhp-permits',
                            'class' => 'govuk-link--no-visited-state',
                            'label' => 'Print IRHP Permits',
                            'route' => 'admin-dashboard/admin-printing/irhp-permits',
                        ],
                    ]
                ],
                [
                    'id' => 'admin-dashboard/admin-financial-standing',
                    'class' => 'govuk-link--no-visited-state',
                    'label' => 'Financial standing rates',
                    'route' => 'admin-dashboard/admin-financial-standing',
                ],
                [
                    'id' => 'admin-dashboard/admin-public-holiday',
                    'class' => 'govuk-link--no-visited-state',
                    'label' => 'Public holidays',
                    'route' => 'admin-dashboard/admin-public-holiday',
                ],
                [
                    'id'    => 'admin-dashboard/admin-publication',
                    'class' => 'govuk-link--no-visited-state',
                    'label' => 'Publications',
                    'route' => 'admin-dashboard/admin-publication',
                    'pages' => [
                        [
                            'id' => 'admin-dashboard/admin-publication/pending',
                            'class' => 'govuk-link--no-visited-state',
                            'label' => 'Pending',
                            'route' => 'admin-dashboard/admin-publication/pending'
                        ],
                        [
                            'id' => 'admin-dashboard/admin-publication/published',
                            'class' => 'govuk-link--no-visited-state',
                            'label' => 'Published',
                            'route' => 'admin-dashboard/admin-publication/published'
                        ],
                        [
                            'id' => 'admin-dashboard/admin-publication/recipient',
                            'class' => 'govuk-link--no-visited-state',
                            'label' => 'Recipients',
                            'route' => 'admin-dashboard/admin-publication/recipient',
                        ]
                    ]
                ],
                [
                    'id' => 'admin-dashboard/continuations',
                    'class' => 'govuk-link--no-visited-state',
                    'label' => 'admin-continuations-title',
                    'route' => 'admin-dashboard/admin-continuation',
                    'pages' => [
                        [
                            'label' => 'admin-generate-continuations-title',
                            'class' => 'govuk-link--no-visited-state',
                            'route' => 'admin-dashboard/admin-continuation',
                            'pages' => [
                                [
                                    'label' => 'admin-generate-continuation-details-title',
                                    'class' => 'govuk-link--no-visited-state',
                                    'route' => 'admin-dashboard/admin-continuation/detail',
                                ],
                            ],
                        ],
                        [
                            'label' => 'admin-continuations-checklist-reminders-title',
                            'class' => 'govuk-link--no-visited-state',
                            'route' => 'admin-dashboard/admin-continuation/checklist-reminder',
                        ],
                    ],
                ],
                // @NOTE Duplicate of the above but with a slightly different structure, to allow the user to click
                // back to the generate page
                [
                    'id' => 'admin-dashboard/continuations-details',
                    'class' => 'govuk-link--no-visited-state',
                    'label' => 'admin-continuations-title',
                    'route' => 'admin-dashboard/admin-continuation',
                    'pages' => [
                        [
                            'label' => 'admin-generate-continuations-title',
                            'class' => 'govuk-link--no-visited-state',
                            'route' => 'admin-dashboard/admin-continuation'
                        ],
                        [
                            'label' => 'admin-generate-continuation-details-title',
                            'class' => 'govuk-link--no-visited-state',
                            'route' => 'admin-dashboard/admin-continuation/detail'
                        ],
                        [
                            'label' => 'admin-continuations-checklist-reminders-title',
                            'class' => 'govuk-link--no-visited-state',
                            'route' => 'admin-dashboard/admin-continuation/checklist-reminder',
                        ]
                    ]
                ],
                [
                    'id' => 'admin-dashboard/continuations-irfo',
                    'class' => 'govuk-link--no-visited-state',
                    'label' => 'admin-continuations-title',
                    'route' => 'admin-dashboard/admin-continuation',
                    'pages' => [
                        [
                            'label' => 'admin-generate-continuations-title',
                            'class' => 'govuk-link--no-visited-state',
                            'route' => 'admin-dashboard/admin-continuation',
                        ],
                        [
                            'id' => 'admin-dashboard/admin-continuation/irfo-psv-auth',
                            'class' => 'govuk-link--no-visited-state',
                            'label' => 'admin-generate-continuation-details-title',
                            'route' => 'admin-dashboard/admin-continuation/irfo-psv-auth',
                        ],
                        [
                            'label' => 'admin-continuations-checklist-reminders-title',
                            'class' => 'govuk-link--no-visited-state',
                            'route' => 'admin-dashboard/admin-continuation/checklist-reminder',
                        ]
                    ]
                ],
                [
                    'id'    => 'admin-dashboard/admin-payment-processing',
                    'class' => 'govuk-link--no-visited-state',
                    'label' => 'Payment processing',
                    'route' => 'admin-dashboard/admin-payment-processing',
                    'pages' => [
                        [
                            'id' => 'admin-dashboard/admin-payment-processing/misc-fees',
                            'class' => 'govuk-link--no-visited-state',
                            'label' => 'Miscellaneous fees',
                            'route' => 'admin-dashboard/admin-payment-processing/misc-fees',
                            'pages' => [
                                [
                                    'id' => 'admin-dashboard/admin-payment-processing/misc-fees/details',
                                    'class' => 'govuk-link--no-visited-state',
                                    'label' => 'Fee details',
                                    'route' => 'admin-dashboard/admin-payment-processing/misc-fees/fee_action',
                                ],
                                [
                                    // note, we can't nest the transaction breadcrumb under fee details
                                    // due to conflicting 'action' params :(
                                    'id' => 'admin-dashboard/admin-payment-processing/misc-fees/transaction',
                                    'class' => 'govuk-link--no-visited-state',
                                    'label' => 'Transaction details',
                                    'route' =>
                                        'admin-dashboard/admin-payment-processing/misc-fees/fee_action/transaction',
                                ],
                            ],
                        ],
                    ]
                ],
                [
                    'id'    => 'admin-dashboard/admin-your-account',
                    'class' => 'govuk-link--no-visited-state',
                    'label' => 'Your account',
                    'route' => 'admin-dashboard/admin-your-account',
                    'pages' => [
                        [
                            'id' => 'admin-dashboard/admin-your-account/details',
                            'class' => 'govuk-link--no-visited-state',
                            'label' => 'Details',
                            'route' => 'admin-dashboard/admin-your-account/details'
                        ],
                        [
                            'id' => 'admin-dashboard/admin-your-account/change-password',
                            'label' => 'Change password',
                            'route' => 'change-password',
                            'class' => 'govuk-link--no-visited-state js-modal-ajax',
                        ],
                    ]
                ],
                [
                    'label' => 'Reports',
                    'id' => 'admin-dashboard/admin-report',
                    'class' => 'govuk-link--no-visited-state',
                    'route' => 'admin-dashboard/admin-report',
                    'pages' => [
                        [
                            'id' => 'admin-dashboard/admin-report/ch-alerts',
                            'class' => 'govuk-link--no-visited-state',
                            'label' => 'Companies House alerts',
                            'route' => 'admin-dashboard/admin-report/ch-alerts'
                        ],
                        [
                            'id' => 'admin-dashboard/admin-report/cpms',
                            'class' => 'govuk-link--no-visited-state',
                            'label' => 'CPMS Financial report',
                            'route' => 'admin-dashboard/admin-report/cpms'
                        ],
                        [
                            'id' => 'admin-dashboard/admin-report/interim-refunds',
                            'class' => 'govuk-link--no-visited-state',
                            'label' => 'Interim Refunds',
                            'route' => 'admin-dashboard/admin-report/interim-refunds',
                        ],
                        [
                            'id' => 'admin-dashboard/admin-report/exported-reports',
                            'class' => 'govuk-link--no-visited-state',
                            'label' => 'Exported reports',
                            'route' => 'admin-dashboard/admin-report/exported-reports',
                        ],
                        [
                            'id' => 'admin-dashboard/admin-report/pi',
                            'class' => 'govuk-link--no-visited-state',
                            'label' => 'Public Inquiry listings',
                            'route' => 'admin-dashboard/admin-report/pi',
                        ],
                        [
                            'id' => 'admin-dashboard/admin-report/cases/open',
                            'class' => 'govuk-link--no-visited-state',
                            'label' => 'Open cases',
                            'route' => 'admin-dashboard/admin-report/cases/open',
                        ],
                        [
                            'id' => 'admin-dashboard/admin-report/permits',
                            'class' => 'govuk-link--no-visited-state',
                            'label' => 'Permits',
                            'route' => 'admin-dashboard/admin-report/permits',
                        ],
                        [
                            'id' => 'admin-dashboard/admin-report/upload',
                            'class' => 'govuk-link--no-visited-state',
                            'label' => 'Upload reports',
                            'route' => 'admin-dashboard/admin-report/upload',
                        ],
                    ],
                ],
                [
                    'id' => 'admin-dashboard/admin-manage-system-parameters',
                    'class' => 'govuk-link--no-visited-state',
                    'label' => 'System parameters',
                    'route' => 'admin-dashboard/admin-system-parameters',
                ],
                [
                    'id' => 'admin-dashboard/admin-feature-toggle',
                    'class' => 'govuk-link--no-visited-state',
                    'label' => 'Feature toggle',
                    'route' => 'admin-dashboard/admin-feature-toggle',
                ],
                [
                    'id' => 'admin-dashboard/admin-permits',
                    'class' => 'govuk-link--no-visited-state',
                    'label' => 'Permits',
                    'route' => 'admin-dashboard/admin-permits',
                    'pages' => [
                        [
                            'id' => 'admin-dashboard/admin-permits/stocks',
                            'class' => 'govuk-link--no-visited-state',
                            'label' => 'Permit system settings',
                            'route' => 'admin-dashboard/admin-permits/stocks'
                        ],
                        [
                            'id' => 'admin-dashboard/admin-permits/ranges',
                            'class' => 'govuk-link--no-visited-state',
                            'label' => 'Stock',
                            'route' => 'admin-dashboard/admin-permits/ranges'
                        ],
                        [
                            'id' => 'admin-dashboard/admin-permits/windows',
                            'class' => 'govuk-link--no-visited-state',
                            'label' => 'Window',
                            'route' => 'admin-dashboard/admin-permits/windows'
                        ],
                        [
                            'id' => 'admin-dashboard/admin-permits/jurisdiction',
                            'class' => 'govuk-link--no-visited-state',
                            'label' => 'Devolved administration',
                            'route' => 'admin-dashboard/admin-permits/jurisdiction'
                        ],
                        [
                            'id' => 'admin-dashboard/admin-permits/sectors',
                            'class' => 'govuk-link--no-visited-state',
                            'label' => 'Sectors',
                            'route' => 'admin-dashboard/admin-permits/sectors'
                        ],
                        [
                            'id' => 'admin-dashboard/admin-permits/scoring',
                            'class' => 'govuk-link--no-visited-state',
                            'label' => 'Scoring',
                            'route' => 'admin-dashboard/admin-permits/scoring'
                        ],
                        [
                            'id' => 'admin-dashboard/admin-permits/exported-reports',
                            'class' => 'govuk-link--no-visited-state',
                            'label' => 'Exported reports',
                            'route' => 'admin-dashboard/admin-permits/exported-reports',
                        ],
                    ],
                ],
                [
                    'id' => 'admin-dashboard/admin-system-info-message',
                    'class' => 'govuk-link--no-visited-state',
                    'label' => 'System messages',
                    'route' => 'admin-dashboard/admin-system-info-message',
                ],
                [
                    'label' => 'Task allocation rules',
                    'class' => 'govuk-link--no-visited-state',
                    'id' => 'admin-dashboard/task-allocation-rules',
                    'route' => 'admin-dashboard/task-allocation-rules',
                ],
                [
                    'label' => 'Content Management',
                    'id' => 'admin-dashboard/content-management',
                    'class' => 'govuk-link--no-visited-state',
                    'route' => 'admin-dashboard/admin-email-templates',
                    'pages' => [
                        [
                            'id' => 'admin-dashboard/content-management/email-templates',
                            'class' => 'govuk-link--no-visited-state',
                            'label' => 'Email Templates',
                            'route' => 'admin-dashboard/admin-email-templates',
                        ],
                        [
                            'id' => 'admin-dashboard/content-management/document-templates',
                            'class' => 'govuk-link--no-visited-state',
                            'label' => 'Document Templates',
                            'route' => 'admin-dashboard/admin-document-templates',
                        ],
                        [
                            'id' => 'admin-dashboard/content-management/editable-translations',
                            'class' => 'govuk-link--no-visited-state',
                            'label' => 'Editable Translations',
                            'route' => 'admin-dashboard/admin-editable-translations',
                        ],
                        [
                            'id' => 'admin-dashboard/content-management/replacements',
                            'class' => 'govuk-link--no-visited-state',
                            'label' => 'Replacements',
                            'route' => 'admin-dashboard/admin-replacements',
                        ]
                    ],
                ],
                [
                    'label' => 'Data retention',
                    'id' => 'admin-dashboard/admin-data-retention',
                    'class' => 'govuk-link--no-visited-state',
                    'route' => 'admin-dashboard/admin-data-retention',
                    'pages' => [
                        [
                            'id' => 'admin-dashboard/admin-data-retention/review',
                            'class' => 'govuk-link--no-visited-state',
                            'label' => 'Review',
                            'route' => 'admin-dashboard/admin-data-retention/review',
                            'pages' => [
                                [
                                    'id' => 'admin-dashboard/admin-data-retention/review/records',
                                    'class' => 'govuk-link--no-visited-state',
                                    'label' => 'Records',
                                    'route' => 'admin-dashboard/admin-data-retention/review/records'
                                ],
                            ]
                        ],
                        [
                            'id' => 'admin-dashboard/admin-data-retention/export',
                            'class' => 'govuk-link--no-visited-state',
                            'label' => 'Export',
                            'route' => 'admin-dashboard/admin-data-retention/export'
                        ],
                        [
                            'id' => 'admin-dashboard/admin-data-retention/rule-admin',
                            'class' => 'govuk-link--no-visited-state',
                            'label' => 'Rule admin',
                            'route' => 'admin-dashboard/admin-data-retention/rule-admin'
                        ],
                    ],
                ],
                [
                    'id' => 'admin-dashboard/admin-fee-rates',
                    'class' => 'govuk-link--no-visited-state',
                    'label' => 'Fee rates',
                    'route' => 'admin-dashboard/admin-fee-rates',
                    'pages' => [
                        [
                            'id' => 'admin-dashboard/admin-fee-rates',
                            'class' => 'govuk-link--no-visited-state',
                            'label' => 'Fee Rates',
                            'route' => 'admin-dashboard/admin-fee-rates',
                        ],
                    ],
                ],
                [
                    'id' => 'admin-dashboard/admin-bus-registration/notice-period',
                    'class' => 'govuk-link--no-visited-state',
                    'label' => 'Bus registrations',
                    'route' => 'admin-dashboard/admin-bus-registration/notice-period',
                    'pages' => [
                        [
                            'id' => 'admin-dashboard/admin-bus-registration/notice-period',
                            'class' => 'govuk-link--no-visited-state',
                            'label' => 'Notice periods',
                            'route' => 'admin-dashboard/admin-bus-registration/notice-period',
                        ],
                        [
                            'id' => 'admin-dashboard/admin-bus-registration/local-authority',
                            'class' => 'govuk-link--no-visited-state',
                            'label' => 'Local Authorities (LTA)',
                            'route' => 'admin-dashboard/admin-bus-registration/local-authority',
                        ],
                    ],
                ],
                [
                    'label' => 'Presiding TCs',
                    'id' => 'admin-dashboard/presiding-tcs',
                    'class' => 'govuk-link--no-visited-state',
                    'route' => 'admin-dashboard/admin-presiding-tcs',
                ],
            ]
        ]
    ]
];
