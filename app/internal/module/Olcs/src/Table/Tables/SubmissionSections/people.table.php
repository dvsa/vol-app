<?php

return [
    'variables' => [
        'action_route' => [
            'route' => 'submission_update_table',
            'params' => ['section' => 'people']
        ],
        'title' => 'People',
        'id' => 'people'
    ],
    'settings' => [
        'crud' => [
            'formName' => 'people',
            'actions' => [
                'refresh-table' => ['label' => 'Refresh table', 'class' => 'govuk-button govuk-button--secondary', 'requireRows' => false],
                'delete-row' => ['label' => 'Delete row', 'class' => 'govuk-button govuk-button--secondary js-require--multiple', 'requireRows' => true]
            ],
            'action_field_name' => 'formAction'
        ],
        'submission_section' => 'display'
    ],
    'attributes' => [
        'name' => 'people'
    ],
    'columns' => [
        [
            'title' => 'Title',
            'name' => 'title'
        ],
        [
            'title' => 'Firstname',
            'name' => 'forename'
        ],
        [
            'title' => 'Surname',
            'name' => 'familyName'
        ],
        [
            'title' => 'DOB',
            'name' => 'birthDate'
        ],
        [
            'title' => 'Disqual.',
            'name' => 'disqualificationStatus'
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
