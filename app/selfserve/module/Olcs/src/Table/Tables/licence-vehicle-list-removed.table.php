<?php

use Common\Service\Table\Formatter\Date;
use Common\Service\Table\Formatter\NumberStackValue;
use Common\Service\Table\Formatter\StackValue;

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
            'formatter' => StackValue::class,
            'stack' => 'vehicle->vrm',
        ],
        [
            'title' => 'table.licence-vehicle-list-removed.column.weight.title',
            'isNumeric' => true,
            'stack' => 'vehicle->platedWeight',
            'formatter' => NumberStackValue::class,
        ],
        [
            'title' => 'table.licence-vehicle-list-removed.column.specified.title',
            'formatter' => Date::class,
            'name' => 'specifiedDate',
        ],
        [
            'title' => 'table.licence-vehicle-list-removed.column.removed.title',
            'formatter' => Date::class,
            'name' => 'removalDate',
        ],
    ],
];
