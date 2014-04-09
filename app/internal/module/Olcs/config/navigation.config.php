<?php

return array(
    'label' => 'Home',
    'route' => 'olcsHome',
    'pages' => array(
        array(
            'label' => 'Search',
            'route' => 'search',
            'action' => 'index',
            'pages' => array(
                array(
                    'label' => 'Operators',
                    'route' => 'operators',
                    'action' => 'operator'
                )
            )
        )
    )
);
