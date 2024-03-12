<?php

use Common\Service\Table\Formatter\IrhpPermitTypeWithValidityDate;
use Common\Service\Table\Formatter\LicencePermitReference;
use Common\Service\Table\Formatter\NullableNumber;
use Common\Service\Table\Formatter\RefDataStatus;

return [
    'variables' => [
        'title' => 'dashboard-table-permit-application-title',
        'empty_message' => 'dashboard-no-permit-text',
        'hide_column_headers' => false,
    ],
    'settings' => [],
    'attributes' => [],
    'columns' => [
        [
            'title' => 'dashboard-table-permit-application-reference',
            'name' => 'id',
            'formatter' => LicencePermitReference::class,
        ],
        [
            'title' => 'dashboard-table-permit-application-num',
            'isNumeric' => true,
            'name' => 'permitsRequired',
            'formatter' => NullableNumber::class
        ],
        [
            'title' => 'dashboard-table-permit-application-type',
            'name' => 'typeDescription',
            'formatter' => IrhpPermitTypeWithValidityDate::class,
        ],
        [
            'title' => 'dashboard-table-permit-application-status',
            'name' => 'status',
            'formatter' => fn($row) => $this->callFormatter(
                [
                    'name' => 'status',
                    'formatter' => RefDataStatus::class,
                ],
                [
                    'status' => [
                        'id' => $row['statusId'],
                        'description' => $row['statusDescription'],
                    ],
                ]
            )
        ]
    ]
];
