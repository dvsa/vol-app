<?php

use Common\Service\Table\Formatter\Date;
use Common\Util\Escape;

return [
    'variables' => [
        'title' => 'Fee Rates',
        'titleSingular' => 'Fee Rate',
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
                ],
            ]
        ],
    ],
    'columns' => [
        [
            'title' => 'ID',
            'isNumeric' => true,
            'name' => 'id',
            'sort' => 'id',
        ],
        [
            'title' => 'Fee Type',
            'name' => 'feeType',
            'sort' => 'ftft.id',
            'formatter' => fn($row, $column) => Escape::html($row['feeType']['id'])
        ],
        [
            'title' => 'Description',
            'name' => 'description',
            'sort' => 'description',
        ],
        [
            'title' => 'Effective from',
            'name' => 'effectiveFrom',
            'sort' => 'effectiveFrom',
            'formatter' => function ($row, $column) {
                $column['formatter'] = Date::class;
                return empty($row['effectiveFrom']) ? 'N/A' : $this->callFormatter($column, $row);
            }
        ],
        [
            'title' => 'Fixed value',
            'isNumeric' => true,
            'name' => 'fixedValue',
            'sort' => 'fixedValue',
        ],
        [
            'title' => 'Annual value',
            'isNumeric' => true,
            'name' => 'annualValue',
            'sort' => 'annualValue',
        ],
        [
            'title' => 'Five year value',
            'isNumeric' => true,
            'name' => 'fiveYearValue',
            'sort' => 'fiveYearValue',
        ],
        [
            'title' => 'markup-table-th-action', //this is a view partial from olcs-common
            'width' => 'checkbox',
            'format' => '{{[elements/radio]}}'
        ],
    ]
];
