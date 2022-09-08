<?php

return [
    'variables' => [
        'title' => 'Permit Windows',
        'titleSingular' => 'Permit Window',
    ],
    'settings' => [
        'crud' => [
            'actions' => [
                'add' => [
                    'class' => 'action--primary',
                    'requireRows' => false
                ],
                'edit' => [
                    'requireRows' => true,
                    'class' => 'action--secondary js-require--one'
                ],
                'delete' => [
                    'requireRows' => true,
                    'class' => 'action--secondary js-require--one'
                ]
            ]
        ],
        'paginate' => [
            'limit' => [
                'default' => 25,
                'options' => [10, 25, 50]
            ],
        ]
    ],
    'attributes' => [
    ],
    'columns' => [
        [
            'title' => 'Window Start Date',
            'name' => 'startDate',
            'formatter' => 'DateTime'
        ],
        [
            'title' => 'Window End Date',
            'name' => 'endDate',
            'formatter' => 'DateTime'
        ],
        [
            'title' => 'markup-table-th-action', //this is a view partial from olcs-common
            'width' => 'checkbox',
            'format' => '{{[elements/radio]}}'
        ],
    ]
];
