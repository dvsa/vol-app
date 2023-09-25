<?php

use Common\Service\Table\Formatter\Date;
use Common\Service\Table\Formatter\NumberStackValue;
use Common\Service\Table\Formatter\VehicleDiscNo;
use Common\Service\Table\Formatter\VehicleLink;
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
            'formatter' => VehicleLink::class,
            'sort' => 'v.vrm'
        ],
        [
            'title' => $translationPrefix . '.weight',
            'isNumeric' => true,
            'stack' => 'vehicle->platedWeight',
            'formatter' => NumberStackValue::class,
        ],
        [
            'title' => $translationPrefix . '.specified',
            'formatter' => Date::class,
            'name' => 'specifiedDate',
            'sort' => 'specifiedDate'
        ],
        [
            'title' => $translationPrefix . '.disc-no',
            'isNumeric' => true,
            'name' => 'discNo',
            'formatter' => VehicleDiscNo::class
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
