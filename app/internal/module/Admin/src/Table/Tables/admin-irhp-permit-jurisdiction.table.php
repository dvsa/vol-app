<?php

use Common\Service\Table\Formatter\IrhpPermitJurisdictionPermitNumber;
use Common\Service\Table\Formatter\IrhpPermitJurisdictionTrafficArea;

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
            'formatter' => IrhpPermitJurisdictionTrafficArea::class
        ],
        [
            'title' => 'Quantity of permits',
            'isNumeric' => true,
            'name' => 'permitNumber',
            'formatter' => IrhpPermitJurisdictionPermitNumber::class
        ],
    ]
];
