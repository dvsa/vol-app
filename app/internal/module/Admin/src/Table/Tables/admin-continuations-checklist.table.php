<?php

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
                'generate-letters' => [
                    'label' => 'Generate letters',
                    'class' => 'govuk-button js-require--multiple',
                    'requireRows' => true
                ],
                'export' => [
                    'label' => 'Export',
                    'class' => 'govuk-button govuk-button--secondary js-disable-crud js-require--multiple',
                    'requireRows' => true
                ],
            ]
        ],
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
            'title' => 'markup-table-th-action', //this is a view partial from olcs-common
            'width' => 'checkbox',
            'type' => 'Checkbox',
            'disableIfRowIsDisabled' => true,
        ]
    ]
];
