<?php

use Common\Service\Table\Formatter\Date;
use Common\Service\Table\Formatter\RefData;

return [
    'variables' => [
        'titleSingular' => 'Opposition',
        'title' => 'Oppositions'
    ],
    'settings' => [
        'crud' => [
            'formName' => 'opposition',
            'actions' => [
                'add' => ['class' => 'govuk-button'],
                'edit' => ['requireRows' => true, 'class' => 'govuk-button govuk-button--secondary js-require--one'],
                'generate' => [
                    'requireRows' => true,
                    'class' => 'govuk-button govuk-button--secondary js-require--one',
                    'label' => 'Generate Letter'
                ],
                'delete' => ['requireRows' => true, 'class' => 'govuk-button govuk-button--warning js-require--one']
            ]
        ],
    ],
    'columns' => [
        [
            'title' => 'markup-table-th-action', //this is a view partial from olcs-common
            'width' => 'checkbox',
            'format' => '{{[elements/radio]}}'
        ],
        [
            'title' => 'Date received',
            'name' => 'raisedDate',
            'formatter' => function ($data, $column) {
                $column['formatter'] = Date::class;
                return '<a href="' . $this->generateUrl(
                    ['action' => 'edit', 'opposition' => $data['id']],
                    'case_opposition',
                    true
                ) . '" class="govuk-link js-modal-ajax">' . $this->callFormatter($column, $data) . '</a>';
            },
            'sort' => 'raisedDate',
        ],
        [
            'title' => 'Opposition type',
            'formatter' => RefData::class,
            'name' => 'oppositionType'
        ],

        [
            'title' => 'Name',
            'formatter' => fn($data, $column) => $data['opposer']['contactDetails']['person']['forename'] . ' ' .
            $data['opposer']['contactDetails']['person']['familyName']
        ],
        [
            'title' => 'Grounds',
            'formatter' => function ($data, $column) {
                $grounds = [];
                foreach ($data['grounds'] as $ground) {
                    $grounds[] = $ground['description'];
                }

                return implode(', ', $grounds);
            }
        ],
        [
            'title' => 'Valid',
            'name' => 'isValid',
            'formatter' => RefData::class,
            'sort' => 'isValid'
        ],
        [
            'title' => 'Copied',
            'name' => 'isCopied',
            'sort' => 'isCopied'
        ],
        [
            'title' => 'In time',
            'name' => 'isInTime',
            'sort' => 'isInTime'
        ],
        [
            'title' => 'Willing to attend PI',
            'name' => 'isWillingToAttendPi',
            'sort' => 'isWillingToAttendPi'
        ],
        [
            'title' => 'Withdrawn',
            'name' => 'isWithdrawn',
            'sort' => 'isWithdrawn'
        ]
    ]
];
