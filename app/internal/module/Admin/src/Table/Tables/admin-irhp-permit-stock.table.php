<?php

return [
    'variables' => [
        'title' => 'Permit Stocks',
        'titleSingular' => 'Permit Stock',
    ],
    'settings' => [
        'crud' => [
            'actions' => [
                'add' => [
                    'class' => 'govuk-button',
                    'requireRows' => false
                ],
                'edit' => [
                    'requireRows' => false,
                    'class' => 'govuk-button govuk-button--secondary js-require--one'
                ],
                'delete' => [
                    'requireRows' => false,
                    'class' => 'govuk-button govuk-button--secondary js-require--one'
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
            'title' => 'Type',
            'name' => 'irhpPermitTypeId',
            'formatter' => 'IrhpPermitStockType'
        ],
        [
            'title' => 'Country',
            'name' => 'country',
            'formatter' => 'IrhpPermitStockCountry'
        ],
        [
            'title' => 'Validity Period',
            'name' => 'validFrom',
            'formatter' => 'IrhpPermitStockValidity'
        ],
        [
            'title' => 'Quota',
            'isNumeric' => true,
            'name' => 'initialStock',
        ],
        [
            'title' => 'SS Visibility',
            'name' => 'hiddenSS',
            'formatter' => function ($row) {
                return $row['hiddenSs'] ? 'Hidden' : 'Visible';
            },
        ],
        [
            'title' => 'markup-table-th-action', //this is a view partial from olcs-common
            'width' => 'checkbox',
            'format' => '{{[elements/radio]}}'
        ],
    ]
];
