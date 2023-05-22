<?php

return [
    'variables' => [
        'title' => 'Jurisdictions',
        'titleSingular' => 'Jurisdiction',
    ],
    'settings' => [
        'crud' => [
            'actions' => [
                'save' => [
                    'class' => 'govuk-button',
                    'requireRows' => false
                ],
                'cancel' => [
                    'class' => 'govuk-button govuk-button--secondary',
                    'requireRows' => false
                ]
            ]
        ],
    ],
    'columns' => [
        [
            'title' => 'Devolved administrations',
            'name' => 'trafficArea',
            'formatter' => 'IrhpPermitJurisdictionTrafficArea'
        ],
        [
            'title' => 'Quantity of permits',
            'isNumeric' => true,
            'name' => 'permitNumber',
            'formatter' => 'IrhpPermitJurisdictionPermitNumber'
        ],
    ]
];
