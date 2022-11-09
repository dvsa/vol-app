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
                    'class' => 'action--primary',
                    'requireRows' => false
                ],
                'cancel' => [
                    'class' => 'action--secondary',
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
