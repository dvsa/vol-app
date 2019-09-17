<?php

use Common\Util\Escape;

return [
    'variables' => [
        'title' => 'Fee Rates',
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
                ],
            ]
        ],
    ],
    'columns' => [
        [
            'title' => 'ID',
            'name' => 'id',
            'sort' => 'id',
        ],
        [
            'title' => 'Fee Type',
            'name' => 'feeType',
            'sort' => 'ftft.id',
            'formatter' => function ($row, $column) {
                return Escape::html($row['feeType']['id']);
            }
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
                $column['formatter'] = 'Date';
                return empty($row['effectiveFrom']) ? 'N/A' : $this->callFormatter($column, $row);
            }
        ],
        [
            'title' => 'Fixed value',
            'name' => 'fixedValue',
            'sort' => 'fixedValue',
        ],
        [
            'title' => 'Annual value',
            'name' => 'annualValue',
            'sort' => 'annualValue',
        ],
        [
            'title' => 'Five year value',
            'name' => 'fiveYearValue',
            'sort' => 'fiveYearValue',
        ],
        [
            'title' => '',
            'width' => 'checkbox',
            'format' => '{{[elements/radio]}}'
        ],
    ]
];
