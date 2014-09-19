<?php

return array(
    'label' => 'Home',
    'route' => 'dashboard',
    'use_route_match' => true,
    'pages' => array(
        array(
            'label' => 'Search',
            'route' => 'search',
            'use_route_match' => true,
            'pages' => array(
                array(
                    'id' => 'licence',
                    'label' => 'Licence',
                    'route' => 'licence',
                    'use_route_match' => true,
                    'pages' => array(
                        array(
                            'id' => 'licence_details_overview',
                            'label' => 'internal-licence-details-breadcrumb',
                            'route' => 'licence/details/overview',
                            'use_route_match' => true,
                            'pages' => array(
                                array(
                                    'id' => 'licence_details_type_of_licence',
                                    'label' => 'internal-licence-details-type_of_licence',
                                    'route' => 'licence/details/type_of_licence',
                                    'use_route_match' => true
                                ),
                                array(
                                    'id' => 'licence_details_business_details',
                                    'label' => 'internal-licence-details-business_details',
                                    'route' => 'licence/details/business_details',
                                    'use_route_match' => true
                                ),
                                array(
                                    'id' => 'licence_details_address',
                                    'label' => 'internal-licence-details-address',
                                    'route' => 'licence/details/address',
                                    'use_route_match' => true
                                ),
                                array(
                                    'id' => 'licence_details_people',
                                    'label' => 'internal-licence-details-people',
                                    'route' => 'licence/details/people',
                                    'use_route_match' => true
                                ),
                                array(
                                    'id' => 'licence_details_operating_centre',
                                    'label' => 'internal-licence-details-operating_centre',
                                    'route' => 'licence/details/operating_centre',
                                    'use_route_match' => true
                                ),
                                array(
                                    'id' => 'licence_details_transport_manager',
                                    'label' => 'internal-licence-details-transport_manager',
                                    'route' => 'licence/details/transport_manager',
                                    'use_route_match' => true
                                ),
                                array(
                                    'id' => 'licence_details_vehicle',
                                    'label' => 'internal-licence-details-vehicle',
                                    'route' => 'licence/details/vehicle',
                                    'use_route_match' => true
                                ),
                                array(
                                    'id' => 'licence_details_safety',
                                    'label' => 'internal-licence-details-safety',
                                    'route' => 'licence/details/safety',
                                    'use_route_match' => true
                                ),
                                array(
                                    'id' => 'licence_details_condition_undertaking',
                                    'label' => 'internal-licence-details-condition_undertaking',
                                    'route' => 'licence/details/condition_undertaking',
                                    'use_route_match' => true
                                ),
                                array(
                                    'id' => 'licence_details_taxi_phv',
                                    'label' => 'internal-licence-details-taxi_phv',
                                    'route' => 'licence/details/taxi_phv',
                                    'use_route_match' => true
                                )
                            )
                        ),
                        array(
                            'id' => 'licence_bus',
                            'label' => 'Bus reg',
                            'route' => 'licence/bus',
                            'use_route_match' => true,
                            'pages' => array (
                                array(
                                    'id' => 'licence_bus_details',
                                    'label' => 'internal-licence-bus-details',
                                    'route' => 'licence/bus-details',
                                    'use_route_match' => true,
                                    'pages' => array(
                                        array(
                                            'id' => 'licence_bus_details-service',
                                            'label' => 'internal-licence-bus-details-service',
                                            'route' => 'licence/bus-details/service',
                                            'use_route_match' => true,
                                        ),
                                        array(
                                            'id' => 'licence_bus_details-stop',
                                            'label' => 'internal-licence-bus-details-stop',
                                            'route' => 'licence/bus-details/stop',
                                            'use_route_match' => true,
                                        ),
                                        array(
                                            'id' => 'licence_bus_details-ta',
                                            'label' => 'internal-licence-bus-details-ta',
                                            'route' => 'licence/bus-details/ta',
                                            'use_route_match' => true,
                                        ),
                                        array(
                                            'id' => 'licence_bus_details-quality',
                                            'label' => 'internal-licence-bus-details-quality',
                                            'route' => 'licence/bus-details/quality',
                                            'use_route_match' => true,
                                        )
                                    )
                                ),
                                array(
                                    'id' => 'licence_bus_short',
                                    'label' => 'internal-licence-bus-short',
                                    'route' => 'licence/bus-short',
                                    'use_route_match' => true,
                                    'pages' => array(
                                        array(
                                            'id' => 'licence_bus_short-placeholder',
                                            'label' => 'internal-licence-bus-short-placeholder',
                                            'route' => 'licence/bus-short/placeholder',
                                            'use_route_match' => true,
                                        ),
                                    )
                                ),
                                array(
                                    'id' => 'licence_bus_route',
                                    'label' => 'internal-licence-bus-route',
                                    'route' => 'licence/bus-route',
                                    'use_route_match' => true,
                                    'pages' => array(
                                        array(
                                            'id' => 'licence_bus_route-placeholder',
                                            'label' => 'internal-licence-bus-route-placeholder',
                                            'route' => 'licence/bus-route/placeholder',
                                            'use_route_match' => true,
                                        ),
                                    )
                                ),
                                array(
                                    'id' => 'licence_bus_trc',
                                    'label' => 'internal-licence-bus-trc',
                                    'route' => 'licence/bus-trc',
                                    'use_route_match' => true,
                                    'pages' => array(
                                        array(
                                            'id' => 'licence_bus_trc-placeholder',
                                            'label' => 'internal-licence-bus-trc-placeholder',
                                            'route' => 'licence/bus-trc/placeholder',
                                            'use_route_match' => true,
                                        ),
                                    )
                                ),
                                array(
                                    'id' => 'licence_bus_docs',
                                    'label' => 'internal-licence-bus-docs',
                                    'route' => 'licence/bus-docs',
                                    'use_route_match' => true,
                                    'pages' => array(
                                        array(
                                            'id' => 'licence_bus_docs-placeholder',
                                            'label' => 'internal-licence-bus-docs-placeholder',
                                            'route' => 'licence/bus-docs/placeholder',
                                            'use_route_match' => true,
                                        ),
                                    )
                                ),
                                array(
                                    'id' => 'licence_bus_processing',
                                    'label' => 'internal-licence-bus-processing',
                                    'route' => 'licence/bus-processing',
                                    'use_route_match' => true,
                                    'pages' => array(
                                        array(
                                            'id' => 'licence_bus_processing_notes',
                                            'label' => 'internal-licence-bus-processing-notes',
                                            'route' => 'licence/bus-processing/notes',
                                            'use_route_match' => true,
                                            'pages' => array(
                                                array(
                                                    'id' => 'licence_bus_processing_notes_add',
                                                    'label' => 'internal-licence-bus-processing-notes-add',
                                                    'route' => 'licence/bus-processing/add-note',
                                                    'use_route_match' => true
                                                ),
                                                array(
                                                    'id' => 'licence_bus_processing_notes_modify',
                                                    'label' => 'internal-licence-bus-processing-notes-modify',
                                                    'route' => 'licence/bus-processing/modify-note',
                                                    'use_route_match' => true
                                                )
                                            )
                                        )
                                    )
                                ),
                                array(
                                    'id' => 'licence_bus_fees',
                                    'label' => 'internal-licence-bus-fees',
                                    'route' => 'licence/bus-fees',
                                    'use_route_match' => true,
                                    'pages' => array(
                                        array(
                                            'id' => 'licence_bus_fees-placeholder',
                                            'label' => 'internal-licence-bus-fees-placeholder',
                                            'route' => 'licence/bus-fees/placeholder',
                                            'use_route_match' => true,
                                        ),
                                    )
                                ),
                            )
                        ),
                        array(
                            'id' => 'licence_caselist',
                            'label' => 'Cases',
                            'route' => 'licence/cases',
                            'use_route_match' => true
                        ),
                        array(
                            'id' => 'licence_opposition',
                            'label' => 'Opposition',
                            'route' => 'licence/opposition',
                            'use_route_match' => true
                        ),
                        array(
                            'id' => 'licence_documents',
                            'label' => 'Docs & attachments',
                            'route' => 'licence/documents',
                            'use_route_match' => true
                        ),
                        array(
                            'id' => 'licence_processing',
                            'label' => 'Processing',
                            'route' => 'licence/processing',
                            'use_route_match' => true,
                            'pages' => array(
                                array(
                                    'id' => 'licence_processing_tasks',
                                    'label' => 'internal-licence-processing-tasks',
                                    'route' => 'licence/processing/tasks',
                                    'use_route_match' => true,
                                ),
                                array(
                                    'id' => 'licence_processing_notes',
                                    'label' => 'internal-licence-processing-notes',
                                    'route' => 'licence/processing/notes',
                                    'use_route_match' => true,
                                    'pages' => array(
                                        array(
                                            'id' => 'licence_processing_notes_add',
                                            'label' => 'internal-licence-processing-notes-add',
                                            'route' => 'licence/processing/add-note',
                                            'use_route_match' => true
                                        ),
                                        array(
                                            'id' => 'licence_processing_notes_modify',
                                            'label' => 'internal-licence-processing-notes-modify',
                                            'route' => 'licence/processing/modify-note',
                                            'use_route_match' => true
                                        )
                                    )
                                )
                            )
                        ),
                        array(
                            'id' => 'licence_fees',
                            'label' => 'Fees',
                            'route' => 'licence/fees',
                            'use_route_match' => true
                        )
                    )
                ),
                array(
                    'label' => 'Operators',
                    'route' => 'operators',
                    'use_route_match' => true,
                    'pages' => array(

                        array(
                            'label' => 'Add Case',
                            'route' => 'licence_case_action',
                            'action' => 'add',
                            'use_route_match' => true
                        ),
                        array(
                            'label' => 'Edit Case',
                            'route' => 'licence_case_action',
                            'action' => 'edit',
                            'use_route_match' => true
                        ),
                        array(
                            'id' => 'licence_cases',
                            'label' => 'Case list',
                            'route' => 'licence/cases',
                            'use_route_match' => true,
                            'pages' => array(
                                array(
                                    'label' => 'Overview',
                                    'route' => 'case_manage',
                                    'use_route_match' => true,
                                    'pages' => array(
                                        array(
                                        'label' => 'Case Submission',
                                        'route' => 'submission',
                                        'action' => 'add',
                                            'pages' => array(
                                                array(
                                                    'label' => 'Decision',
                                                    'route' => 'submission',
                                                    'action' => 'decision',
                                                    'use_route_match' => true,
                                                ),
                                                array(
                                                    'label' => 'Recommendation',
                                                    'route' => 'submission',
                                                    'action' => 'recommendation',
                                                    'use_route_match' => true,
                                                ),
                                                array(
                                                    'label' => 'Add note',
                                                    'route' => 'note',
                                                    'action' => 'add',
                                                    'use_route_match' => true,
                                                )
                                            ),
                                            'use_route_match' => true,
                                        ),
                                        array(
                                            'label' => 'Edit Submission',
                                            'route' => 'submission',
                                            'action' => 'edit',
                                            'use_route_match' => true,
                                            'pages' => array(
                                                array(
                                                    'label' => 'Decision',
                                                    'route' => 'submission',
                                                    'action' => 'decision'
                                                ),
                                                array(
                                                    'label' => 'Recommendation',
                                                    'route' => 'submission',
                                                    'action' => 'recommendation'
                                                ),
                                                array(
                                                    'label' => 'Add note',
                                                    'route' => 'note',
                                                    'action' => 'add'
                                                )
                                            )
                                        )
                                    )
                                ),
                                array(
                                    'label' => 'Convictions',
                                    'route' => 'case_convictions',
                                    'use_route_match' => true,
                                    'pages' => array(
                                        array(
                                            'label' => 'Add Conviction',
                                            'route' => 'conviction',
                                            'action' => 'add',
                                            'use_route_match' => true
                                        ),
                                        array(
                                            'label' => 'Edit Conviction',
                                            'route' => 'conviction',
                                            'action' => 'edit',
                                            'use_route_match' => true
                                        )
                                    )
                                ),
                                array(
                                    'label' => 'Prohibitions',
                                    'route' => 'case_prohibition',
                                    'action' => 'index',
                                    'use_route_match' => true,
                                    'pages' => array(
                                        array(
                                            'label' => 'Add Prohibition',
                                            'route' => 'case_prohibition',
                                            'action' => 'add',
                                            'use_route_match' => true
                                        ),
                                        array(
                                            'label' => 'Edit Prohibition',
                                            'route' => 'case_prohibition',
                                            'action' => 'edit',
                                            'use_route_match' => true
                                        )
                                    )
                                ),
                                array(
                                    'label' => 'Annual Test History',
                                    'route' => 'case_annual_test_history',
                                    'action' => 'index',
                                    'use_route_match' => true
                                ),
                                array(
                                    'label' => 'Penalties',
                                    'route' => 'case_penalty',
                                    'action' => 'index',
                                    'use_route_match' => true
                                ),
                                array(
                                    'label' => 'ERRU Penalties',
                                    'route' => 'case_manage',
                                    'action' => 'manage',
                                    'tab' => 'erru',
                                    'use_route_match' => true
                                ),
                                array(
                                    'label' => 'Statements',
                                    'route' => 'case_statement',
                                    'action' => 'index',
                                    'use_route_match' => true,
                                    'pages' => array(
                                        array(
                                            'label' => 'Add Statement',
                                            'route' => 'case_statement',
                                            'action' => 'add',
                                            'use_route_match' => true
                                        ),
                                        array(
                                            'label' => 'Edit Statement',
                                            'route' => 'case_statement',
                                            'action' => 'edit',
                                            'use_route_match' => true
                                        )
                                    )
                                ),
                                array(
                                    'label' => 'Complaints',
                                    'route' => 'case_complaints',
                                    'action' => 'index',
                                    'use_route_match' => true,
                                    'pages' => array(
                                        array(
                                            'label' => 'Add Complaint',
                                            'route' => 'complaint',
                                            'action' => 'add',
                                            'use_route_match' => true
                                        ),
                                        array(
                                            'label' => 'Edit Complaint',
                                            'route' => 'complaint',
                                            'action' => 'edit',
                                            'use_route_match' => true
                                        )
                                    ),
                                ),
                                array(
                                    'label' => 'Serious infringement',
                                    'route' => 'case_manage',
                                    'action' => 'si',
                                    'use_route_match' => true
                                ),
                                array(
                                    'label' => 'Appeal & Stays',
                                    'route' => 'case_stay_action',
                                    'action' => 'index',
                                    'use_route_match' => true,
                                    'pages' => array(
                                        array(
                                            'label' => 'Add Stay',
                                            'route' => 'case_stay_action',
                                            'action' => 'add',
                                            'use_route_match' => true
                                        ),
                                        array(
                                            'label' => 'Edit Stay',
                                            'route' => 'case_stay_action',
                                            'action' => 'edit',
                                            'use_route_match' => true
                                        )
                                        ,
                                        array(
                                            'label' => 'Add Appeal',
                                            'route' => 'case_appeal',
                                            'action' => 'add',
                                            'use_route_match' => true
                                        ),
                                        array(
                                            'label' => 'Edit Appeal',
                                            'route' => 'case_appeal',
                                            'action' => 'edit',
                                            'use_route_match' => true
                                        )
                                    )
                                ),
                                array(
                                    'label' => 'Documents',
                                    'route' => 'case_manage',
                                    'action' => 'documents',
                                    'use_route_match' => true
                                ),
                                array(
                                    'label' => 'Notes',
                                    'route' => 'case_manage',
                                    'action' => 'notes',
                                    'use_route_match' => true
                                ),
                                array(
                                    'label' => 'Conditions & Undertakings',
                                    'route' => 'case_conditions_undertakings',
                                    'action' => 'index',
                                    'use_route_match' => true,
                                    'pages' => array(
                                        array(
                                            'label' => 'Add Condition',
                                            'route' => 'conditions',
                                            'action' => 'add',
                                            'use_route_match' => true
                                        ),
                                        array(
                                            'label' => 'Edit Condition',
                                            'route' => 'conditions',
                                            'action' => 'edit',
                                            'use_route_match' => true
                                        ),
                                        array(
                                            'label' => 'Add Undertaking',
                                            'route' => 'undertakings',
                                            'action' => 'add',
                                            'use_route_match' => true
                                        ),
                                        array(
                                            'label' => 'Edit Undertaking',
                                            'route' => 'undertakings',
                                            'action' => 'edit',
                                            'use_route_match' => true
                                        )
                                    ),
                                ),
                                array(
                                    'label' => 'Impounding',
                                    'route' => 'case_impounding',
                                    'action' => 'index',
                                    'pages' => array(
                                        array(
                                            'label' => 'Add Impounding',
                                            'route' => 'case_impounding',
                                            'action' => 'add'
                                        ),
                                        array(
                                            'label' => 'Edit Impounding',
                                            'route' => 'case_impounding',
                                            'action' => 'edit'
                                        )
                                    )
                                ),
                                array(
                                    'label' => 'In-Office revocation',
                                    'route' => 'case_revoke',
                                    'action' => 'index',
                                    'use_route_match' => true,
                                    'pages' => array(
                                        array(
                                            'label' => 'Add In-Office revocation',
                                            'route' => 'case_revoke',
                                            'action' => 'add',
                                            'use_route_match' => true
                                        ),
                                        array(
                                            'label' => 'Edit In-Office revocation',
                                            'route' => 'case_revoke',
                                            'action' => 'edit',
                                            'use_route_match' => true
                                        )
                                    )
                                ),
                                array(
                                    'label' => 'Public inquiry',
                                    'route' => 'case_pi',
                                    'action' => 'index',
                                    'pages' => array(

                                    )
                                ),
                            )
                        )
                    )
                )
            )
        ),
        array(
            'id' => 'application',
            'label' => 'Application',
            'route' => 'Application',
            'use_route_match' => true,
            'pages' => array(
                array(
                    'id' => 'application_details',
                    'label' => 'Application details',
                    'route' => 'Application',
                    'use_route_match' => true,
                    'pages' => $navItems
                ),
                array(
                    'id' => 'application_case',
                    'label' => 'Cases',
                    'route' => 'Application/case',
                    'use_route_match' => true
                ),
                array(
                    'id' => 'application_environmental',
                    'label' => 'Environmental',
                    'route' => 'Application/environmental',
                    'use_route_match' => true
                ),
                array(
                    'id' => 'application_document',
                    'label' => 'Docs & attachments',
                    'route' => 'Application/document',
                    'use_route_match' => true
                ),
                array(
                    'id' => 'application_processing',
                    'label' => 'Processing',
                    'route' => 'Application/processing',
                    'use_route_match' => true
                ),
                array(
                    'id' => 'application_fee',
                    'label' => 'Fees',
                    'route' => 'Application/fee',
                    'use_route_match' => true
                ),
            )
        )
    )
);
