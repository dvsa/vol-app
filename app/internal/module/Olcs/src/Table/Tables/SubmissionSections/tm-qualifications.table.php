<?php
return [
    'variables' => [
        'id' => 'tm-qualifications',
        'action_route' => [
            'route' => 'submission_update_table',
            'params' => ['section' => 'tm-qualifications']
        ],
        'title' => 'Qualifications'
    ],
    'settings' => [
        'crud' => [
            'formName' => 'tm-qualifications',
            'actions' => [
                'refresh-table' => ['label' => 'Refresh table', 'class' => 'govuk-button govuk-button--secondary', 'requireRows' => false],
                'delete-row' => ['label' => 'Delete row', 'class' => 'govuk-button govuk-button--secondary js-require--multiple', 'requireRows' => true]
            ],
            'action_field_name' => 'formAction'
        ],
        'submission_section' => 'display',
    ],
    'attributes' => [
        'name' => 'tm-qualifications'
    ],
    'columns' => [
        [
            'title' => 'Type',
            'name' => 'qualificationType',
        ],
        [
            'title' => 'Serial no.',
            'name' => 'serialNo',
        ],
        [
            'title' => 'Date',
            'name' => 'issuedDate'
        ],
        [
            'title' => 'Country',
            'name' => 'country',
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
