<?php

use Common\Service\Table\Formatter\Date;
use Common\Service\Table\Formatter\NumberStackValue;
use Common\Service\Table\Formatter\VehicleDiscNo;
use Common\Service\Table\Formatter\VehicleLink;

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
            'formatter' => VehicleLink::class,
            'sort' => 'v.vrm',
        ],
        [
            'title' => 'table.licence-vehicle-list-current.column.weight.title',
            'isNumeric' => true,
            'stack' => 'vehicle->platedWeight',
            'formatter' => NumberStackValue::class,
        ],
        [
            'title' => 'table.licence-vehicle-list-current.column.specified.title',
            'formatter' => Date::class,
            'name' => 'specifiedDate',
            'sort' => 'specifiedDate',
        ],
        [
            'title' => 'table.licence-vehicle-list-current.column.disc_no.title',
            'isNumeric' => true,
            'name' => 'discNo',
            'formatter' => VehicleDiscNo::class
        ],
    ],
];
