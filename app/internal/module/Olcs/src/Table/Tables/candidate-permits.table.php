<?php

use Common\Util\Escape;

return [
    'variables' => [
        'title' => 'Permits',
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
            'formatter' => function ($row) {
                return 'Not known';
            },
        ],
        [
            'title' => 'Issued date',
        ],
        [
            'title' => 'Not valid for travel to',
            'formatter' => function ($row) {
                $c = [];
                foreach ($row['constrainedCountries'] as $country) {
                    $c[] = Escape::html($country['countryDesc']);
                }
                $return = empty($c) ? 'No exclusions' : implode(', ', $c);
                return $return;
            },
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
        ]
    ],
];
