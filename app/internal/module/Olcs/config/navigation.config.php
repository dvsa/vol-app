<?php

use Common\Service\Data\SectionConfig;

$sectionConfig = new SectionConfig();
$sections = $sectionConfig->getAllReferences();
$applicationDetailsPages = [];
$licenceDetailsPages = [];
$variationDetailsPages = [];

foreach ($sections as $section) {
    $applicationDetailsPages[] = [
        'id' => 'application_' . $section,
        'label' => 'section.name.' . $section,
        'class' => 'govuk-link--no-visited-state',
        'route' => 'lva-application/' . $section,
        'use_route_match' => true
    ];

    $licenceDetailsPages[] = [
        'id' => 'licence_' . $section,
        'label' => 'section.name.' . $section,
        'class' => 'govuk-link--no-visited-state',
        'route' => 'lva-licence/' . $section,
        'use_route_match' => true
    ];

    $variationDetailsPages[] = [
        'id' => 'variation_' . $section,
        'label' => 'section.name.' . $section,
        'class' => 'govuk-link--no-visited-state',
        'route' => 'lva-variation/' . $section,
        'use_route_match' => true
    ];
}

/*
 * This is here purely to ensure that the breadcrumb for grace periods
 * appears when on the grace period page as per the AC.
 */
$licenceDetailsPages[] = [
    'id' => 'licence_grace_periods',
    'label' => 'internal-licence-grace-periods-breadcrumb',
    'class' => 'govuk-link--no-visited-state',
    'route' => 'licence/grace-periods',
    'use_route_match' => true,
];

