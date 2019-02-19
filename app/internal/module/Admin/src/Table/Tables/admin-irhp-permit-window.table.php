<?php

return [
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
            'formatter' => 'DateTimezoneFix'
        ],
        [
            'title' => 'Window End Date',
            'name' => 'endDate',
            'formatter' => 'DateTimezoneFix'
        ],
        [
            'title' => 'Emissions Question',
            'name' => 'emissionsCategory',
            'formatter' => 'RefData'
        ],
        [
            'title' => '',
            'width' => 'checkbox',
            'format' => '{{[elements/radio]}}'
        ],
    ]
];
