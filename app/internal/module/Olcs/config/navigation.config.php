<?php

return array(
    'label' => 'Home',
    'route' => 'dashboard',
    'pages' => array(
        array(
            'label' => 'Search',
            'route' => 'search',
            'pages' => array(
                array(
                    'label' => 'Operators',
                    'route' => 'operators/operators-params',
                    'pages' => array(
                        array(
                            'label' => 'Case list',
                            'route' => 'licence_case_list/pagination',
                            'pages' => array(
                                array(
                                    'label' => 'Case Summary',
                                    'route' => 'case_manage',
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
                                                )
                                            )
                                        )
                                    )
                                ),
                                array(
                                    'label' => 'Case Convictions',
                                    'route' => 'case_convictions',
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
                                )

                            )
                        )
                    )
                )
            )
        )
    )
);
