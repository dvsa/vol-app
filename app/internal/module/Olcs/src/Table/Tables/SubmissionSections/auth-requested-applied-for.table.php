<?php

return [
    'variables' => [
        'id' => 'auth-requested-applied-for',
        'action_route' => [
            'route' => 'submission_update_table',
            'params' => ['section' => 'auth-requested-applied-for']
        ],
        'title' => 'Authorisation requested / applied for'
    ],
    'settings' => [
        'crud' => [
            'formName' => 'auth-requested-applied-for',
            'actions' => [
                'refresh-table' => ['label' => 'Refresh table', 'class' => 'govuk-button govuk-button--secondary', 'requireRows' => false],
                'delete-row' => ['label' => 'Delete row', 'class' => 'govuk-button govuk-button--secondary js-require--multiple', 'requireRows' => true]
            ],
            'action_field_name' => 'formAction'
        ],
        'submission_section' => 'display'
    ],
    'attributes' => [
        'name' => 'auth-requested-applied-for'
    ],
    'columns' => [
        [
            'title' => 'Application ID',
            'name' => 'id'
        ],
        [
            'title' => 'Current VIP',
            'name' => 'currentVehiclesInPossession'
        ],
        [
            'title' => 'Current TIP',
            'name' => 'currentTrailersInPossession'
        ],
        [
            'title' => 'Current vehicle authorisation',
            'name' => 'currentVehicleAuthorisation'
        ],
        [
            'title' => 'Current trailer authorisation',
            'name' => 'currentTrailerAuthorisation'
        ],
        [
            'title' => 'Requested vehicle authorisation',
            'name' => 'requestedVehicleAuthorisation'
        ],
        [
            'title' => 'Requested trailer authorisation',
            'name' => 'requestedTrailerAuthorisation'
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
