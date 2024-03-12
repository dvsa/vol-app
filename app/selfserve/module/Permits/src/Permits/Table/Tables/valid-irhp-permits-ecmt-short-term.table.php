<?php

use Common\RefData;
use Common\Service\Table\Formatter\ConstrainedCountriesList;
use Common\Service\Table\Formatter\Date;
use Common\Service\Table\Formatter\RefDataStatus;
use Common\Service\Table\Formatter\StackValue;
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
            'title' => 'permits.irhp.valid.permits.table.emissions-standard',
            'name' => 'emissionsCategory',
            'stack' => 'irhpPermitRange->emissionsCategory->description',
            'formatter' => StackValue::class,
        ],
        [
            'title' => 'permits.irhp.valid.permits.table.constrained.countries',
            'name' => 'constrainedCountries',
            'formatter' => ConstrainedCountriesList::class,
        ],
        [
            'title' => 'permits.irhp.valid.permits.table.issue-date',
            'name' => 'issueDate',
            'formatter' => Date::class,
        ],
        [
            'title' => 'permits.irhp.valid.permits.table.use-by-date',
            'name' => 'useByDate',
            'formatter' => fn($row) => $this->callFormatter(
                [
                    'name' => 'useByDate',
                    'formatter' => Date::class,
                ],
                [
                    'useByDate' => $row['ceasedDate'],
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
