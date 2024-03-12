<?php

use Common\Service\Table\Formatter\StackValue;
use Common\Service\Table\Formatter\UnlicensedVehicleWeight;

$translationPrefix = 'internal-operator-unlicensed-vehicles.table';

return [
    'variables' => [
        'title' => 'Vehicles',
        'titleSingular' => 'Vehicle',
    ],
    'settings' => [
        'crud' => [
            'actions' => [
                'add' => ['class' => 'govuk-button'],
                'edit' => ['requireRows' => true, 'class' => 'govuk-button govuk-button--secondary js-require--one'],
                'delete' => ['requireRows' => true, 'class' => 'govuk-button govuk-button--warning js-require--one']
            ]
        ],
        'paginate' => [
            'limit' => [
                'default' => 10,
                'options' => [10, 25, 50, 100]
            ]
        ],
        'useQuery' => true
    ],
    'columns' => [
        [
            'title' => $translationPrefix . '.vrm',
            'stack' => 'vehicle->vrm',
            'formatter' => StackValue::class,
            'action' => 'edit',
            'type' => 'Action',
        ],
        [
            'title' => $translationPrefix . '.weight',
            'isNumeric' => true,
            'stack' => 'vehicle->platedWeight',
            'formatter' => UnlicensedVehicleWeight::class,
            'name' => 'weight',
        ],
        [
            'title' => 'markup-table-th-action', //this is a view partial from olcs-common
            'width' => 'checkbox',
            'format' => '{{[elements/checkbox]}}'
        ],
    ]
];
