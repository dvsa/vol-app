<?php

return [
    'variables' => [
        'title' => 'Permit Stock Window'
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
            'formatter' => 'Date'
        ],
        [
            'title' => 'Window End Date',
            'name' => 'endDate',
            'formatter' => 'Date'
        ],
        [
            'title' => '',
            'width' => 'checkbox',
            'format' => '{{[elements/radio]}}'
        ],
    ]
];
