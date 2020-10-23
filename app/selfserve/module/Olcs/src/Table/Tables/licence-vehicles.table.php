<?php

use Olcs\Controller\Licence\Vehicle\AbstractVehicleController;

$translationPrefix = 'licence.vehicle.table';

return [
    'variables' => [
        'title' => '',
        'titleSingular' => '',
        'empty_message' => '',
        'within_form' => true
    ],
    'settings' => [
        'paginate' => [
            'limit' => [
                'default' => AbstractVehicleController::DEFAULT_TABLE_ROW_LIMIT,
                'options' => [10, 25, 50],
            ],
        ],
        'overrideTotal' => true
    ],
    'columns' => [
        [
            'title' => $translationPrefix . '.vrm',
            'formatter' => 'VehicleLink',
            'sort' => 'v.vrm'
        ],
        [
            'title' => $translationPrefix . '.weight',
            'stack' => 'vehicle->platedWeight',
            'formatter' => 'NumberStackValue',
        ],
        [
            'title' => $translationPrefix . '.specified',
            'formatter' => 'Date',
            'name' => 'specifiedDate',
            'sort' => 'specifiedDate'
        ],
        [
            'title' => $translationPrefix . '.disc-no',
            'name' => 'discNo',
            'formatter' => 'VehicleDiscNo'
        ],
        [
            'title' => 'Select',
            'name' => 'action',
            'width' => 'checkbox',
            'type' => 'Checkbox',
            'selectAll' => false
        ]
    ]
];
