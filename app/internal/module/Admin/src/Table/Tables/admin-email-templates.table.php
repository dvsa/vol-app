<?php

return [
    'variables' => [
        'title' => 'Email Templates',
        'titleSingular' => 'Email Templates',
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
                    'class' => 'govuk-button govuk-button--secondary js-require--one'
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
            'formatter' => fn($row) => $row['locale'] === 'en_GB' ? 'English' : 'Welsh',
        ],
        [
            'title' => 'Format',
            'name' => 'format',
        ],
        [
            'title' => 'markup-table-th-action', //this is a view partial from olcs-common
            'width' => 'checkbox',
            'format' => '{{[elements/radio]}}'
        ],
    ]
];
