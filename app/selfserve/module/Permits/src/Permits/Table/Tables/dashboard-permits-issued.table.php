<?php

use Common\Service\Table\Formatter\LicencePermitReference;
use Common\Service\Table\Formatter\NullableNumber;
use Common\Service\Table\Formatter\RefDataStatus;
use Common\Service\Table\TableBuilder;

return [
    'variables' => [
        'title' => 'dashboard-table-permits-title',
        'empty_message' => 'dashboard-no-permit-text',
        'hide_column_headers' => false,
    ],
    'settings' => [],
    'attributes' => [],
    'columns' => [
        [
            'title' => 'dashboard-table-permit-licence-number',
            'name' => 'id',
            'formatter' => LicencePermitReference::class,
        ],
        [
            'title' => 'dashboard-table-permit-application-num',
            'isNumeric' => true,
            'name' => 'validPermitCount',
            'formatter' => NullableNumber::class
        ],
        [
            'title' => 'dashboard-table-permit-application-type',
            'name' => 'typeDescription',
        ],
        [
            'title' => 'dashboard-table-permit-application-status',
            'name' => 'status',
            'formatter' => fn($row) =>
                /**
                 * @var TableBuilder $this
                 * @psalm-scope-this TableBuilder
                 */
                $this->callFormatter(
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
