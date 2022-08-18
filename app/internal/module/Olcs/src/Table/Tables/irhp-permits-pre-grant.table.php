<?php

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
                'preGrantAdd' => ['requireRows' => false, 'class' => 'action--primary', 'label' => 'Add', 'value' => 'preGrantAdd'],
                'preGrantDelete' => ['requireRows' => true, 'class' => 'action--secondary js-require--one', 'label' => 'Delete', 'value' => 'preGrantDelete'],
                'preGrantEdit' => ['requireRows' => true, 'class' => 'action--secondary js-require--one', 'label' => 'Edit', 'value' => 'preGrantEdit']
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
            'formatter' => function ($row) {
                return 'Not known';
            },
        ],
        [
            'title' => 'Emissions',
            'name' => 'emissions',
            'formatter' => function ($row) {
                return Escape::html($row['assignedEmissionsCategory']['description']);
            },
        ],
        [
            'title' => 'Issued date',
        ],
        [
            'title' => 'Not valid for travel to',
            'formatter' => 'ConstrainedCountriesList'
        ],
        [
            'title' => 'Ceased Date',
        ],
        [
            'title' => 'Replacement',
            'name' => 'successful',
            'formatter' => function () {
                return 'No';
            },
        ],
        [
            'title' => 'Status',
            'name' => 'version',
            'formatter' => function () {
                return 'Pending';
            },
        ],
        [
            'title' => '',
            'width' => 'checkbox',
            'format' => '{{[elements/radio]}}'
        ]
    ],
];
