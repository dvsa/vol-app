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
                                )
                            )
                        )
                    )
                )
            )
        )
    )
);
