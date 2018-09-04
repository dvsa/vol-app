<?php

return [
    'variables' => [
        'title' => ''
    ],
    'settings' => [
        'crud' => [
            'actions' => [
                'add' => [
                    'class' => 'action--primary',
                    'requireRows' => false
                ],
                'edit' => [
                    'requireRows' => false,
                    'class' => 'action--secondary js-require--one'
                ],
                'delete' => [
                    'requireRows' => false,
                    'class' => 'action--secondary js-require--one'
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
            'title' => 'Permit numbers',
            'name' => 'permitNumbers',
            'formatter' => 'IrhpPermitRangePermitNumber'
        ],
        [
            'title' => 'Restricted countries',
            'name' => 'restrictedCountries',
            'formatter' => 'IrhpPermitRangeRestrictedCountries'
        ],
        [
            'title' => 'Minister of state reserve',
            'name' => 'ssReserve',
            'formatter' => 'IrhpPermitRangeReserve'
        ],
        [
            'title' => 'Replacement stock',
            'name' => 'lostReplacement',
            'formatter' => 'IrhpPermitRangeReplacement'
        ],
        [
            'title' => 'Total permits',
            'name' => 'totalPermits',
            'formatter' => 'IrhpPermitRangeTotalPermits'
        ],
        [
            'title' => '',
            'width' => 'checkbox',
            'format' => '{{[elements/radio]}}'
        ],
    ]
];
