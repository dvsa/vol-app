<?php

use Common\Util\Escape;

return [
    'variables' => [
        'title' => 'Pre-Grant Candidate Permits',
        'id' => 'candidate-permits',
        'empty_message' => 'There are no permit records to display'
    ],
    'settings' => [
        'crud' => [
            'actions' => [
                'add' => ['requireRows' => false, 'class' => 'action--primary'],
                'delete' => ['requireRows' => true, 'class' => 'action--secondary js-require--one'],
                'edit' => ['requireRows' => true, 'class' => 'action--secondary js-require--one']
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
