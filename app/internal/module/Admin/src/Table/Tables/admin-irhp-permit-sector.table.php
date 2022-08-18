<?php

return [
    'variables' => [
        'title' => 'Permit Sectors',
        'titleSingular' => 'Permit Sector',
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
            'title' => 'Sector Name',
            'name' => 'sectorId',
            'formatter' => 'IrhpPermitSectorName'
        ],
        [
            'title' => 'Quantity of permits',
            'isNumeric' => true,
            'name' => 'quotaNumber',
            'formatter' => 'IrhpPermitSectorQuota'
        ],
    ]
];
