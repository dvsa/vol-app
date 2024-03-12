<?php

use Common\Service\Table\Formatter\Date;
use Common\Service\Table\Formatter\IrhpPermitRangeType;
use Common\Service\Table\Formatter\NullableNumber;
use Common\Service\Table\Formatter\RefDataStatus;
use Common\Service\Table\Formatter\StackValue;
use Common\Util\Escape;
use Common\RefData;

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
            'title' => 'permits.irhp.valid.permits.table.application-no',
            'isNumeric' => true,
            'name' => 'irhpApplication',
            'stack' => 'irhpPermitApplication->relatedApplication->id',
            'formatter' => StackValue::class,
        ],
        [
            'title' => 'permits.irhp.valid.permits.table.permit-no',
            'isNumeric' => true,
            'name' => 'permitNumber',
            'formatter' => NullableNumber::class,
        ],
        [
            'title' => 'permits.irhp.valid.permits.table.country',
            'name' => 'country',
            'formatter' => fn($row, $column) => Escape::html(
                $this->translator->translate($row['irhpPermitRange']['irhpPermitStock']['country']['countryDesc'])
            ),
        ],
        [
            'title' => 'permits.irhp.valid.permits.table.type',
            'name' => 'type',
            'formatter' => fn($row) => $this->callFormatter(
                [
                    'name' => 'irhpPermitRangeType',
                    'formatter' => IrhpPermitRangeType::class,
                ],
                $row['irhpPermitRange']
            )
        ],
        [
            'title' => 'permits.irhp.valid.permits.table.issued-date',
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
            'name' => 'ceasedDate',
            'formatter' => Date::class,
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
