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
                            'label' => 'Add Case',
                            'route' => 'licence_case_action',
                            'action' => 'add'
                                ),
                        array(
                            'label' => 'Edit Case',
                            'route' => 'licence_case_action',
                            'action' => 'edit'
                                )
                            )
                        )
                    )
                )
            )
        )
    )
);
