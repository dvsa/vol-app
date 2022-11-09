<?php

return [
    'settings' => [
        'paginate' => [
            'limit' => [
                'default' => \Olcs\Controller\Licence\Vehicle\ListVehicleController::DEFAULT_LIMIT_CURRENT_VEHICLES,
                'options' => [10, 25, 50],
            ],
        ],
        'overrideTotal' => true
    ],
    'columns' => [
        [
            'title' => 'table.licence-vehicle-list-current.column.vrm.title',
            'formatter' => 'VehicleLink',
            'sort' => 'v.vrm',
        ],
        [
            'title' => 'table.licence-vehicle-list-current.column.weight.title',
            'isNumeric' => true,
            'stack' => 'vehicle->platedWeight',
            'formatter' => 'NumberStackValue',
        ],
        [
            'title' => 'table.licence-vehicle-list-current.column.specified.title',
            'formatter' => 'Date',
            'name' => 'specifiedDate',
            'sort' => 'specifiedDate',
        ],
        [
            'title' => 'table.licence-vehicle-list-current.column.disc_no.title',
            'isNumeric' => true,
            'name' => 'discNo',
            'formatter' => 'VehicleDiscNo'
        ],
    ],
];
