<?php

use Common\RefData;
use Common\Service\Table\Formatter\LicenceTypeShort;
use Common\Service\Table\Formatter\StackValue;
use Common\Service\Table\Formatter\StackValueReplacer;

return [
    'variables' => [
        'title' => 'Continuations',
        'titleSingular' => 'Continuation',
    ],
    'settings' => [
        'crud' => [
            'actions' => [
                'generate' => [
                    'label' => 'Generate',
                    'class' => 'govuk-button js-require--multiple',
                    'requireRows' => true
                ],
            ]
        ],
        'row-disabled-callback' => function ($row) {
            $enabledLicenceStatuses = [
                RefData::LICENCE_STATUS_VALID,
                RefData::LICENCE_STATUS_CURTAILED,
                RefData::LICENCE_STATUS_SUSPENDED
            ];

            $enabledStatuses = [
                RefData::CONTINUATION_DETAIL_STATUS_PREPARED,
                RefData::CONTINUATION_DETAIL_STATUS_PRINTING,
                RefData::CONTINUATION_DETAIL_STATUS_PRINTED,
                RefData::CONTINUATION_DETAIL_STATUS_ERROR
            ];

            return !(
                in_array($row['licence']['status']['id'], $enabledLicenceStatuses)
                && in_array($row['status']['id'], $enabledStatuses)
            );
        }
    ],
    'columns' => [
        [
            'title' => 'Operator name',
            'stack' => ['licence', 'organisation', 'name'],
            'formatter' => StackValue::class
        ],
        [
            'title' => 'Licence',
            'stringFormat' => '<a class="govuk-link" href="[LINK]">{licence->licNo}</a> ({licence->status->description})',
            'formatter' => StackValueReplacer::class,
            'type' => 'Link',
            'route' => 'lva-licence',
            'params' => [
                'licence' => '{licence->id}'
            ]
        ],
        [
            'title' => 'Licence type',
            'formatter' => LicenceTypeShort::class
        ],
        [
            'title' => 'Method',
            'formatter' => fn($data) => $data['licence']['organisation']['allowEmail'] === 'Y' ? 'Email' : 'Post'
        ],
        [
            'title' => 'Status',
            'formatter' => \Common\Service\Table\Formatter\RefData::class,
            'name' => 'status'
        ],
        [
            'title' => 'markup-table-th-action', //this is a view partial from olcs-common
            'width' => 'checkbox',
            'type' => 'Checkbox',
            'disableIfRowIsDisabled' => true,
        ]
    ]
];
