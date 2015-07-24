<?php

return array(
    'variables' => array(
        'title' => 'search-result-header-operating-centres',
    ),
    'settings' => array(),
    'attributes' => array(),
    'columns' => array(
        array(
            'title' => 'sddress',
            'formatter' => 'Address',
            'name' => 'address'
        ),
        array(
            'title' => 'vehicles-auth',
            'name' => 'vehicleAuth'
        ),
        array(
            'title' => 'trailer-auth',
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
