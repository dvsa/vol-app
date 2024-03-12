<?php

use Common\Service\Table\Formatter\Address;

return [
    'variables' => [
        'id' => 'tm-other-employment',
        'action_route' => [
            'route' => 'submission_update_table',
            'params' => ['section' => 'tm-other-employment']
        ],
        'title' => 'Other employment'
    ],
    'settings' => [
        'crud' => [
            'formName' => 'tm-other-employment',
            'actions' => [
                'refresh-table' => ['label' => 'Refresh table', 'class' => 'govuk-button govuk-button--secondary', 'requireRows' => false],
                'delete-row' => ['label' => 'Delete row', 'class' => 'govuk-button govuk-button--secondary js-require--multiple', 'requireRows' => true]
            ],
            'action_field_name' => 'formAction'
        ],
        'submission_section' => 'display',
    ],
    'attributes' => [
        'name' => 'tm-other-employment'
    ],
    'columns' => [
        [
            'title' => 'Employer',
            'name' => 'employerName',
        ],
        [
            'title' => 'Address',
            'width' => '350px',
            'formatter' => Address::class,
            'name' => 'address'
        ],
        [
            'title' => 'Position',
            'name' => 'position'
        ],
        [
            'title' => 'Hours/Days',
            'name' => 'hoursPerWeek'
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
