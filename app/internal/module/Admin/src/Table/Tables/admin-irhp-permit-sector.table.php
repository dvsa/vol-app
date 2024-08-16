<?php

use Common\Service\Table\Formatter\IrhpPermitSectorName;
use Common\Service\Table\Formatter\IrhpPermitSectorQuota;

return [
    'variables' => [
        'title' => 'Permit Sectors',
        'titleSingular' => 'Permit Sector',
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
            'title' => 'Sector Name',
            'name' => 'sectorId',
            'formatter' => IrhpPermitSectorName::class
        ],
        [
            'title' => 'Quantity of permits',
            'isNumeric' => true,
            'name' => 'quotaNumber',
            'formatter' => IrhpPermitSectorQuota::class
        ],
    ]
];
