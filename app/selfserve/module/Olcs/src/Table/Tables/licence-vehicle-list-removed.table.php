<?php

return [
    'paginate' => [
        'limit' => [
            'default' => \Olcs\Controller\Licence\Vehicle\ListVehicleController::DEFAULT_LIMIT_REMOVED_VEHICLES,
            'options' => [10],
        ],
    ],
    'columns' => [
        [
            'title' => 'table.licence-vehicle-list-removed.column.vrm.title',
            'formatter' => 'StackValue',
            'stack' => 'vehicle->vrm',
        ],
        [
            'title' => 'table.licence-vehicle-list-removed.column.weight.title',
            'isNumeric' => true,
            'stack' => 'vehicle->platedWeight',
            'formatter' => 'NumberStackValue',
        ],
        [
            'title' => 'table.licence-vehicle-list-removed.column.specified.title',
            'formatter' => 'Date',
            'name' => 'specifiedDate',
        ],
        [
            'title' => 'table.licence-vehicle-list-removed.column.removed.title',
            'formatter' => 'Date',
            'name' => 'removalDate',
        ],
    ],
];
