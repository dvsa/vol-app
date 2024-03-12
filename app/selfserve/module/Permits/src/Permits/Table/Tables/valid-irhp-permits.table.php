<?php

use Common\RefData;
use Common\Service\Table\Formatter\Date;
use Common\Service\Table\Formatter\NullableNumber;
use Common\Service\Table\Formatter\RefDataStatus;
use Common\Service\Table\Formatter\StackValue;

return [
    'variables' => [],
    'settings' => [
        'paginate' => [
            'limit' => [
                'default' => 10,
                'options' => [10, 25, 50]
            ],
        ],
    ],
    'attributes' => [],
    'columns' => [
        [
            'title' => 'permits.irhp.valid.permits.table.permit-no',
            'isNumeric' => true,
            'name' => 'permitNumber',
            'formatter' => NullableNumber::class,
        ],
        [
            'title' => 'permits.irhp.valid.permits.table.application-no',
            'name' => 'irhpApplication',
            'stack' => 'irhpPermitApplication->relatedApplication->id',
            'formatter' => StackValue::class,
        ],
        [
            'title' => 'permits.irhp.valid.permits.table.issue-date',
            'name' => 'issueDate',
            'formatter' => Date::class,
        ],
        [
            'title' => 'permits.irhp.valid.permits.table.start-date',
            'name' => 'startDate',
            'formatter' => Date::class,
        ],
        [
            'title' => 'permits.irhp.valid.permits.table.expiry-date',
            'name' => 'expiryDate',
            'formatter' => fn($row) => $this->callFormatter(
                [
                    'name' => 'useByDate',
                    'formatter' => Date::class,
                ],
                [
                    'useByDate' => $row['irhpPermitRange']['irhpPermitStock']['validTo'] ?? null,
                ]
            )
        ],
        [
            'title' => 'status',
            'name' => 'status',
            'formatter' => fn($row) => $this->callFormatter(
                [
                    'name' => 'status',
                    'formatter' => RefDataStatus::class,
                ],
                [
                    'status' => [
                        'id' => RefData::PERMIT_VALID,
                        'description' => RefData::PERMIT_VALID
                    ],
                ]
            )
        ],
    ]
];
