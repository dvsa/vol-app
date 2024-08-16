<?php

return [
    'variables' => [
        'id' => 'compliance-complaints',
        'action_route' => [
            'route' => 'submission_update_table',
            'params' => ['section' => 'compliance-complaints']
        ],
        'title' => 'Compliance complaints'
    ],
    'settings' => [
        'crud' => [
            'formName' => 'compliance-complaints',
            'actions' => [
                'refresh-table' => ['label' => 'Refresh table', 'class' => 'govuk-button govuk-button--secondary', 'requireRows' => false],
                'delete-row' => ['label' => 'Delete row', 'class' => 'govuk-button govuk-button--secondary js-require--multiple', 'requireRows' => true]
            ],
            'action_field_name' => 'formAction'
        ],
        'submission_section' => 'display'
    ],
    'columns' => [
        [
            'title' => 'Complaint date',
            'name' => 'complaintDate'
        ],
        [
            'title' => 'Complainant name',
            'formatter' => fn($data) => $data['complainantForename'] . ' ' . $data['complainantFamilyName'],
        ],
        [
            'title' => 'Description',
            'name' => 'description'
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
