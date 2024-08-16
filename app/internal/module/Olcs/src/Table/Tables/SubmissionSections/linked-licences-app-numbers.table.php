<?php

return [
    'variables' => [
        'id' => 'linked-licences-app-numbers',
        'action_route' => [
            'route' => 'submission_update_table',
            'params' => ['section' => 'linked-licences-app-numbers']
        ],
        'title' => 'Linked licences'
    ],
    'settings' => [
        'crud' => [
            'formName' => 'persons',
            'actions' => [
                'refresh-table' => ['label' => 'Refresh table', 'class' => 'govuk-button govuk-button--secondary', 'requireRows' => false],
                'delete-row' => ['label' => 'Delete row', 'class' => 'govuk-button govuk-button--secondary js-require--multiple', 'requireRows' => true]
            ],
            'action_field_name' => 'formAction'
        ],
        'submission_section' => 'display'
    ],
    'attributes' => [
        'name' => 'linked-licences-app-numbers'
    ],
    'columns' => [
        [
            'title' => 'Lic #',
            'name' => 'licNo'
        ],
        [
            'title' => 'Status',
            'name' => 'status'
        ],
        [
            'title' => 'Licence type',
            'name' => 'licenceType'
        ],
        [
            'title' => 'Total V-auth',
            'name' => 'totAuthVehicles'
        ],
        [
            'title' => 'Total T-auth',
            'name' => 'totAuthTrailers'
        ],
        [
            'title' => 'Vehicles in possession',
            'name' => 'vehiclesInPossession'
        ],
        [
            'title' => 'Trailers in possession',
            'name' => 'trailersInPossession'
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
