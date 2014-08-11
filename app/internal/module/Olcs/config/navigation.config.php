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
                            'id' => 'licence_edit',
                            'label' => 'Details',
                            'route' => 'licence/edit',
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
                            'label' => 'Case list',
                            'route' => 'licence_case_list',
                            'use_route_match' => true,
                            'pages' => array(
                                array(
                                    'label' => 'Case Summary',
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
                                        ),
                                        array(
                                        'label' => 'Edit Submission',
                                        'route' => 'submission',
                                        'action' => 'edit',
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
                                    'label' => 'Case Convictions',
                                    'route' => 'case_convictions',
                                    'use_route_match' => true,
                                    'pages' => array(
                                        array(
                                            'label' => 'Add Conviction',
                                            'route' => 'conviction',
                                            'action' => 'add'
                                        ),
                                        array(
                                            'label' => 'Edit Conviction',
                                            'route' => 'conviction',
                                            'action' => 'edit'
                                        )
                                    )
                                ),
                                array(
                                    'label' => 'Case',
                                    'route' => 'licence_case_action',
                                    'action' => 'add'
                                ),
                                array(
                                    'label' => 'Add Case',
                                    'route' => 'licence_case_action',
                                    'action' => 'add'
                                ),
                                array(
                                    'label' => 'Edit Case',
                                    'route' => 'licence_case_action',
                                    'action' => 'edit'
                                ),
                                array(
                                    'label' => 'Case Statements',
                                    'route' => 'case_statement',
                                    'action' => 'index',
                                    'pages' => array(
                                        array(
                                            'label' => 'Add Statement',
                                            'route' => 'case_statement',
                                            'action' => 'add'
                                        ),
                                        array(
                                            'label' => 'Edit Statement',
                                            'route' => 'case_statement',
                                            'action' => 'edit'
                                        )
                                    )
                                ),
                                array(
                                    'label' => 'Stays & Appeals',
                                    'route' => 'case_stay_action',
                                    'action' => 'index',
                                    'pages' => array(
                                        array(
                                            'label' => 'Add Stay',
                                            'route' => 'case_stay_action',
                                            'action' => 'add'
                                        ),
                                        array(
                                            'label' => 'Edit Stay',
                                            'route' => 'case_stay_action',
                                            'action' => 'edit'
                                        )
                                        ,
                                        array(
                                            'label' => 'Add Appeal',
                                            'route' => 'case_appeal',
                                            'action' => 'add'
                                        ),
                                        array(
                                            'label' => 'Edit Appeal',
                                            'route' => 'case_appeal',
                                            'action' => 'edit'
                                        )
                                    )
                                ),
                                array(
                                    'label' => 'In-Office revocation',
                                    'route' => 'case_revoke',
                                    'action' => 'index',
                                    'pages' => array(
                                        array(
                                            'label' => 'Add In-Office revocation',
                                            'route' => 'case_revoke',
                                            'action' => 'add'
                                        ),
                                        array(
                                            'label' => 'Edit In-Office revocation',
                                            'route' => 'case_revoke',
                                            'action' => 'edit'
                                        )
                                    )
                                ),
                                array(
                                    'label' => 'Case Complaints',
                                    'route' => 'case_complaints',
                                    'action' => 'index',
                                    'use_route_match' => true,
                                    'pages' => array(
                                        array(
                                            'label' => 'Add Complaint',
                                            'route' => 'complaint',
                                            'action' => 'add'
                                        ),
                                        array(
                                            'label' => 'Edit Complaint',
                                            'route' => 'complaint',
                                        )
                                    ),
                                ),
                                array(
                                    'label' => 'Case Penalties',
                                    'route' => 'case_penalty',
                                    'action' => 'index',
                                ),
                                array(
                                    'label' => 'Case Prohibitions',
                                    'route' => 'case_prohibition',
                                    'action' => 'index'
                                ),
                                array(
                                    'label' => 'Case Annual Test History',
                                    'route' => 'case_annual_test_history',
                                    'action' => 'index'
                                ),
                                array(
                                    'label' => 'Conditions &amp; Undertakings',
                                    'route' => 'case_conditions_undertakings',
                                    'action' => 'index',
                                    'use_route_match' => true,
                                    'pages' => array(
                                        array(
                                            'label' => 'Add Condition',
                                            'route' => 'conditions',
                                            'action' => 'add'
                                        ),
                                        array(
                                            'label' => 'Edit Condition',
                                            'route' => 'conditions',
                                        ),
                                        array(
                                            'label' => 'Add Undertaking',
                                            'route' => 'undertakings',
                                            'action' => 'add'
                                        ),
                                        array(
                                            'label' => 'Edit Undertaking',
                                            'route' => 'undertakings',
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
