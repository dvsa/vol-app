<?php

use Common\Service\Table\Formatter\Address;

return array(
    'variables' => array(),
    'settings' => array(),
    'attributes' => array(),
    'columns' => array(
        array(
            'title' => 'search-result-label-operating-centre',
            'formatter' => Address::class,
            'addressFields' => 'FULL',
            'name' => 'operatingCentre->address'
        ),
        array(
            'title' => 'search-result-label-vehicles',
            'isNumeric' => true,
            'formatter' => function ($data, $column) {
                if (empty($data['noOfVehiclesRequired'])) {
                    return '0';
                }
                return $data['noOfVehiclesRequired'];
            }
        ),
        array(
            'title' => 'search-result-label-trailers',
            'isNumeric' => true,
            'formatter' => function ($data, $column) {
                if (empty($data['noOfTrailersRequired'])) {
                    return '0';
                }
                return $data['noOfTrailersRequired'];
            }
        )
    )
);
