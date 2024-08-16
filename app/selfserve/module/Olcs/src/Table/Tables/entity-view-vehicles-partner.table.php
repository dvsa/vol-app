<?php

use Common\Service\Table\Formatter\Date;
use Common\Service\Table\Formatter\StackValueReplacer;
use Common\Service\Table\Formatter\YesNo;

return [
    'variables' => [],
    'settings' => [],
    'attributes' => ['id' => 'vehicles'],
    'columns' => [
        [
            'title' => 'Interim',
            'name' => 'interimApplication',
            'formatter' => YesNo::class,
        ],
        [
            'title' => 'Vehicle registration number',
            'name' => 'vrm',
            'formatter' => function ($data) {
                if (!is_null($data['interimApplication'])) {
                    return $data['vehicle']['vrm'] . ' (interim)';
                }

                return $data['vehicle']['vrm'];
            }
        ],
        [
            'title' => 'Plated weight',
            'isNumeric' => true,
            'stringFormat' => '{vehicle->platedWeight} Kg',
            'formatter' => StackValueReplacer::class
        ],
        [
            'title' => 'Specified date',
            'formatter' => Date::class,
            'name' => 'specifiedDate'
        ],
    ]
];
