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
                    'class' => 'govuk-button',
                    'requireRows' => false
                ],
                'edit' => [
                    'requireRows' => true,
                    'class' => 'govuk-button govuk-button--secondary js-require--one'
                ],
                'delete' => [
                    'requireRows' => true,
                    'class' => 'govuk-button govuk-button--warning js-require--one'
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
            'formatter' => \Common\Service\Table\Formatter\DateTime::class
        ],
        [
            'title' => 'Window End Date',
            'name' => 'endDate',
            'formatter' => \Common\Service\Table\Formatter\DateTime::class
        ],
        [
            'title' => 'markup-table-th-action', //this is a view partial from olcs-common
            'width' => 'checkbox',
            'format' => '{{[elements/radio]}}'
        ],
    ]
];
