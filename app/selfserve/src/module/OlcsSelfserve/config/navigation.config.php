<?php
    return array(
        'label' => 'Home',
        'route' => 'home',
        'pages' => array(
            array(
                'label' => 'Search',
                'route' => 'lookup',
                'action' => 'index',
                'pages' => array(
                    array(
                        'label' => 'search results',
                        'route' => 'operator_results',
                        'action' => 'operatorResults',
                        'pages' => array(
                            array(
                                'label' => 'list of cases',
                                'route' => 'case_list',
                                'action' => 'index',
                                'pages' => array(
                                    array(
                                        'label' => 'create new case',
                                        'route' => 'case_new',
                                        'action' => 'index',
                                    ),
                                    array(
                                        'label' => 'edit case',
                                        'route' => 'case_dashboard',
                                        'action' => 'index',
                                    ),
                                    array(
                                        'label' => 'edit case',
                                        'route' => 'case_convictions',
                                        'action' => 'index',
                                    ),
                                    array(
                                        'label' => 'create new case',
                                        'route' => 'case_submission_view',
                                        'action' => 'view',
                                    )
                                )
                            )
                        )
                    )
                )

            ),
            array(
                'label' => 'Create new application',
                'route' => 'application_new',
                'action' => 'index'
            ),
            array(
                'label' => 'Create new application',
                'route' => 'application_new_details',
                'action' => 'details'
            ),
            array(
                'label' => 'Licence details',
                'route' => 'application_licence_details',
                'action' => 'details'
            ),
            array(
                'label' => 'Application fees',
                'route' => 'application_fees_list',
                'action' => 'feesList'
            )
        )
    )
?>