<?php

return array(
    'variables' => array(),
    'settings' => array(),
    'attributes' => array(),
    'columns' => array(
        array(
            'title' => 'search-result-label-operating-centre',
            'formatter' => 'Address',
            'addressFields' => 'FULL',
            'name' => 'operatingCentre->address'
        ),
        array(
            'title' => 'search-result-label-vehicles',
            'formatter' => function ($data, $column) {
                if (empty($data['noOfVehiclesRequired'])) {
                    return '0';
                }
                return $data['noOfVehiclesRequired'];
            }
        ),
        array(
            'title' => 'search-result-label-trailers',
            'formatter' => function ($data, $column) {
                if (empty($data['noOfTrailersRequired'])) {
                    return '0';
                }
                return $data['noOfTrailersRequired'];
            }
        )
    )
);
