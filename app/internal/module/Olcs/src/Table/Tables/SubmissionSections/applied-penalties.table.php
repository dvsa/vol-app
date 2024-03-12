<?php

return [
    'variables' => [
        'id' => 'applied-penalties',
        'action_route' => [
            'route' => 'submission_update_table',
            'params' => ['section' => 'penalties', 'subSection' => 'applied-penalties']
        ],
        'title' => 'Applied penalties'
    ],
    'settings' => [
        'crud' => [
            'formName' => 'applied-penalties',
            'actions' => [
                'refresh-table' => ['label' => 'Refresh table', 'class' => 'govuk-button govuk-button--secondary', 'requireRows' => false],
                'delete-row' => ['label' => 'Delete row', 'class' => 'govuk-button govuk-button--secondary js-require--multiple', 'requireRows' => true]
            ],
            'action_field_name' => 'formAction'
        ],
        'submission_section' => 'display'
    ],
    'attributes' => [
        'name' => 'applied-penalties'
    ],
    'columns' => [
        [
            'title' => 'Penalty ID',
            'name' => 'id'
        ],
        [
            'title' => 'Penalty type',
            'name' => 'penaltyType'
        ],
        [
            'title' => 'Start Date',
            'name' => 'startDate'
        ],
        [
            'title' => 'End Date',
            'name' => 'endDate'
        ],
        [
            'title' => 'Imposed',
            'name' => 'imposed'
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
