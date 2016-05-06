<?php

return [
    'variables' => [
        'title' => 'System messages',
        'titleSingular' => 'System message',
        'empty_message' => 'You don\'t have messages',
    ],
    'settings' => [
        'crud' => [
            'actions' => [
                'add' => [
                    'class' => 'primary',
                    'requireRows' => false,
                ],
                'edit' => [
                    'requireRows' => true,
                    'class' => 'secondary js-require--one',
                ],
                'delete' => [
                    'requireRows' => true,
                    'class' => 'secondary js-require--one',
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
            'title' => 'Message',
            'sort' => 'description',
            'action' => 'edit',
            'formatter' => 'SystemInfoMessageLink',
        ],
        [
            'title' => 'Displayed to',
            'sort' => 'isInternal',
            'formatter' => function ($row) {
                return ($row['isInternal']
                    ? 'Internal'
                    : 'Self serve'
                );
            }
        ],
        [
            'title' => 'Start',
            'name' => 'startDate',
            'sort' => 'startDate',
            'formatter' => 'DateTime',
            'dateformat' => 'H:i d/m/Y',
        ],
        [
            'title' => 'End',
            'name' => 'endDate',
            'sort' => 'endDate',
            'formatter' => 'DateTime',
            'dateformat' => 'H:i d/m/Y',
        ],
        [
            'type' => 'Action',
            'action' => 'delete',
            'text' => 'Remove',
        ],
    ],
];
