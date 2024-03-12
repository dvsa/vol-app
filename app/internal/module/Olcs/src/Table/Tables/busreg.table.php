<?php

use Common\Service\Table\Formatter\BusRegNumberLink;
use Common\Service\Table\Formatter\Date;

return [
    'variables' => [
        'titleSingular' => 'Bus registration',
        'title' => 'Bus registrations'
    ],
    'settings' => [
        'crud' => [
            'actions' => [
                'add' => ['class' => 'govuk-button'],
                'edit' => ['requireRows' => true, 'class' => 'govuk-button govuk-button--secondary js-require--one']
            ]
        ],
        'paginate' => [
            'limit' => [
                'default' => 10,
                'options' => [10, 25, 50, 100]
            ]
        ]
    ],
    'columns' => [
        [
            'title' => 'Reg No.',
            'formatter' => BusRegNumberLink::class,
            'sort' => 'routeNo',
        ],
        [
            'title' => 'Var No.',
            'isNumeric' => true,
            'name' => 'variationNo',
            'sort' => 'variationNo'
        ],
        [
            'title' => 'Service No.',
            'isNumeric' => true, //mostly numeric so using the style
            'name' => 'serviceNo',
            'sort' => 'serviceNo'
        ],
        [
            'title' => '1st registered / cancelled',
            'formatter' => Date::class,
            'name' => 'date1stReg'
        ],
        [
            'title' => 'Starting point',
            'name' => 'startPoint',
            'sort' => 'startPoint'
        ],
        [
            'title' => 'Finishing point',
            'name' => 'finishPoint',
            'sort' => 'finishPoint'
        ],
        [
            'title' => 'Status',
            'name' => 'busRegStatusDesc',
            'sort' => 'busRegStatusDesc',
        ],
        [
            'title' => 'markup-table-th-action', //this is a view partial from olcs-common
            'width' => 'checkbox',
            'format' => '{{[elements/radio]}}'
        ]
    ]
];
