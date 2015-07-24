<?php

return array(
    'variables' => array(
        'title' => 'search-result-header-vehicles',
    ),
    'settings' => array(),
    'attributes' => array(),
    'columns' => array(
        array(
            'title' => 'vehicle-registration',
            'formatter' => 'Address',
            'name' => 'address'
        ),
        array(
            'title' => 'interim',
            'formatter' => 'YesNo',
            'name' => 'interim'
        ),
        array(
            'title' => 'vehicle-auth.',
            'name' => 'vehicleAuth'
        ),
        array(
            'title' => 'trailer-auth.',
            'name' => 'trailerAuth'
        ),
        array(
            'title' => 'added',
            'formatter' => 'Date',
            'name' => 'dateAdded'
        ),
        array(
            'title' => 'removed',
            'formatter' => 'Date',
            'name' => 'dateRemoved'
        )
    )
);
