<?php

use Common\Service\Table\Formatter\ConstrainedCountriesList;
use Common\Service\Table\Formatter\RefDataStatus;
use Common\Service\Table\Formatter\StackValue;

return [
    'variables' => [
        'title' => 'Permits',
        'titleSingular' => 'Permit',
        'id' => 'permits-table',
        'empty_message' => 'There are no permit records to display'
    ],
    'settings' => [
        'crud' => [
            'actions' => [
                'terminate' => ['requireRows' => true, 'class' => 'govuk-button govuk-button--secondary js-require--one'],
                'request replacement' => ['requireRows' => true, 'class' => 'govuk-button govuk-button--secondary js-require--one']
            ],
        ],
        'paginate' => [
            'limit' => [
                'options' => [10, 25, 50],
            ],
        ],
    ],
    'columns' => [
        [
            'title' => 'Permit No.',
            'isNumeric' => true,
            'name' => 'permitNumber',
        ],
        [
            'title' => 'Minimum emission standard',
            'name' => 'emissionsCategory',
            'stack' => 'irhpPermitRange->emissionsCategory->description',
            'formatter' => StackValue::class,
        ],
        [
            'title' => 'Issued date',
            'name' => 'issueDate',
            'formatter' => \Common\Service\Table\Formatter\DateTime::class,
        ],
        [
            'title' => 'Not valid for travel to',
            'formatter' => ConstrainedCountriesList::class,
        ],
        [
            'title' => 'Ceased Date',
            'name' => 'expiryDate',
            'formatter' => \Common\Service\Table\Formatter\DateTime::class,
        ],
        [
            'title' => 'Replacement',
            'name' => 'replaces',
            'formatter' => function ($row) {
                $val = is_array($row['replaces']) ? 'Yes' : 'No';
                return $val;
            },
        ],
        [
            'title' => 'Status',
            'name' => 'status',
            'formatter' => RefDataStatus::class
        ],
        [
            'title' => 'markup-table-th-action', //this is a view partial from olcs-common
            'width' => 'checkbox',
            'format' => '{{[elements/radio]}}'
        ]
    ],
];
