<?php

use Common\RefData;
use Common\Service\Table\Formatter\Date;
use Common\Service\Table\Formatter\RefDataStatus;
use Common\Service\Table\Formatter\StackValue;
use Common\Service\Table\TableBuilder;
use Common\Util\Escape;

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
            'formatter' => fn($row) => '<b>' . Escape::html($row['permitNumber']) . '</b>',
        ],
        [
            'title' => 'permits.irhp.valid.permits.table.application-no',
            'isNumeric' => true,
            'name' => 'irhpApplication',
            'stack' => 'irhpPermitApplication->relatedApplication->id',
            'formatter' => StackValue::class,
        ],
        [
            'title' => 'permits.ecmt.page.valid.tableheader.countries',
            'name' => 'countries',
            'formatter' => fn($row) => 'Cyprus'
        ],
        [
            'title' => 'permits.irhp.valid.permits.table.start-date',
            'name' => 'issueDate',
            'formatter' => Date::class,
            ],
            [
            'title' => 'permits.irhp.valid.permits.table.expiry-date',
            'name' => 'expiryDate',
            'formatter' => Date::class,
            ],
            [
            'title' => 'status',
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
                        'id' => RefData::PERMIT_VALID,
                        'description' => RefData::PERMIT_VALID
                    ],
                ]
            )
            ],
    ]
];
