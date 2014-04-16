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
                        )
                    )
                )
            )
        )
    )
);
