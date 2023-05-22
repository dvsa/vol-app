<?php

use Common\Util\Escape;

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
            'formatter' => 'IrhpPermitNumberInternal',
        ],
        [
            'title' => 'Minimum emission standard',
            'name' => 'emissionsCategory',
            'formatter' => function ($row) {
                return Escape::html($row['irhpPermitRange']['emissionsCategory']['description']);
            },
        ],
        [
            'title' => 'Not valid to travel to',
            'name' => 'constrainedCountries',
            'formatter' => 'ConstrainedCountriesList',
        ],
        [
            'title' => 'Issued date',
            'name' => 'issueDate',
            'formatter' => 'DateTime',
        ],
        [
            'title' => 'Type',
            'name' => 'type',
            'formatter' => function ($row) {
                return $this->callFormatter(
                    [
                        'name' => 'irhpPermitRangeType',
                        'formatter' => 'IrhpPermitRangeType',
                    ],
                    $row['irhpPermitRange']
                );
            },
        ],
        [
            'title' => 'Country',
            'name' => 'country',
            'formatter' => function ($row) {
                return Escape::html($row['irhpPermitRange']['irhpPermitStock']['country']['countryDesc']);
            },
        ],
        [
            'title' => 'Ceased Date',
            'name' => 'ceasedDate',
            'formatter' => 'DateTime',
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
            'formatter' => 'RefDataStatus'
        ],
        [
            'title' => 'markup-table-th-action', //this is a view partial from olcs-common
            'width' => 'checkbox',
            'format' => '{{[elements/radio]}}'
        ]
    ],
];
