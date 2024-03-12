<?php

use Common\Service\Table\Formatter\Date;

return [
    'variables' => [
        'title' => 'Statements',
        'titleSingular' => 'Statement',
        'empty_message' => 'There are no statements'
    ],
    'settings' => [
        'crud' => [
            'actions' => [
                'add' => ['class' => 'govuk-button', 'label' => 'Add statement'],
                'edit' => ['class' => 'govuk-button govuk-button--secondary js-require--one', 'requireRows' => true],
                'generate' => [
                    'requireRows' => true,
                    'class' => 'govuk-button govuk-button--secondary js-require--one',
                    'label' => 'Generate Letter'
                ],
                'delete' => ['class' => 'govuk-button govuk-button--warning js-require--one', 'requireRows' => true]
            ]
        ]
    ],
    'columns' => [
        [
            'title' => 'markup-table-th-action', //this is a view partial from olcs-common
            'width' => 'checkbox',
            'format' => '{{[elements/radio]}}'
        ],
        [
            'title' => 'Date requested',
            'formatter' => function ($data, $column) {
                $column['formatter'] = Date::Class;
                return '<a href="' . $this->generateUrl(
                    ['action' => 'edit', 'statement' => $data['id']],
                    'case_statement',
                    true
                ) . '" class="govuk-link js-modal-ajax">' . $this->callFormatter($column, $data) . '</a>';
            },
            'name' => 'requestedDate'
        ],
        [
            'title' => 'Requested by',
            'formatter' => fn($data, $column) => $data['requestorsContactDetails']['person']['forename'] . ' ' .
                $data['requestorsContactDetails']['person']['familyName']
        ],
        [
            'title' => 'Statement type',
            'formatter' => fn($data, $column) => $data['statementType']['description'],
        ],
        [
            'title' => 'Date stopped',
            'formatter' => Date::class,
            'name' => 'stoppedDate'
        ],
        [
            'title' => 'Requestor body',
            'name' => 'requestorsBody'
        ],
        [
            'title' => 'Date issued',
            'formatter' => function ($data, $column) {
                $column['formatter'] = Date::class;
                return (!empty($data['issuedDate']) ?
                    $this->callFormatter($column, $data) :
                    $this->translator->translate('Not issued')
                );
            },
            'name' => 'issuedDate'
        ],
        [
            'title' => 'VRM',
            'name' => 'vrm'
        ],
    ]
];
