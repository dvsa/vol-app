<?php

return [
    'variables' => [
        'id' => 'requested-penalties',
        'action_route' => [
            'route' => 'submission_update_table',
            'params' => ['section' => 'penalties', 'subSection' => 'requested-penalties']
        ],
        'title' => 'Requested penalties'
    ],
    'settings' => [
        'crud' => [
            'formName' => 'requested-penalties',
            'actions' => [
                'refresh-table' => ['label' => 'Refresh table', 'class' => 'govuk-button govuk-button--secondary', 'requireRows' => false],
                'delete-row' => ['label' => 'Delete row', 'class' => 'govuk-button govuk-button--secondary js-require--multiple', 'requireRows' => true]
            ],
            'action_field_name' => 'formAction'
        ],
        'submission_section' => 'display'
    ],
    'attributes' => [
        'name' => 'requested-penalties'
    ],
    'columns' => [
        [
            'title' => 'Penalty type',
            'name' => 'penaltyType'
        ],
        [
            'title' => 'Duration',
            'name' => 'duration'
        ],
        [
            'type' => 'Checkbox',
            'title' => 'markup-table-th-action', //this is a view partial from olcs-common
            'width' => 'checkbox',
            'format' => '{{[elements/checkbox]}}',
            'hideWhenDisabled' => true
        ]
    ]
];
