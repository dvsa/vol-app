<?php

return [
    'variables' => [
        'title' => 'Permit Ranges',
        'titleSingular' => 'Permit Range',
    ],
    'settings' => [
        'crud' => [
            'actions' => [
                'add' => [
                    'class' => 'govuk-button',
                    'requireRows' => false
                ],
                'edit' => [
                    'requireRows' => false,
                    'class' => 'govuk-button govuk-button--secondary js-require--one'
                ],
                'delete' => [
                    'requireRows' => false,
                    'class' => 'govuk-button govuk-button--secondary js-require--one'
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
            'title' => 'Emissions Question',
            'name' => 'emissionsCategory',
            'formatter' => 'RefData'
        ],
        [
            'title' => 'Type',
            'name' => 'permitRangeType',
            'formatter' => 'IrhpPermitRangeType'
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
            'isNumeric' => true,
            'name' => 'totalPermits',
            'formatter' => 'IrhpPermitRangeTotalPermits'
        ],
        [
            'title' => 'markup-table-th-action', //this is a view partial from olcs-common
            'width' => 'checkbox',
            'format' => '{{[elements/radio]}}'
        ],
    ]
];
