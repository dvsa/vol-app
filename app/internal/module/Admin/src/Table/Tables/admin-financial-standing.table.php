<?php

use Common\Service\Table\Formatter\Date;
use Common\Service\Table\Formatter\RefData;

return [
    'variables' => [
        'title' => 'crud-financial-standing-title'
    ],
    'settings' => [
        'crud' => [
            'actions' => [
                'add' => ['class' => 'govuk-button'],
                'edit' => ['class' => 'govuk-button govuk-button--secondary js-require--one'],
                'delete' => ['class' => 'govuk-button govuk-button--warning js-require--multiple']
            ]
        ]
    ],
    'columns' => [
        [
            'title' => 'Operator type',
            'type' => 'Action',
            'action' => 'edit',
            'name' => 'goodsOrPsv',
            'formatter' => RefData::class
        ],
        [
            'title' => 'Licence type',
            'name' => 'licenceType',
            'formatter' => RefData::class
        ],
        [
            'title' => 'Vehicle type',
            'name' => 'vehicleType',
            'formatter' => RefData::class
        ],
        [
            'title' => 'First',
            'isNumeric' => true,
            'name' => 'firstVehicleRate',
        ],
        [
            'title' => 'Additional',
            'isNumeric' => true,
            'name' => 'additionalVehicleRate',
        ],
        [
            'title' => 'Effective',
            'name' => 'effectiveFrom',
            'formatter' => Date::class
        ],
        [
            'title' => 'markup-table-th-action', //this is a view partial from olcs-common
            'width' => 'checkbox',
            'type' => 'Checkbox'
        ],
    ]
];
