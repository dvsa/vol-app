<?php

use Common\Service\Table\Formatter\ConstrainedCountriesList;
use Common\Util\Escape;

return [
    'variables' => [
        'title' => 'Pre-Grant Candidate Permits',
        'titleSingular' => 'Pre-Grant Candidate Permit',
        'id' => 'candidate-permits',
        'empty_message' => 'There are no permit records to display'
    ],
    'settings' => [
        'crud' => [
            'actions' => [
                'preGrantAdd' => ['requireRows' => false, 'class' => 'govuk-button', 'label' => 'Add', 'value' => 'preGrantAdd'],
                'preGrantDelete' => ['requireRows' => true, 'class' => 'govuk-button govuk-button--warning js-require--one', 'label' => 'Delete', 'value' => 'preGrantDelete'],
                'preGrantEdit' => ['requireRows' => true, 'class' => 'govuk-button govuk-button--secondary js-require--one', 'label' => 'Edit', 'value' => 'preGrantEdit']
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
            'name' => 'permitNumber',
            'formatter' => fn($row) => 'Not known',
        ],
        [
            'title' => 'Emissions',
            'name' => 'emissions',
            'formatter' => fn($row) => Escape::html($row['assignedEmissionsCategory']['description']),
        ],
        [
            'title' => 'Issued date',
        ],
        [
            'title' => 'Not valid for travel to',
            'formatter' => ConstrainedCountriesList::class
        ],
        [
            'title' => 'Ceased Date',
        ],
        [
            'title' => 'Replacement',
            'name' => 'successful',
            'formatter' => fn() => 'No',
        ],
        [
            'title' => 'Status',
            'name' => 'version',
            'formatter' => fn() => 'Pending',
        ],
        [
            'title' => 'markup-table-th-action', //this is a view partial from olcs-common
            'width' => 'checkbox',
            'format' => '{{[elements/radio]}}'
        ]
    ],
];
