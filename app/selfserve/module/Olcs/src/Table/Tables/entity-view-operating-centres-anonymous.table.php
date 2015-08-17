<?php

return array(
    'variables' => array(),
    'settings' => array(),
    'attributes' => array(),
    'columns' => array(
        array(
            'title' => 'search-result-label-operating-centre',
            'formatter' => 'Address',
            'name' => 'operatingCentre->address'
        ),
        array(
            'title' => 'search-result-label-vehicles',
            'formatter' => function ($data, $column) {
                if (empty($data['noOfVehiclesPossessed'])) {
                    return '0';
                }
                return $data['noOfVehiclesPossessed'];
            }
        ),
        array(
            'title' => 'search-result-label-trailers',
            'formatter' => function ($data, $column) {
                if (empty($data['noOfTrailersPossessed'])) {
                    return '0';
                }
                return $data['noOfTrailersPossessed'];
            }
        )
    )
);
