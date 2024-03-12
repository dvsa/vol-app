<?php

use Common\Service\Table\Formatter\Address;
use Common\Service\Table\Formatter\OcConditions;
use Common\Service\Table\Formatter\OcUndertakings;

return [
    'variables' => [
        'title' => 'schedule41.operating-centre.table.title',
        'empty_message' => 'schedule41.operating-centre.table.empty',
        'within_form' => true,
    ],
    'settings' => [
        'crud' => [
            'actions' => []
        ]
    ],
    'attributes' => [],
    'columns' => [
        [
            'title' => 'schedule41.operating-centre.table.address',
            'formatter' => Address::class,
            'name' => 'address'
        ],
        [
            'title' => 'schedule41.operating-centre.table.vehicles',
            'isNumeric' => true,
            'name' => 'noOfVehiclesRequired'
        ],
        [
            'title' => 'schedule41.operating-centre.table.trailers',
            'isNumeric' => true,
            'name' => 'noOfTrailersRequired'
        ],
        [
            'title' => 'schedule41.operating-centre.table.conditions',
            'isNumeric' => true,
            'name' => 'noOfConditions',
            'formatter' => OcConditions::class
        ],
        [
            'title' => 'schedule41.operating-centre.table.undertakings',
            'isNumeric' => true,
            'name' => 'noOfUndertakings',
            'formatter' => OcUndertakings::class
        ],
        [
            'width' => 'checkbox',
            'type' => 'Checkbox'
        ]
    ]
];
