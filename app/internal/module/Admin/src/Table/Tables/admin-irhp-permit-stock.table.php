<?php

use Common\Service\Table\Formatter\IrhpPermitStockCountry;
use Common\Service\Table\Formatter\IrhpPermitStockType;
use Common\Service\Table\Formatter\IrhpPermitStockValidity;

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
            'title' => 'Type',
            'name' => 'irhpPermitTypeId',
            'formatter' => IrhpPermitStockType::class
        ],
        [
            'title' => 'Country',
            'name' => 'country',
            'formatter' => IrhpPermitStockCountry::class
        ],
        [
            'title' => 'Validity Period',
            'name' => 'validFrom',
            'formatter' => IrhpPermitStockValidity::class
        ],
        [
            'title' => 'Quota',
            'isNumeric' => true,
            'name' => 'initialStock',
        ],
        [
            'title' => 'SS Visibility',
            'name' => 'hiddenSS',
            'formatter' => fn($row) => $row['hiddenSs'] ? 'Hidden' : 'Visible',
        ],
        [
            'title' => 'markup-table-th-action', //this is a view partial from olcs-common
            'width' => 'checkbox',
            'format' => '{{[elements/radio]}}'
        ],
    ]
];
