<?php

use Common\Service\Table\Formatter\Name;

return [
    'variables' => [
        'id' => 'statements',
        'action_route' => [
            'route' => 'submission_update_table',
            'params' => ['section' => 'statements']
        ],
        'title' => 'Statements'
    ],
    'settings' => [
        'crud' => [
            'formName' => 'statements',
            'actions' => [
                'refresh-table' => ['label' => 'Refresh table', 'class' => 'govuk-button govuk-button--secondary', 'requireRows' => false],
                'delete-row' => ['label' => 'Delete row', 'class' => 'govuk-button govuk-button--secondary js-require--multiple', 'requireRows' => true]
            ],
            'action_field_name' => 'formAction'
        ],
        'submission_section' => 'display',
    ],
    'attributes' => [
        'name' => 'statements'
    ],
    'columns' => [
        [
            'title' => 'Date requested',
            'name' => 'requestedDate'
        ],
        [
            'title' => 'Requested by',
            'name' => 'requestedBy',
            'formatter' => Name::class
        ],
        [
            'title' => 'Statement type',
            'formatter' => fn($data) => $data['statementType'],
        ],
        [
            'title' => 'Date stopped',
            'name' => 'stoppedDate'
        ],
        [
            'title' => 'Requestor body',
            'formatter' => \Common\Service\Table\Formatter\Comment::class,
            'name' => 'requestorsBody'
        ],
        [
            'title' => 'Date issued',
            'name' => 'issuedDate'
        ],
        [
            'title' => 'VRM',
            'name' => 'vrm'
        ],
        [
            'type' => 'Checkbox',
            'title' => 'markup-table-th-action', //this is a view partial from olcs-common
            'width' => 'checkbox',
            'format' => '{{[elements/checkbox]}}',
            'hideWhenDisabled' => true
        ],
    ]
];
