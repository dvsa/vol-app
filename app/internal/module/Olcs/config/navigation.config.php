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
                            'id' => 'licence_overview',
                            'class' => 'horizontal-navigation__item',
                            'label' => 'Overview',
                            'route' => 'licence/overview',
                            'use_route_match' => true
                        ),
                        array(
                            'id' => 'licence_details_overview',
                            'label' => 'Details',
                            'route' => 'licence/details/overview',
                            'use_route_match' => true
                        ),
                        array(
                            'id' => 'licence_bus',
                            'label' => 'Bus reg',
                            'route' => 'licence/bus',
                            'use_route_match' => true
                        ),
                        array(
                            'id' => 'licence_caselist',
                            'label' => 'Cases',
                            'route' => 'licence/cases',
                            'use_route_match' => true
                        ),
                        array(
                            'id' => 'licence_documents',
                            'label' => 'Documents',
                            'route' => 'licence/documents',
                            'use_route_match' => true
                        ),
                        array(
                            'id' => 'licence_processing',
                            'label' => 'Processing',
                            'route' => 'licence/processing',
                            'use_route_match' => true
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
        )
    )
);
