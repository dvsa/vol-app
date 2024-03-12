<?php
return [
    'variables' => [
        'id' => 'applications',
        'action_route' => [
            'route' => 'submission_update_table',
            'params' => ['section' => 'tm-responsibilities', 'subSection' => 'applications']
        ],
        'title' => 'Applications'
    ],
    'settings' => [
        'crud' => [
            'formName' => 'applications',
            'actions' => [
                'refresh-table' => ['label' => 'Refresh table', 'class' => 'govuk-button govuk-button--secondary', 'requireRows' => false],
                'delete-row' => ['label' => 'Delete row', 'class' => 'govuk-button govuk-button--secondary js-require--multiple', 'requireRows' => true]
            ],
            'action_field_name' => 'formAction'
        ],
        'submission_section' => 'display',
    ],
    'attributes' => [
        'name' => 'applications'
    ],
    'columns' => [
        [
            'title' => 'Manager type',
            'name' => 'managerType',
        ],
        [
            'title' => 'No. of operating centres',
            'name' => 'noOpCentres',
        ],
        [
            'title' => 'Application ID',
            'name' => 'applicationId'
        ],
        [
            'title' => 'Licence No.',
            'name' => 'licNo'
        ],
        [
            'title' => 'Operator name',
            'name' => 'organisationName',
        ],
        [
            'title' => 'Hours per week',
            'name' => 'hrsPerWeek',
        ],
        [
            'title' => 'Status',
            'name' => 'status',
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
