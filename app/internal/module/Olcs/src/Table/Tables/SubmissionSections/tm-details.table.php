<?php

use Common\Service\Table\Formatter\Address;

return [
    'variables' => [
        'id' => 'tm-details',
        'action_route' => [
            'route' => 'submission_update_table',
            'params' => ['section' => 'tm-details']
        ],
        'title' => 'TM details'
    ],
    'settings' => [
        'crud' => [
            'formName' => 'tm-details',
            'actions' => [
                'refresh-table' => ['label' => 'Refresh table', 'class' => 'govuk-button govuk-button--secondary', 'requireRows' => false],
                'delete-row' => ['label' => 'Delete row', 'class' => 'govuk-button govuk-button--secondary js-require--multiple', 'requireRows' => true]
            ],
            'action_field_name' => 'formAction'
        ],
        'submission_section' => 'display',
    ],
    'attributes' => [
        'name' => 'tm-details'
    ],
    'columns' => [
        [
            'title' => 'Title',
            'name' => 'title',
        ],
        [
            'title' => 'First name',
            'name' => 'forename',
        ],
        [
            'title' => 'Family name',
            'name' => 'familyName',
        ],
        [
            'title' => 'DOB',
            'name' => 'birthDate',
        ],
        [
            'title' => 'Place of birth',
            'name' => 'birthPlace',
        ],
        [
            'title' => 'Type',
            'name' => 'tmType',
        ],
        [
            'title' => 'Address',
            'width' => '350px',
            'formatter' => Address::class,
            'name' => 'address'
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
