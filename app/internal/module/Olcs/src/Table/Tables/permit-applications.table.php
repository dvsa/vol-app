<?php

use Common\Service\Table\Formatter\Date;
use Common\Service\Table\Formatter\InternalLicencePermitReference;
use Common\Service\Table\Formatter\IrhpPermitsRequired;
use Common\Service\Table\Formatter\IrhpPermitTypeWithValidityDate;
use Common\Service\Table\Formatter\RefDataStatus;
use Common\Service\Table\TableBuilder;

return [
    'variables' => [
        'title' => 'Permit Applications',
        'titleSingular' => 'Permit Application',
        'id' => 'permit-applications-table',
        'empty_message' => 'There are no permit application records to display'
    ],
    'settings' => [
        'crud' => [
            'actions' => [
                'Apply' => ['class' => 'govuk-button']
            ],
        ],
    ],
    'columns' => [
        [
            'title' => 'dashboard-table-permit-application-ref',
            'name' => 'id',
            'formatter' => InternalLicencePermitReference::class
        ],
        [
            'title' => 'dashboard-table-permit-application-num',
            'isNumeric' => true,
            'formatter' => IrhpPermitsRequired::class,
            'name' => 'permitsRequired',
        ],
        [
            'title' => 'dashboard-table-permit-application-type',
            'formatter' => IrhpPermitTypeWithValidityDate::class,
            'name' => 'typeDescription',
        ],
        [
            'title' => 'Rec\'d Date',
            'name' => 'dateReceived',
            'formatter' => Date::class
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
    ],
];
