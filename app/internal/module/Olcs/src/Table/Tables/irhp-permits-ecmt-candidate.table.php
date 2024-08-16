<?php

use Common\Service\Table\Formatter\ConstrainedCountriesList;

return [
    'variables' => [
        'title' => 'Permits',
        'titleSingular' => 'Permit',
        'id' => 'candidate-permits',
        'empty_message' => 'There are no permit records to display'
    ],
    'settings' => [
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
        ]
    ],
];
