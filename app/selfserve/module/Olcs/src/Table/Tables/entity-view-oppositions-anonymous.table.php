<?php

use Common\Service\Table\Formatter\Address;

return [
    'variables' => [],
    'settings' => [],
    'attributes' => [],
    'columns' => [
        [
            'title' => 'search-result-label-operating-centre',
            'formatter' => Address::class,
            'addressFields' => 'FULL',
            'name' => 'operatingCentre->address'
        ],
        [
            'title' => 'search-result-label-vehicles',
            'isNumeric' => true,
            'formatter' => function ($data, $column) {
                if (empty($data['noOfVehiclesRequired'])) {
                    return '0';
                }
                return $data['noOfVehiclesRequired'];
            }
        ],
        [
            'title' => 'search-result-label-trailers',
            'isNumeric' => true,
            'formatter' => function ($data, $column) {
                if (empty($data['noOfTrailersRequired'])) {
                    return '0';
                }
                return $data['noOfTrailersRequired'];
            }
        ]
    ]
];
