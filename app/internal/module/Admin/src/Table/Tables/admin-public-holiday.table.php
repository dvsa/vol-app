<?php

return [
    'variables' => [
        'title' => 'Public holidays',
        'titleSingular' => 'Public holiday',
        'empty_message' => 'You don\'t have public holidays',
    ],
    'settings' => [
        'crud' => [
            'actions' => [
                'add' => [
                    'class' => 'primary',
                    'requireRows' => false,
                    'label' => 'Add holiday',
                ],
            ],
        ],
        'paginate' => [
            'limit' => [
                'default' => 25,
                'options' => [10, 25, 50],
            ],
        ],
    ],
    'columns' => [
        [
            'type' => 'Action',
            'action' => 'edit',
            'title' => 'Date',
            'name' => 'publicHolidayDate',
            'sort' => 'publicHolidayDate',
            'formatter' => 'Date',
        ],
        [
            'title' => 'Area',
            'formatter' => 'PublicHolidayArea',
        ],
        [
            'type' => 'Action',
            'action' => 'delete',
            'text' => 'Remove',
        ],
    ],
];