$nav = [
    'label' => 'Home',
    'route' => 'dashboard',
    'use_route_match' => false,
    'pages' => [
        [
            'id' => 'case',
            'label' => 'Case',
            'class' => 'govuk-link--no-visited-state',
            'route' => 'case',
            'action' => 'redirect',
            'use_route_match' => true,
            'pages' => [
                [
                    'id' => 'case_details',
                    'label' => 'Case details',
                    'class' => 'govuk-link--no-visited-state',
                    'route' => 'case',
                    'action' => 'redirect',
                    'use_route_match' => true,
                    'pages' => [
                        [
                            'id' => 'case_details_overview',
                            'label' => 'Overview',
                            'class' => 'govuk-link--no-visited-state',
                            'route' => 'case',
                            'action' => 'details',
                            'use_route_match' => true,
                        ],
                        [
                            'id' => 'case_details_convictions',
                            'label' => 'Convictions',
                            'class' => 'govuk-link--no-visited-state',
                            'route' => 'conviction',
                            'action' => 'index',
                            'use_route_match' => true,
                        ],
                        [
                            'id' => 'case_details_legacy_offence',
                            'label' => 'Legacy offences',
                            'class' => 'govuk-link--no-visited-state',
                            'route' => 'offence',
                            'action' => 'index',
                            'use_route_match' => true,
                            'pages' => [
                                [
                                    'id' => 'case_details_legacy_offence_details',
                                    'label' => 'Legacy offence details',
                                    'class' => 'govuk-link--no-visited-state',
                                    'route' => 'offence',
                                    'action' => 'details',
                                    'use_route_match' => true,
                                ]
                            ]
                        ],
                        [
                            'id' => 'case_details_annual_test_history',
                            'label' => 'Annual test history',
                            'class' => 'govuk-link--no-visited-state',
                            'route' => 'case_annual_test_history',
                            'action' => 'index',
                            'use_route_match' => true,
                        ],
                        [
                            'id' => 'case_details_penalties',
                            'label' => 'Serious infringements',
                            'class' => 'govuk-link--no-visited-state',
                            'route' => 'case_penalty',
                            'action' => 'index',
                            'use_route_match' => true,
                            'pages' => [
                                [
                                    'id' => 'case_penalty_applied',
                                    'label' => 'Serious infringement',
                                    'class' => 'govuk-link--no-visited-state',
                                    'route' => 'case_penalty_applied',
                                    'action' => 'index',
                                    'use_route_match' => true,
                                    'pages' => [
                                        [
                                            'id' => 'case_penalty_applied_add',
                                            'label' => 'Add Penalty',
                                            'class' => 'govuk-link--no-visited-state',
                                            'route' => 'case_penalty_applied',
                                            'action' => 'add',
                                            'use_route_match' => true,
                                        ],
                                        [
                                            'id' => 'case_penalty_applied_edit',
                                            'label' => 'Edit Penalty',
                                            'class' => 'govuk-link--no-visited-state',
                                            'route' => 'case_penalty_applied',
                                            'action' => 'edit',
                                            'use_route_match' => true,
                                        ],
                                    ]
                                ]
                            ]
                        ],
                        [
                            'id' => 'case_details_prohibitions',
                            'label' => 'Prohibitions',
                            'class' => 'govuk-link--no-visited-state',
                            'route' => 'case_prohibition',
                            'action' => 'index',
                            'use_route_match' => true,
                        ],
                        [
                            'id' => 'case_details_statements',
                            'label' => 'Section statements',
                            'class' => 'govuk-link--no-visited-state',
                            'route' => 'case_statement',
                            'action' => 'index',
                            'use_route_match' => true,
                        ],
                        [
                            'id' => 'case_details_complaints',
                            'label' => 'Complaints',
                            'class' => 'govuk-link--no-visited-state',
                            'route' => 'case_complaint',
                            'action' => 'index',
                            'use_route_match' => true,
                        ],
                        [
                            'id' => 'case_details_conditions_undertakings',
                            'label' => 'Conditions & undertakings',
                            'class' => 'govuk-link--no-visited-state',
                            'route' => 'case_conditions_undertakings',
                            'action' => 'index',
                            'use_route_match' => true,
                        ],
                    ]
                ],
                [
                    'id' => 'case_opposition',
                    'label' => 'Opposition',
                    'class' => 'govuk-link--no-visited-state',
                    'route' => 'case_opposition',
                    'action' => 'index',
                    'use_route_match' => true,
                    'pages' => [
                        [
                            'id' => 'case_opposition_add',
                            'label' => 'Add Opposition',
                            'class' => 'govuk-link--no-visited-state',
                            'route' => 'case_opposition',
                            'action' => 'add',
                            'use_route_match' => true,
                        ],
                        [
                            'id' => 'case_opposition_edit',
                            'label' => 'Edit Opposition',
                            'class' => 'govuk-link--no-visited-state',
                            'route' => 'case_opposition',
                            'action' => 'edit',
                            'use_route_match' => true,
                        ],
                    ]
                ],
                [
                    'id' => 'case_submissions',
                    'label' => 'Submissions',
                    'class' => 'govuk-link--no-visited-state',
                    'route' => 'submission',
                    'action' => 'index',
                    'use_route_match' => true,
                    'pages' => [
                        [
                            'id' => 'case_submission_list',
                            'label' => 'Submission List',
                            'class' => 'govuk-link--no-visited-state',
                            'route' => 'submission',
                            'action' => 'index',
                            'use_route_match' => true,
                        ],
                        [
                            'id' => 'case_submission_add',
                            'label' => 'Add Submission',
                            'class' => 'govuk-link--no-visited-state',
                            'route' => 'submission',
                            'action' => 'add',
                            'use_route_match' => true,
                        ],
                        [
                            'id' => 'case_submission_edit',
                            'label' => 'Edit Submission',
                            'class' => 'govuk-link--no-visited-state',
                            'route' => 'submission',
                            'action' => 'edit',
                            'use_route_match' => true,
                        ],
                    ]
                ],
                [
                    'id' => 'case_hearings_appeals',
                    'label' => 'Hearings & appeals',
                    'class' => 'govuk-link--no-visited-state',
                    'route' => 'case_pi',
                    'action' => 'details',
                    'use_route_match' => true,
                    'pages' => [
                        [
                            'id' => 'case_hearings_appeals_public_inquiry',
                            'label' => 'Public Inquiry',
                            'route' => 'case_pi',
                            'class' => 'govuk-link--no-visited-state',
                            'action' => 'index',
                            'use_route_match' => true,
                            'pages' => [
                                [
                                    'id' => 'case_hearings_appeals_public_inquiry_add',
                                    'label' => 'internal-pi-hearing-add',
                                    'route' => 'case_pi_hearing',
                                    'class' => 'govuk-link--no-visited-state',
                                    'action' => 'add'
                                ],
                                [
                                    'id' => 'case_hearings_appeals_public_inquiry_edit',
                                    'label' => 'internal-pi-hearing-edit',
                                    'route' => 'case_pi_hearing',
                                    'class' => 'govuk-link--no-visited-state',
                                    'action' => 'edit'
                                ],
                            ],
                        ],
                        [
                            'id' => 'case_hearings_appeals_stays',
                            'label' => 'Appeal and stays',
                            'route' => 'case_hearing_appeal',
                            'class' => 'govuk-link--no-visited-state',
                            'action' => 'details',
                            'use_route_match' => true,
                        ],
                        [
                            'id' => 'case_hearings_appeals_non_public_inquiry',
                            'label' => 'Non-Public Inquiry',
                            'route' => 'case_non_pi',
                            'class' => 'govuk-link--no-visited-state',
                            'action' => 'details',
                            'use_route_match' => true,
                            'pages' => [
                                [
                                    'id' => 'case_hearings_appeals_non_public_inquiry_add',
                                    'label' => 'internal-non-pi-hearing-add',
                                    'route' => 'case_pi_hearing',
                                    'class' => 'govuk-link--no-visited-state',
                                    'action' => 'add'
                                ],
                                [
                                    'id' => 'case_hearings_appeals_non_public_inquiry_edit',
                                    'label' => 'internal-non-pi-hearing-edit',
                                    'route' => 'case_pi_hearing',
                                    'class' => 'govuk-link--no-visited-state',
                                    'action' => 'edit'
                                ],
                            ],
                        ],
                        [
                            'id' => 'case_details_impounding',
                            'label' => 'Impoundings',
                            'route' => 'case_details_impounding',
                            'class' => 'govuk-link--no-visited-state',
                            'action' => 'index',
                            'use_route_match' => true,
                        ]
                    ]
                ],
                [
                    'id' => 'case_docs_attachments',
                    'label' => 'Docs & attachments',
                    'route' => 'case_licence_docs_attachments',
                    'class' => 'govuk-link--no-visited-state',
                    'action' => 'documents',
                    'use_route_match' => true,
                ],
                [
                    'id' => 'case_processing',
                    'label' => 'Processing',
                    'route' => 'processing',
                    'class' => 'govuk-link--no-visited-state',
                    'action' => 'overview',
                    'use_route_match' => true,
                    'pages' => [
                        [
                            'id' => 'case_processing_in_office_revocation',
                            'label' => 'In-office revocation',
                            'route' => 'processing_in_office_revocation',
                            'class' => 'govuk-link--no-visited-state',
                            'action' => 'index',
                            'use_route_match' => true,
                        ],
                        [
                            'id' => 'case_processing_decisions',
                            'label' => 'Decisions',
                            'route' => 'processing_decisions',
                            'class' => 'govuk-link--no-visited-state',
                            'action' => 'details',
                            'use_route_match' => true,
                        ],
                        [
                            'id' => 'case_processing_history',
                            'label' => 'internal-crud-event-history',
                            'route' => 'processing_history',
                            'class' => 'govuk-link--no-visited-state',
                            'action' => 'index',
                            'use_route_match' => true,
                        ],
                        [
                            'id' => 'case_processing_read_history',
                            'label' => 'internal-crud-read-history',
                            'route' => 'processing_read_history',
                            'class' => 'govuk-link--no-visited-state',
                            'action' => 'redirect',
                            'use_route_match' => true,
                        ],
                        [
                            'id' => 'case_processing_notes',
                            'label' => 'Notes',
                            'route' => 'case_processing_notes',
                            'class' => 'govuk-link--no-visited-state',
                            'action' => 'index',
                            'use_route_match' => true,
                            'pages' => [
                                [
                                    'id' => 'case_processing_notes_add',
                                    'label' => 'case-note-add-label',
                                    'route' => 'case_processing_notes/add-note',
                                    'class' => 'govuk-link--no-visited-state',
                                    'action' => 'add',

                                ],
                                [
                                    'id' => 'case_processing_notes_edit',
                                    'label' => 'case-note-edit-label',
                                    'route' => 'case_processing_notes/modify-note',
                                    'class' => 'govuk-link--no-visited-state',
                                    'action' => 'edit',

                                ]
                            ]
                        ],
                        [
                            'id' => 'case_processing_tasks',
                            'label' => 'Tasks',
                            'route' => 'case_processing_tasks',
                            'class' => 'govuk-link--no-visited-state',
                            'action' => 'index',
                            'use_route_match' => true,
                        ],
                    ]
                ]
            ],
        ],
        [
            'id' => 'case_add',
            'label' => 'Add Case',
            'route' => 'case',
            'class' => 'govuk-link--no-visited-state',
            'action' => 'add',
            'use_route_match' => true
        ],
        [
            'id' => 'case_edit',
            'label' => 'Edit Case',
            'route' => 'case',
            'class' => 'govuk-link--no-visited-state',
            'action' => 'edit',
            'use_route_match' => true
        ],
        [
            'id' => 'mainsearch',
            'label' => 'Search',
            'route' => 'search',
            'class' => 'govuk-link--no-visited-state',
            'use_route_match' => true,
            'pages' => [
                [
                    'id' => 'licence',
                    'label' => 'Licence',
                    'route' => 'lva-licence',
                    'class' => 'govuk-link--no-visited-state',
                    'use_route_match' => true,
                    'pages' => [
                        [
                            'id' => 'licence_details_overview',
                            'label' => 'internal-licence-details-breadcrumb',
                            'route' => 'lva-licence',
                            'class' => 'govuk-link--no-visited-state',
                            'use_route_match' => true,
                            'pages' => $licenceDetailsPages
                        ],
                        [
                            'id' => 'licence_bus',
                            'label' => 'Bus registrations',
                            'route' => 'licence/bus',
                            'class' => 'govuk-link--no-visited-state',
                            'use_route_match' => true,
                            'pages' => [
                                [
                                    'id' => 'licence_bus_details',
                                    'label' => 'internal-licence-bus-details',
                                    'route' => 'licence/bus-details/service',
                                    'class' => 'govuk-link--no-visited-state',
                                    'use_route_match' => true,
                                    'pages' => [
                                        [
                                            'id' => 'licence_bus_details-service',
                                            'label' => 'internal-licence-bus-details-service',
                                            'route' => 'licence/bus-details/service',
                                            'class' => 'govuk-link--no-visited-state',
                                            'use_route_match' => true,
                                        ],
                                        [
                                            'id' => 'licence_bus_details-stop',
                                            'label' => 'internal-licence-bus-details-stop',
                                            'route' => 'licence/bus-details/stop',
                                            'class' => 'govuk-link--no-visited-state',
                                            'use_route_match' => true,
                                        ],
                                        [
                                            'id' => 'licence_bus_details-ta',
                                            'label' => 'internal-licence-bus-details-ta',
                                            'route' => 'licence/bus-details/ta',
                                            'class' => 'govuk-link--no-visited-state',
                                            'use_route_match' => true,
                                        ],
                                        [
                                            'id' => 'licence_bus_details-quality',
                                            'label' => 'internal-licence-bus-details-quality',
                                            'route' => 'licence/bus-details/quality',
                                            'class' => 'govuk-link--no-visited-state',
                                            'use_route_match' => true,
                                        ]
                                    ]
                                ],
                                [
                                    'id' => 'licence_bus_short',
                                    'label' => 'internal-licence-bus-short',
                                    'route' => 'licence/bus-short',
                                    'class' => 'govuk-link--no-visited-state',
                                    'use_route_match' => true
                                ],
                                [
                                    'id' => 'licence_bus_register_service',
                                    'label' => 'internal-licence-register-service',
                                    'route' => 'licence/bus-register-service',
                                    'class' => 'govuk-link--no-visited-state',
                                    'use_route_match' => true
                                ],
                                [
                                    'id' => 'licence_bus_docs',
                                    'label' => 'internal-licence-bus-docs',
                                    'route' => 'licence/bus-docs',
                                    'class' => 'govuk-link--no-visited-state',
                                    'use_route_match' => true,
                                    'pages' => [
                                        [
                                            'id' => 'licence_bus_docs',
                                            'label' => 'internal-licence-bus-docs',
                                            'route' => 'licence/bus-docs',
                                            'class' => 'govuk-link--no-visited-state',
                                            'use_route_match' => true,
                                        ],
                                    ]
                                ],
                                [
                                    'id' => 'licence_bus_processing',
                                    'label' => 'internal-licence-bus-processing',
                                    'route' => 'licence/bus-processing/decisions',
                                    'class' => 'govuk-link--no-visited-state',
                                    'use_route_match' => true,
                                    'params' => [
                                        'action' => 'details',
                                    ],
                                    'pages' => [
                                        [
                                            'id' => 'licence_bus_processing_registration_history',
                                            'label' => 'internal-licence-bus-processing-registration-history',
                                            'route' => 'licence/bus-processing/registration-history',
                                            'class' => 'govuk-link--no-visited-state',
                                            'use_route_match' => true,
                                            'params' => [
                                                'action' => 'index',
                                            ],
                                        ],
                                        [
                                            'id' => 'licence_bus_processing_decisions',
                                            'label' => 'internal-licence-bus-processing-decisions',
                                            'route' => 'licence/bus-processing/decisions',
                                            'class' => 'govuk-link--no-visited-state',
                                            'use_route_match' => true,
                                            'params' => [
                                                'action' => 'details',
                                            ],
                                        ],
                                        [
                                            'id' => 'licence_bus_processing_event-history',
                                            'label' => 'internal-crud-event-history',
                                            'route' => 'licence/bus-processing/event-history',
                                            'class' => 'govuk-link--no-visited-state',
                                            'use_route_match' => true,
                                            'params' => [
                                                'action' => null,
                                            ],
                                        ],
                                        [
                                            'id' => 'licence_bus_processing_event-history',
                                            'label' => 'internal-crud-read-history',
                                            'route' => 'licence/bus-processing/read-history',
                                            'class' => 'govuk-link--no-visited-state',
                                            'use_route_match' => true,
                                        ],
                                        [
                                            'id' => 'licence_bus_processing_notes',
                                            'label' => 'internal-licence-bus-processing-notes',
                                            'route' => 'licence/bus-processing/notes',
                                            'class' => 'govuk-link--no-visited-state',
                                            'use_route_match' => true,
                                            'params' => [
                                                'action' => 'index',
                                            ],
                                            'pages' => [
                                                [
                                                    'id' => 'licence_bus_processing_notes_add',
                                                    'label' => 'internal-licence-bus-processing-notes-add',
                                                    'route' => 'licence/bus-processing/add-note',
                                                    'class' => 'govuk-link--no-visited-state',
                                                    'use_route_match' => true
                                                ],
                                                [
                                                    'id' => 'licence_bus_processing_notes_modify',
                                                    'label' => 'internal-licence-bus-processing-notes-modify',
                                                    'route' => 'licence/bus-processing/modify-note',
                                                    'class' => 'govuk-link--no-visited-state',
                                                    'use_route_match' => true
                                                ]
                                            ]
                                        ],
                                        [
                                            'id' => 'licence_bus_processing_tasks',
                                            'label' => 'internal-licence-bus-processing-tasks',
                                            'route' => 'licence/bus-processing/tasks',
                                            'class' => 'govuk-link--no-visited-state',
                                            'use_route_match' => true,
                                        ],
                                    ]
                                ],
                                [
                                    'id' => 'licence_bus_fees',
                                    'label' => 'internal-licence-bus-fees',
                                    'route' => 'licence/bus-fees',
                                    'class' => 'govuk-link--no-visited-state',
                                    'use_route_match' => true,
                                    'pages' => [
                                        [
                                            'id' => 'licence_bus_fees_details',
                                            'label' => 'Fee details',
                                            'route' => 'licence/bus-fees/fee_action',
                                            'class' => 'govuk-link--no-visited-state',
                                        ],
                                        [
                                            'id' => 'licence_bus_fees_transaction',
                                            'label' => 'Transaction details',
                                            'route' => 'licence/bus-fees/fee_action/transaction',
                                            'class' => 'govuk-link--no-visited-state',
                                        ],
                                    ]
                                ],
                            ]
                        ],
                        [
                            'id' => 'licence/cases',
                            'label' => 'Cases',
                            'route' => 'licence/cases',
                            'class' => 'govuk-link--no-visited-state',
                            'action' => 'cases',
                            'use_route_match' => true
                        ],
                        [
                            'id' => 'licence_opposition',
                            'label' => 'Opposition',
                            'route' => 'licence/opposition',
                            'class' => 'govuk-link--no-visited-state',
                            'use_route_match' => true
                        ],
                        [
                            'id' => 'licence_irhp_permits',
                            'label' => 'IRHP Permits',
                            'route' => 'licence/irhp-permits',
                            'class' => 'govuk-link--no-visited-state',
                            'use_route_match' => true,
                            'pages' => [
                                [
                                    'id' => 'licence_irhp_permits-application',
                                    'label' => 'Permit Applications',
                                    'route' => 'licence/irhp-permits/application',
                                    'class' => 'govuk-link--no-visited-state',
                                    'use_route_match' => true,
                                    'pages' => [
                                        [
                                            'id' => 'licence_irhp_permits-application-details',
                                            'label' => 'Application details',
                                            'route' => 'licence/irhp-application/application',
                                            'class' => 'govuk-link--no-visited-state',
                                            'use_route_match' => true,
                                            'params' => [
                                                'action' => 'details',
                                            ],
                                            'pages' => [
                                                [
                                                    'id' => 'licence_irhp_applications-edit',
                                                    'label' => 'Application',
                                                    'route' => 'licence/irhp-application/application',
                                                    'class' => 'govuk-link--no-visited-state',
                                                    'use_route_match' => true,
                                                    'params' => [
                                                        'action' => 'edit',
                                                    ],
                                                ],
                                                [
                                                    'id' => 'irhp_permits-permits',
                                                    'label' => 'Permits',
                                                    'route' => 'licence/irhp-application/application',
                                                    'class' => 'govuk-link--no-visited-state',
                                                    'params' => [
                                                        'action' => 'viewpermits',
                                                    ],
                                                    'use_route_match' => true,
                                                ],
                                                [
                                                    'id' => 'licence_irhp_applications-pregrant',
                                                    'label' => 'Permits',
                                                    'route' => 'licence/irhp-application/application',
                                                    'class' => 'govuk-link--no-visited-state',
                                                    'visible' => 0,
                                                    'use_route_match' => true,
                                                    'params' => [
                                                        'action' => 'preGrant',
                                                        'id' => null
                                                    ],
                                                ],
                                            ]
                                        ],
                                        [
                                            'id' => 'licence_irhp_applications-document',
                                            'label' => 'Docs & Attachments',
                                            'route' => 'licence/irhp-application-docs',
                                            'class' => 'govuk-link--no-visited-state',
                                            'use_route_match' => true,
                                        ],
                                        [
                                            'id' => 'licence_irhp_applications-processing',
                                            'label' => 'Processing',
                                            'route' => 'licence/irhp-application/application',
                                            'class' => 'govuk-link--no-visited-state',
                                            'use_route_match' => true,
                                            'params' => [
                                                'action' => 'processing',
                                                'id' => null
                                            ],
                                            'pages' => [
                                                [
                                                    'id' => 'licence_irhp_applications_processing_notes',
                                                    'label' => 'Notes',
                                                    'route' => 'licence/irhp-application-processing/notes',
                                                    'class' => 'govuk-link--no-visited-state',
                                                    'use_route_match' => true,
                                                ],
                                                [
                                                    'id' => 'licence_irhp_applications_processing_tasks',
                                                    'label' => 'Tasks',
                                                    'route' => 'licence/irhp-application-processing/tasks',
                                                    'class' => 'govuk-link--no-visited-state',
                                                    'use_route_match' => true,
                                                ],
                                                [
                                                    'id' => 'licence_irhp_applications_processing_event-history',
                                                    'label' => 'Change history',
                                                    'route' => 'licence/irhp-application-processing/event-history',
                                                    'class' => 'govuk-link--no-visited-state',
                                                    'use_route_match' => true,
                                                ],
                                                [
                                                    'id' => 'licence_irhp_applications_processing_read-history',
                                                    'label' => 'internal-crud-read-history',
                                                    'route' => 'licence/irhp-application-processing/read-history',
                                                    'class' => 'govuk-link--no-visited-state',
                                                    'use_route_match' => true,
                                                ],
                                            ]
                                        ],
                                        [
                                            'id' => 'licence_irhp_applications-fees',
                                            'label' => 'Fees',
                                            'route' => 'licence/irhp-application-fees/table',
                                            'class' => 'govuk-link--no-visited-state',
                                            'use_route_match' => true,
                                            'pages' => [
                                                [
                                                    'id' => 'licence_irhp_applications-fees_details',
                                                    'label' => 'Fee details',
                                                    'route' => 'licence/irhp-application-fees/fee_action',
                                                    'class' => 'govuk-link--no-visited-state',
                                                ],
                                                [
                                                    'id' => 'licence_irhp_applications-fees_transaction',
                                                    'label' => 'Transaction details',
                                                    'route' => 'licence/irhp-application-fees/fee_action/transaction',
                                                    'class' => 'govuk-link--no-visited-state',
                                                ],
                                            ]
                                        ],
                                        [
                                            'id' => 'licence_irhp_applications-cancel',
                                            'label' => 'Cancel',
                                            'visible' => 0,
                                            'route' => 'licence/irhp-application/application',
                                            'class' => 'govuk-link--no-visited-state',
                                            'use_route_match' => true,
                                            'params' => [
                                                'action' => 'cancel',
                                                'id' => null
                                            ],
                                        ],
                                        [
                                            'id' => 'licence_irhp_applications-submit',
                                            'label' => 'Submit',
                                            'visible' => 0,
                                            'route' => 'licence/irhp-application/application',
                                            'class' => 'govuk-link--no-visited-state',
                                            'use_route_match' => true,
                                            'params' => [
                                                'action' => 'submit',
                                                'id' => null
                                            ],
                                        ],
                                    ],
                                ],
                                [
                                    'id' => 'licence_irhp_permits-permit',
                                    'label' => 'Issued Permits',
                                    'route' => 'licence/irhp-permits/permit',
                                    'class' => 'govuk-link--no-visited-state',
                                    'use_route_match' => true,
                                ],
                            ]
                        ],
                        [
                            'id' => 'licence_documents',
                            'label' => 'Docs & attachments',
                            'route' => 'licence/documents',
                            'class' => 'govuk-link--no-visited-state',
                            'use_route_match' => true
                        ],
                        [
                            'id' => 'licence_processing',
                            'label' => 'Processing',
                            'route' => 'licence/processing',
                            'class' => 'govuk-link--no-visited-state',
                            'use_route_match' => true,
                            'pages' => [
                                [
                                    'id' => 'licence_processing_publications',
                                    'label' => 'internal-licence-processing-publications',
                                    'route' => 'licence/processing/publications',
                                    'class' => 'govuk-link--no-visited-state',
                                    'use_route_match' => true,
                                ],
                                [
                                    'id' => 'licence_processing_inspection_request',
                                    'label' => 'internal-licence-processing-inspection-request',
                                    'route' => 'licence/processing/inspection-request',
                                    'class' => 'govuk-link--no-visited-state',
                                    'use_route_match' => true,
                                ],
                                [
                                    'id' => 'licence_processing_notes',
                                    'label' => 'internal-licence-processing-notes',
                                    'route' => 'licence/processing/notes',
                                    'class' => 'govuk-link--no-visited-state',
                                    'use_route_match' => true,
                                    'pages' => [
                                        [
                                            'id' => 'licence_processing_notes_add',
                                            'label' => 'internal-licence-processing-notes-add',
                                            'route' => 'licence/processing/add-note',
                                            'class' => 'govuk-link--no-visited-state',
                                            'use_route_match' => true
                                        ],
                                        [
                                            'id' => 'licence_processing_notes_modify',
                                            'label' => 'internal-licence-processing-notes-modify',
                                            'route' => 'licence/processing/modify-note',
                                            'class' => 'govuk-link--no-visited-state',
                                            'use_route_match' => true
                                        ]
                                    ]
                                ],
                                [
                                    'id' => 'licence_processing_tasks',
                                    'label' => 'internal-licence-processing-tasks',
                                    'route' => 'licence/processing/tasks',
                                    'class' => 'govuk-link--no-visited-state',
                                    'use_route_match' => true,
                                ],
                                [
                                    'id' => 'licence_processing_event-history',
                                    'label' => 'internal-crud-event-history',
                                    'route' => 'licence/processing/event-history',
                                    'class' => 'govuk-link--no-visited-state',
                                    'use_route_match' => true,
                                ],
                                [
                                    'id' => 'licence_processing_read-history',
                                    'label' => 'internal-crud-read-history',
                                    'route' => 'licence/processing/read-history',
                                    'class' => 'govuk-link--no-visited-state',
                                    'use_route_match' => true,
                                ],
                            ]
                        ],
                        [
                            'id' => 'licence_fees',
                            'label' => 'Fees',
                            'route' => 'licence/fees',
                            'class' => 'govuk-link--no-visited-state',
                            'use_route_match' => true,
                            'pages' => [
                                [
                                    'id' => 'licence_fees_details',
                                    'label' => 'Fee details',
                                    'route' => 'licence/fees/fee_action',
                                    'class' => 'govuk-link--no-visited-state',
                                ],
                                [
                                    'id' => 'licence_fees_transaction',
                                    'label' => 'Transaction details',
                                    'route' => 'licence/fees/fee_action/transaction',
                                    'class' => 'govuk-link--no-visited-state',
                                ],
                            ],
                        ],
                        [
                            'id' => 'licence_surrender',
                            'label' => 'Surrender',
                            'route' => 'licence/surrender-details/GET',
                            'class' => 'govuk-link--no-visited-state',
                            'use_route_match' => true,
                            'pages' => [
                                [
                                    'id' => 'licence_surrender_details',
                                    'label' => 'Surrender details',
                                    'route' => 'licence/surrender-details/GET',
                                    'class' => 'govuk-link--no-visited-state',
                                ]
                            ],
                        ],
                        [
                            'id' => 'conversations',
                            'label' => 'Messages',
                            'route' => 'licence/conversation',
                            'class' => 'govuk-link--no-visited-state',
                            'use_route_match' => true,
                            'pages' => [
                                [
                                    'id' => 'conversations_list',
                                    'label' => 'Messages',
                                    'route' => 'licence/conversation',
                                    'class' => 'govuk-link--no-visited-state',
                                    'use_route_match' => true,

                                ],
                                [
                                    'id' => 'conversation_list_new_conversation',
                                    'label' => 'New Conversation',
                                    'route' => 'licence/conversation/new',
                                    'class' => 'govuk-link--no-visited-state',
                                    'use_route_match' => true,

                                ],
                                [
                                    'id' => 'conversation_list_disable_messaging',
                                    'label' => 'Disable Messaging',
                                    'route' => 'licence/conversation/disable',
                                    'class' => 'govuk-link--no-visited-state',
                                    'use_route_match' => true,
                                ]
                            ],
                        ],
                    ],
                ],
                [
                    'id' => 'transport_manager',
                    'label' => 'internal-navigation-transport-manager',
                    'route' => 'transport-manager',
                    'class' => 'govuk-link--no-visited-state',
                    'use_route_match' => true,
                    'pages' => [
                        [
                            'id' => 'transport_manager_details',
                            'label' => 'internal-navigation-transport-manager-details',
                            'route' => 'transport-manager/details',
                            'class' => 'govuk-link--no-visited-state',
                            'use_route_match' => true,
                            'pages' => [
                                [
                                    'id' => 'transport_manager_details_details',
                                    'label' => 'internal-navigation-transport-manager-details-details',
                                    'route' => 'transport-manager/details',
                                    'class' => 'govuk-link--no-visited-state',
                                    'use_route_match' => true
                                ],
                                [
                                    'id' => 'transport_manager_details_competences',
                                    'label' => 'internal-navigation-transport-manager-details-competences',
                                    'route' => 'transport-manager/details/competences',
                                    'class' => 'govuk-link--no-visited-state',
                                    'use_route_match' => true,
                                    'params' => [
                                        'action' => null,
                                        'id' => null,
                                    ],
                                ],
                                [
                                    'id' => 'transport_manager_details_responsibility',
                                    'label' => 'internal-navigation-transport-manager-details-responsibilities',
                                    'route' => 'transport-manager/details/responsibilities',
                                    'class' => 'govuk-link--no-visited-state',
                                    'use_route_match' => true,
                                    'params' => [
                                        'action' => null,
                                        'id' => null,
                                    ],
                                ],
                                [
                                    'id' => 'transport_manager_details_employment',
                                    'label' => 'internal-navigation-transport-manager-details-employment',
                                    'route' => 'transport-manager/details/employment',
                                    'class' => 'govuk-link--no-visited-state',
                                    'use_route_match' => true,
                                    'params' => [
                                        'action' => null,
                                        'id' => null,
                                    ],
                                ],
                                [
                                    'id' => 'transport_manager_details_previous_history',
                                    'label' => 'internal-navigation-transport-manager-previous-history',
                                    'route' => 'transport-manager/details/previous-history',
                                    'class' => 'govuk-link--no-visited-state',
                                    'use_route_match' => true,
                                    'params' => [
                                        'action' => null,
                                        'id' => null,
                                    ],
                                ],
                            ],
                        ],
                        [
                            'id' => 'transport_manager_cases',
                            'label' => 'internal-navigation-transport-manager-cases',
                            'route' => 'transport-manager/cases',
                            'class' => 'govuk-link--no-visited-state',
                            'use_route_match' => true,
                        ],
                        [
                            'id' => 'transport_manager_documents',
                            'label' => 'internal-navigation-transport-manager-documents',
                            'route' => 'transport-manager/documents',
                            'class' => 'govuk-link--no-visited-state',
                            'use_route_match' => true,
                        ],
                        [
                            'id' => 'transport_manager_processing',
                            'label' => 'internal-navigation-transport-manager-processing',
                            'route' => 'transport-manager/processing',
                            'class' => 'govuk-link--no-visited-state',
                            'use_route_match' => true,
                            'pages' => [
                                [
                                    'id' => 'transport_manager_processing_publications',
                                    'label' => 'internal-navigation-transport-manager-processing-publications',
                                    'route' => 'transport-manager/processing/publication',
                                    'class' => 'govuk-link--no-visited-state',
                                    'use_route_match' => true,
                                ],
                                [
                                    'id' => 'transport_manager_processing_event-history',
                                    'label' => 'internal-crud-event-history',
                                    'route' => 'transport-manager/processing/event-history',
                                    'class' => 'govuk-link--no-visited-state',
                                    'use_route_match' => true,
                                ],
                                [
                                    'id' => 'transport_manager_processing_read-history',
                                    'label' => 'internal-crud-read-history',
                                    'route' => 'transport-manager/processing/read-history',
                                    'class' => 'govuk-link--no-visited-state',
                                    'use_route_match' => true,
                                ],
                                [
                                    'id' => 'transport_manager_processing_notes',
                                    'label' => 'internal-navigation-transport-manager-processing-notes',
                                    'route' => 'transport-manager/processing/notes',
                                    'class' => 'govuk-link--no-visited-state',
                                    'use_route_match' => true,
                                ],
                                [
                                    'id' => 'transport_manager_processing_tasks',
                                    'label' => 'internal-navigation-transport-manager-processing-tasks',
                                    'route' => 'transport-manager/processing/tasks',
                                    'class' => 'govuk-link--no-visited-state',
                                    'use_route_match' => true,
                                ],
                            ]
                        ],
                    ]
                ],
                [
                    'id' => 'operator',
                    'label' => 'internal-navigation-operator',
                    'route' => 'operator',
                    'class' => 'govuk-link--no-visited-state',
                    'use_route_match' => true,
                    'pages' => [
                        [
                            'id' => 'operator_profile',
                            'label' => 'internal-navigation-operator-profile',
                            'route' => 'operator',
                            'class' => 'govuk-link--no-visited-state',
                            'use_route_match' => true,
                            'pages' => [
                                [
                                    'id' => 'unlicensed_operator_business_details',
                                    'label' => 'internal-navigation-operator-business_details',
                                    'route' => 'operator-unlicensed/business-details',
                                    'class' => 'govuk-link--no-visited-state',
                                    'use_route_match' => true,
                                ],
                                [
                                    'id' => 'operator_business_details',
                                    'label' => 'internal-navigation-operator-business_details',
                                    'route' => 'operator/business-details',
                                    'class' => 'govuk-link--no-visited-state',
                                    'use_route_match' => true,
                                ],
                                [
                                    'id' => 'operator_people',
                                    'label' => 'internal-navigation-operator-people',
                                    'route' => 'operator/people',
                                    'class' => 'govuk-link--no-visited-state',
                                    'use_route_match' => true,
                                    'action' => 'index',
                                ],
                                [
                                    'id' => 'operator_licences',
                                    'label' => 'internal-navigation-operator-licences',
                                    'route' => 'operator/licences',
                                    'class' => 'govuk-link--no-visited-state',
                                    'use_route_match' => true,
                                ],
                                [
                                    'id' => 'operator_applications',
                                    'label' => 'internal-navigation-operator-applications',
                                    'route' => 'operator/applications',
                                    'class' => 'govuk-link--no-visited-state',
                                    'use_route_match' => true,
                                ],
                                [
                                    'id' => 'unlicensed_operator_vehicles',
                                    'label' => 'internal-navigation-operator-vehicles',
                                    'route' => 'operator-unlicensed/vehicles',
                                    'use_route_match' => true,
                                ],
                                [
                                    'id' => 'operator_users',
                                    'label' => 'internal-navigation-operator-users',
                                    'route' => 'operator/users',
                                    'class' => 'govuk-link--no-visited-state',
                                    'use_route_match' => true,
                                ],
                            ]
                        ],
                        [
                            'id' => 'operator_irfo',
                            'label' => 'internal-navigation-operator-irfo',
                            'route' => 'operator/irfo',
                            'class' => 'govuk-link--no-visited-state',
                            'use_route_match' => true,
                            'pages' => [
                                [
                                    'id' => 'operator_irfo_details',
                                    'label' => 'internal-navigation-operator-irfo-details',
                                    'route' => 'operator/irfo/details',
                                    'class' => 'govuk-link--no-visited-state',
                                    'use_route_match' => true,
                                ],
                                [
                                    'id' => 'operator_irfo_gv_permits',
                                    'label' => 'internal-navigation-operator-irfo-gv_permits',
                                    'route' => 'operator/irfo/gv-permits',
                                    'class' => 'govuk-link--no-visited-state',
                                    'action' => 'index',
                                    'use_route_match' => true,
                                ],
                                [
                                    'id' => 'operator_irfo_psv_authorisations',
                                    'label' => 'internal-navigation-operator-irfo-psv_authorisations',
                                    'route' => 'operator/irfo/psv-authorisations',
                                    'class' => 'govuk-link--no-visited-state',
                                    'action' => 'index',
                                    'use_route_match' => true,
                                ]
                            ]
                        ],
                        [
                            'id' => 'unlicensed_operator_cases',
                            'label' => 'internal-navigation-operator-cases',
                            'route' => 'operator-unlicensed/cases',
                            'class' => 'govuk-link--no-visited-state',
                            'use_route_match' => true,
                        ],
                        [
                            'id' => 'operator_processing',
                            'label' => 'internal-navigation-operator-processing',
                            'route' => 'operator/processing/history',
                            'class' => 'govuk-link--no-visited-state',
                            'use_route_match' => true,
                            'params' => [
                                'action' => null,
                            ],
                            'pages' => [
                                [
                                    'id' => 'operator_processing_history',
                                    'label' => 'internal-crud-event-history',
                                    'route' => 'operator/processing/history',
                                    'class' => 'govuk-link--no-visited-state',
                                    'use_route_match' => true,
                                ],
                                [
                                    'id' => 'operator_processing_read_history',
                                    'label' => 'internal-crud-read-history',
                                    'route' => 'operator/processing/read-history',
                                    'class' => 'govuk-link--no-visited-state',
                                    'use_route_match' => true,
                                ],
                                [
                                    'id' => 'operator_processing_notes',
                                    'label' => 'internal-navigation-operator-processing-notes',
                                    'route' => 'operator/processing/notes',
                                    'class' => 'govuk-link--no-visited-state',
                                    'use_route_match' => true,
                                ],
                                [
                                    'id' => 'operator_processing_tasks',
                                    'label' => 'internal-navigation-operator-processing-tasks',
                                    'route' => 'operator/processing/tasks',
                                    'class' => 'govuk-link--no-visited-state',
                                    'use_route_match' => true,
                                ],
                            ]
                        ],
                        [
                            'id' => 'operator_fees',
                            'label' => 'internal-navigation-operator-irfo-fees',
                            'route' => 'operator/fees',
                            'class' => 'govuk-link--no-visited-state',
                            'use_route_match' => true,
                            'pages' => [
                                [
                                    'id' => 'operator_fees_details',
                                    'label' => 'Fee details',
                                    'route' => 'operator/fees/fee_action',
                                    'class' => 'govuk-link--no-visited-state',
                                ],
                                [
                                    'id' => 'operator_fees_transaction',
                                    'label' => 'Transaction details',
                                    'route' => 'operator/fees/fee_action/transaction',
                                    'class' => 'govuk-link--no-visited-state',
                                ],
                            ],
                        ],
                        [
                            'id' => 'operator_documents',
                            'label' => 'internal-navigation-operator-documents',
                            'route' => 'operator/documents',
                            'class' => 'govuk-link--no-visited-state',
                            'use_route_match' => true,
                        ],
                    ]
                ],
            ],
        ],
        'application' => [
            'id' => 'application',
            'label' => 'Application',
            'route' => 'lva-application',
            'class' => 'govuk-link--no-visited-state',
            'use_route_match' => true,
            'pages' => [
                [
                    'id' => 'application_details',
                    'label' => 'Application details',
                    'route' => 'lva-application',
                    'class' => 'govuk-link--no-visited-state',
                    'use_route_match' => true,
                    'pages' => array_merge(
                        $applicationDetailsPages,
                        [
                            [
                                'id' => 'grant_application',
                                'label' => 'Grant application',
                                'route' => 'lva-application/grant',
                                'class' => 'govuk-link--no-visited-state',
                                'use_route_match' => true
                            ],
                            [
                                'id' => 'undogrant_application',
                                'label' => 'Undo grant application',
                                'route' => 'lva-application/undo-grant',
                                'class' => 'govuk-link--no-visited-state',
                                'use_route_match' => true
                            ]
                        ]
                    )
                ],
                [
                    'id' => 'application_case',
                    'label' => 'Cases',
                    'route' => 'lva-application/case',
                    'class' => 'govuk-link--no-visited-state',
                    'use_route_match' => true
                ],
                [
                    'id' => 'application_opposition',
                    'label' => 'Opposition',
                    'route' => 'lva-application/opposition',
                    'class' => 'govuk-link--no-visited-state',
                    'use_route_match' => true
                ],
                [
                    'id' => 'application_document',
                    'label' => 'Docs & attachments',
                    'route' => 'lva-application/documents',
                    'class' => 'govuk-link--no-visited-state',
                    'use_route_match' => true
                ],
                [
                    'id' => 'application_processing',
                    'label' => 'Processing',
                    'route' => 'lva-application/processing',
                    'class' => 'govuk-link--no-visited-state',
                    'use_route_match' => true,
                    'pages' => [
                        [
                            'id' => 'application_processing_publications',
                            'label' => 'internal-licence-processing-publications',
                            'route' => 'lva-application/processing/publications',
                            'class' => 'govuk-link--no-visited-state',
                            'use_route_match' => true,
                        ],
                        [
                            'id' => 'application_processing_inspection_request',
                            'label' => 'internal-application-processing-inspection-request',
                            'route' => 'lva-application/processing/inspection-request',
                            'class' => 'govuk-link--no-visited-state',
                            'use_route_match' => true,
                        ],
                        [
                            'id' => 'application_processing_notes',
                            'label' => 'internal-application-processing-notes',
                            'route' => 'lva-application/processing/notes',
                            'class' => 'govuk-link--no-visited-state',
                            'use_route_match' => true,
                            'pages' => [
                                [
                                    'id' => 'application_processing_notes_add',
                                    'label' => 'internal-application-processing-notes-add',
                                    'route' => 'lva-application/processing/add-note',
                                    'class' => 'govuk-link--no-visited-state',
                                    'use_route_match' => true
                                ],
                                [
                                    'id' => 'application_processing_notes_modify',
                                    'label' => 'internal-application-processing-notes-modify',
                                    'route' => 'lva-application/processing/modify-note',
                                    'class' => 'govuk-link--no-visited-state',
                                    'use_route_match' => true
                                ]
                            ]
                        ],
                        [
                            'id' => 'application_processing_tasks',
                            'label' => 'internal-application-processing-tasks',
                            'route' => 'lva-application/processing/tasks',
                            'class' => 'govuk-link--no-visited-state',
                            'use_route_match' => true,
                        ],
                        [
                            'id' => 'application_processing_history',
                            'label' => 'internal-crud-event-history',
                            'route' => 'lva-application/processing/event-history',
                            'class' => 'govuk-link--no-visited-state',
                            'use_route_match' => true,
                        ],
                        [
                            'id' => 'application_processing_read_history',
                            'label' => 'internal-crud-read-history',
                            'route' => 'lva-application/processing/read-history',
                            'class' => 'govuk-link--no-visited-state',
                            'use_route_match' => true,
                        ],
                    ]
                ],
                [
                    'id' => 'application_fee',
                    'label' => 'Fees',
                    'route' => 'lva-application/fees',
                    'class' => 'govuk-link--no-visited-state',
                    'use_route_match' => true,
                    'pages' => [
                        [
                            'id' => 'application_fee_details',
                            'label' => 'Fee details',
                            'route' => 'lva-application/fees/fee_action',
                            'class' => 'govuk-link--no-visited-state',
                        ],
                        [
                            'id' => 'application_fee_transaction',
                            'label' => 'Transaction details',
                            'route' => 'lva-application/fees/fee_action/transaction',
                            'class' => 'govuk-link--no-visited-state',
                        ],
                    ],
                ],
                [
                    'id' => 'application_conversations',
                    'label' => 'Messages',
                    'route' => 'lva-application/conversation',
                    'class' => 'govuk-link--no-visited-state',
                    'use_route_match' => true,
                    'pages' => [
                        [
                            'id' => 'application_conversations_list',
                            'label' => 'Messages',
                            'route' => 'lva-application/conversation',
                            'class' => 'govuk-link--no-visited-state',
                            'use_route_match' => true,
                        ],
                        [
                            'id' => 'conversation_list_new_conversation',
                            'label' => 'New Message',
                            'route' => 'lva-application/conversation/new',
                            'class' => 'govuk-link--no-visited-state',
                            'use_route_match' => true,
                        ],
                        [
                            'id' => 'conversation_list_disable_messaging',
                            'label' => 'Disable Messaging',
                            'route' => 'lva-application/conversation/disable',
                            'class' => 'govuk-link--no-visited-state',
                            'use_route_match' => true,
                        ]
                    ],
                ],
            ],
        ],
        'variation' => [
            'id' => 'variation',
            'label' => 'Application',
            'route' => 'lva-variation',
            'class' => 'govuk-link--no-visited-state',
            'use_route_match' => true,
            'pages' => [
                [
                    'id' => 'variation_details',
                    'label' => 'Application details',
                    'route' => 'lva-variation',
                    'class' => 'govuk-link--no-visited-state',
                    'use_route_match' => true,
                    'pages' => array_merge(
                        $variationDetailsPages,
                        [
                            [
                                'id' => 'grant_variation',
                                'label' => 'Grant application',
                                'route' => 'lva-variation/grant',
                                'class' => 'govuk-link--no-visited-state',
                                'use_route_match' => true
                            ],
                        ]
                    )
                ]
            ]
        ],
    ]
];

// @NOTE Here we dynamically attach all application navigation items to the variation node
$applicationPages = $nav['pages']['application']['pages'];
array_shift($applicationPages);
$nav['pages']['variation']['pages'] = array_merge($nav['pages']['variation']['pages'], $applicationPages);

return $nav;
