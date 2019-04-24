<?php

return [
    'variables' => [
        'title' => 'Email Templates',
    ],
    'settings' => [
        'paginate' => [
            'limit' => [
                'default' => 10,
                'options' => [10, 25, 50]
            ]
        ],
        'crud' => [
            'actions' => [
                'edit' => [
                    'requireRows' => false,
                    'class' => 'action--secondary js-require--one'
                ]
            ]
        ],
    ],
    'columns' => [
        [
            'title' => 'Subject',
            'name' => 'description',
        ],
        [
            'title' => 'Language',
            'name' => 'language',
            'formatter' => function ($row) {
                return $row['locale'] === 'en_GB' ? 'English' : 'Welsh';
            },
        ],
        [
            'title' => 'Format',
            'name' => 'format',
        ],
        [
            'title' => '',
            'width' => 'checkbox',
            'format' => '{{[elements/radio]}}'
        ],
    ]
];
